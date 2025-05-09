<?php
session_start();

ini_set("display_errors", 1);


require "DB.php";
require "products.php";

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = new DB();

// Sanitize search term
$searchTerm = isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : '';

// Prepare SQL query to search products by name
$query = $db->connect()->prepare("SELECT * FROM tbl_products WHERE name LIKE :searchTerm");
$query->execute(['searchTerm' => "%$searchTerm%"]);

$product = array();
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id'];
    $name = $row['name'];
    $image = $row['image'];
    $product[] = new Product($id, $name, $image);
}

// Initialize the basket if it's not set
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = array();
}

// Add item to the basket when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    // Verify CSRF token
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }

    $product_id = (int) $_POST['product_id'];  // Ensure it's an integer
    $product_name = htmlspecialchars($_POST['product_name'], ENT_QUOTES, 'UTF-8');
    $product_price = (float) $_POST['product_price'];  // Ensure it's a float
    $product_image = htmlspecialchars($_POST['product_image'], ENT_QUOTES, 'UTF-8');
    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) && $_POST['quantity'] > 0
        ? (int) $_POST['quantity']
        : 1;

    // Add or update the item in the basket
    $found = false;
    foreach ($_SESSION['basket'] as &$item) {
        if ($item['id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['basket'][] = array(
            'id' => $product_id,
            'name' => $product_name,
            'price' => $product_price,
            'image' => $product_image,
            'quantity' => $quantity
        );
    }

    echo "<script type='text/javascript'>
            alert('Item added to your basket!');
            window.location = 'search_results.php?search=$searchTerm'; 
          </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tyne Brew Coffee</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<style>
/* Global Reset and Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
    
}



.logo img {
    height: 50px;
}

.search-form {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Heading */
h1 {
    text-align: center;
    margin: 80px 0 30px;
    color: #e87c03;
}

/* Product Container */
.product-item-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
    padding: 20px;
}

/* Product Card */
.product-item {
    background-color: #fff;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    text-align: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
}

.product-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    margin: 10px 0;
}

.product-price {
    font-weight: bold;
    color: #333;
    margin-bottom: 10px;
}

/* Quantity and Button */
.quantity-container input {
    width: 60px;
    padding: 5px;
    border-radius: 6px;
    border: 1px solid #ccc;
    margin-bottom: 10px;
}

.add-to-cart-btn {
    background-color: #e87c03;
    color: white;
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.add-to-cart-btn:hover {
    background-color: #c96500;
}

/* Footer */
footer {
    margin-top: 40px;
    text-align: center;
    padding: 15px;
    background-color: #f3e5ab;
    border-top: 2px solid #ddd;
}

footer p {
    color: #444;
    font-size: 0.95rem;
}
.search_results{
    color: black;
}
/* Responsive Design */
@media (max-width: 768px) {
    .nav-list {
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }

    .navbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .product-item img {
        height: 150px;
    }
}
</style>

<div class="navbar">
        <div class="logo"><img src="img/logo.png" alt="Golden Crust Bakery Logo"></div>
        <form class="search-form" method="GET" action="search_results.php">
            <input type="text" name="search" placeholder="Search..." required>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
        <ul class="nav-list">
            <li><a href="index.php"><i class="fa fa-hzome"></i> Home</a></li>
            <li><a href="aboutus.php"><i class="fa fa-info-circle"></i> About</a></li>
            <li><a href="loginn.php"><i class="fa fa-user"></i> Login</a></li>
            <li><a href="register.php"><i class="fa fa-sign-in"></i> Register</a></li>
       
            <li><a href="basket.php"><img src="img/basket.jpg" alt="Basket" width="30px"></a></li>
        </ul>
       
    </div>

    <br><h1>Search Results for: "<?= htmlspecialchars($searchTerm) ?>"</h1>
    
    <?php if (empty($product)): ?>
        <p>No products found for your search.</p>
    <?php else: ?>
        <div class="product-item-container">
            <?php foreach ($product as $p): ?>
                <div class="product-item">
                    <p><?= htmlspecialchars($p->name()) ?></p>
                    <img src="<?= htmlspecialchars($p->image()) ?>" alt="Product Image" />
                    <p class="product-price">Price: £4.99</p>

                 <center>   <form action="search_results.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $p->id() ?>">
                        <input type="hidden" name="product_name" value="<?= $p->name() ?>">
                        <input type="hidden" name="product_price" value="4.99">
                        <input type="hidden" name="product_image" value="<?= $p->image() ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                
                        <button type="submit" class="btn add-to-cart-btn">Add to Cart</button></center>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
     <!-- Footer -->
     <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
</body>
</html>
