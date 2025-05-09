
<?php

require "DB.php";
require "products.php";
session_start();
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict' // or 'Lax'
]);

session_regenerate_id(true);
session_set_cookie_params([
    'secure' => true, // Ensure cookies are sent only over HTTPS
    'httponly' => true, // Prevent JavaScript access to the session cookie
]);

// Secure session management
session_regenerate_id(true); // Regenerate session ID to prevent session fixation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generates a CSRF token
}

$db = new DB();

$query = $db->connect()->prepare("SELECT * FROM tbl_products");
$query->execute();

$product = array();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $id = htmlspecialchars($row['id']);
    $name = htmlspecialchars($row['name']);
    $image = htmlspecialchars($row['image']);
    $product[] = new Product($id, $name, $image);
}

// Initialize the basket if it's not set
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = array();
}

// Add item to the basket when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['products'])) {
    // Validate CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }

    // Flag to check if any valid quantity is provided
    $itemSelected = false;

    // Iterate through the products array
    foreach ($_POST['products'] as $productData) {
        $product_id = filter_var($productData['id'], FILTER_SANITIZE_NUMBER_INT);
        $product_name = htmlspecialchars($productData['name']);
        $product_price = filter_var($productData['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $product_image = htmlspecialchars($productData['image']);
        $quantity = filter_var($productData['quantity'], FILTER_SANITIZE_NUMBER_INT);

        // If quantity is greater than 0, proceed with adding the item
        if ($quantity > 0) {
            $itemSelected = true;

            // Check if product already exists in the basket
            $found = false;
            foreach ($_SESSION['basket'] as &$item) {
                if ($item['id'] == $product_id) {
                    $item['quantity'] += $quantity; // Update quantity if the product is already in the basket
                    $found = true;
                    break;
                }
            }

            // If not found in the basket, add it
            if (!$found) {
                $_SESSION['basket'][] = array(
                    'id' => $product_id,
                    'name' => $product_name,
                    'price' => $product_price,
                    'image' => $product_image,
                    'quantity' => $quantity
                );
            }
        }
    }

    if (!$itemSelected) {
        echo "<script type='text/javascript'>
                alert('Please select an item first!');
                window.location = 'index.php'; // Redirect back to the product page
              </script>";
    } else {
        echo "<script type='text/javascript'>
                alert('Items added to your basket!');
                window.location = 'basket.php'; // Redirect to the basket page
              </script>";
    }
}



?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Golden Crust Bakery</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <!-- Navigation -->
    <div class="navbar">
        <div class="logo"><img src="img/logo.png" alt="Golden Crust Bakery Logo"></div>
        <form class="search-form" method="GET" action="search_results.php">
            <input type="text" name="search" placeholder="Search..." required>
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
        <ul class="nav-list">
            <li><a href="index.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="aboutus.php"><i class="fa fa-info-circle"></i> About</a></li>
            <li><a href="loginn.php"><i class="fa fa-user"></i> Login</a></li>
            <li><a href="register.php"><i class="fa fa-sign-in"></i> Register</a></li>
            <li><a href="basket.php"><img src="img/basket.jpg" alt="Basket" width="30px"></a></li>
        </ul>
       
    </div>

    <!-- Hero Banner -->
    <section class="hero">
        <img src="img/aa.jpg" alt="Bakery Banner">
        <div class="hero-content">
            <h1>Golden Crust Bakery</h1>
            <p>Freshly Baked Every Day – From Our Oven to Your Table</p>
            <a href="loginn.php" class="cta-btn">Order Now</a>
        </div>
    </section>

    <!-- About Us -->
    <section class="about-section">
        
        <div class="about-text">
            <center><h2>What Makes Our Bakery Special?</h2>
            <p>We believe in the magic of fresh baking. Everything we offer is crafted with care and quality ingredients. From breads to cakes, we’re here to bring warmth to your home, now with convenient online ordering.</p> </center>
        </div>
    </section>

    <!-- Product Offer -->
    <section class="special-offer">
        <h2>Special Offer: Everything for £4.99</h2>
        <form method="POST" action="index.php">
            <div class="product-grid">
                <?php foreach ($product as $p): ?>
                    <div class="product-card">
                        <img src="<?= $p->image() ?>" alt="<?= $p->name() ?>">
                        <h3><?= $p->name() ?></h3>
                        <p class="price">£4.99</p>
                        <div class="quantity-selector">
                            <button type="button" onclick="changeQuantity(<?= $p->id() ?>, 1)">+</button>
                            <input type="number" id="quantity-<?= $p->id() ?>" name="products[<?= $p->id() ?>][quantity]" value="0" min="0">
                            <button type="button" onclick="changeQuantity(<?= $p->id() ?>, -1)">-</button>
                        </div>
                        <input type="hidden" name="products[<?= $p->id() ?>][id]" value="<?= $p->id() ?>">
                        <input type="hidden" name="products[<?= $p->id() ?>][name]" value="<?= $p->name() ?>">
                        <input type="hidden" name="products[<?= $p->id() ?>][price]" value="4.99">
                        <input type="hidden" name="products[<?= $p->id() ?>][image]" value="<?= $p->image() ?>">
                        <a href="information.php?id=<?= $p->id() ?>" class="info-link">More Info</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="center"><button type="submit" class="add-btn">Add Selected to Basket</button></div>
        </form>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-grid">
            <div class="testimonial">
                <p>"The cinnamon rolls are life-changing! Super fresh and buttery."</p>
                <span>- Emma J.</span>
            </div>
            <div class="testimonial">
                <p>"Best sourdough in town. We order every week. Highly recommended!"</p>
                <span>- Mark W.</span>
            </div>
            <div class="testimonial">
                <p>"I love the new online ordering! Easy and quick."</p>
                <span>- Aisha R.</span>
            </div>
        </div>
    </section>

    <!-- Contact Teaser -->
    <section class="contact-teaser">
        <h2>Want to know more about us?</h2>
        <p>Click here to go to our About Us page</p>
        <a href="aboutus.php" class="cta-btn secondary">About Us</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>

    <script>
        function changeQuantity(id, delta) {
            const input = document.getElementById('quantity-' + id);
            let val = parseInt(input.value) || 0;
            val += delta;
            input.value = val < 0 ? 0 : val;
        }

        document.getElementById("year").textContent = new Date().getFullYear();
    </script>
</body>
</html>
