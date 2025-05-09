<?php
session_start();
ini_set('display_errors', 1);
require_once 'DB.php';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    if(isset($_POST['update_quantity']) || isset($_POST['delete_item'])) {
        $itemId = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
        if ($itemId === false) die("Invalid item ID");
    }

    if (isset($_POST['delete_item'])) {
        unset($_SESSION['basket'][$itemId]);
    } elseif (isset($_POST['update_quantity'])) {
        $newQuantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);
        if ($newQuantity === false || $newQuantity <= 0) {
            unset($_SESSION['basket'][$itemId]);
        } else {
            $_SESSION['basket'][$itemId]['quantity'] = $newQuantity;
        }
    }

    if (isset($_POST['checkout'])) {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) {
            $_SESSION['redirected'] = true;
            header('Location: loginn.php');
        } else {
            header('Location: confirm_order.php');
        }
    }
}

// Redirect if not logged in and trying to checkout
if (!isset($_SESSION['user_id']) && isset($_GET['action']) && $_GET['action'] == 'checkout') {
    header("Location: loginn.php?redirect=checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Golden Crust Bakery - Basket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Encode+Sans+Semi+Expanded:wght@100;900&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body><!-- Navigation -->
    <div class="navbar">
        <div class="logo"><img src="img/logo.png" alt="Golden Crust Bakery Logo"></div>
        <form class="search-form" method="GET" action="search_results.php">
            <input type="text" name="search" placeholder="Search..." required>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
        <ul class="nav-list">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="aboutus.php"><i class="fa fa-info-circle"></i> About</a></li>
            <li><a href="logout.php"><i class="fa fa-user"></i> Logout</a></li>
            <li><a href="register.php"><i class="fa fa-sign-in"></i> Register</a></li>           
            <li><a href="basket.php"><img src="img/basket.jpg" alt="Basket" width="30px"></a></li>
        </ul>
       
    </div>

<h1 class="basket-title">Your Basket</h1>

<?php if (empty($_SESSION['basket'])): ?>
    <p class="empty-basket-msg">Your basket is empty!</p>
<?php else: ?>
    <table class="basket-table">
        <tr class="table-header">
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Actions</th>
        </tr>
        <?php
        $totalPrice = 0;
        foreach ($_SESSION['basket'] as $itemId => $item):
            if ($item['quantity'] <= 0) continue;
            $itemTotal = $item['quantity'] * $item['price'];
            $totalPrice += $itemTotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td>£<?= number_format($item['price'], 2) ?></td>
            <td>
                <form method="POST" action="basket.php" class="inline-form">
                    <input type="number" name="quantity" value="<?= htmlspecialchars($item['quantity']) ?>" min="1" class="quantity-input">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($itemId) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <button type="submit" name="update_quantity" class="update-btn">Update</button>
                </form>
            </td>
            <td>£<?= number_format($itemTotal, 2) ?></td>
            <td>
                <form method="POST" action="basket.php" class="inline-form">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($itemId) ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                    <button type="submit" name="delete_item" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="total-display">
        <p>Total: £<?= number_format($totalPrice, 2) ?></p>
    </div>
<?php endif; ?>

<div class="checkout-container">
    <?php if (empty($_SESSION['basket'])): ?>
        <button class="disabled-btn" disabled>Proceed to Checkout</button>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="submit" name="checkout" value="Proceed To Checkout" class="checkout-btn">
        </form>
    <?php endif; ?>
</div>

 <!-- Footer -->
 <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
</body>
</html>
