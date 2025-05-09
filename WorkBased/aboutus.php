<?php
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));  // Create a new CSRF token
}

// Validate CSRF token for form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token'])) {
    die('Invalid CSRF token');
}

// Sanitize dynamic content
$productName = isset($productName) ? htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') : '';
$productImage = isset($productImage) ? htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') : '';

// Initialize the visitor counter session variable
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count'] = 0;
}

// Increment the counter on each visit
$_SESSION['visit_count']++;

// Get the current count
$visitCount = $_SESSION['visit_count'];

// Team Members Data
$teamMembers = [
    [
        'name' => 'Emma Carter',
        'role' => 'Founder & Master Baker',
        'image' => 'img/baker1.jpg',
        'bio' => 'Emma\'s passion for bread baking began in her grandmother’s kitchen. Now, she leads Golden Crust with creativity and warmth.'
    ],
    [
        'name' => 'Leo Tran',
        'role' => 'Pastry Chef',
        'image' => 'img/chef2.jpg',
        'bio' => 'From flaky croissants to creamy éclairs, Leo’s precision and flair bring elegance to every bite.'
    ],
    [
        'name' => 'Ava Jenkins',
        'role' => 'Operations Manager',
        'image' => 'img/chef3.jpg',
        'bio' => 'Ava makes sure everything runs like clockwork, ensuring customers always get their favorites fresh and on time.'
    ]
];
?>
<!DOCTYPE html
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Golden Crust Bakery - Freshly baked delights delivered to your door.">
    <meta name="author" content="Golden Crust Bakery">
    <meta name="robots" content="index, follow">
    <title>Golden Crust Bakery - About Us</title>

    <link rel="stylesheet" href="style.css">
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

<!-- About Us Section -->
<div class="about-container" style="padding: 0px 0x 0px; color: white; font-family: 'Roboto Condensed', sans-serif; margin-top: 120px; display: flex; gap: 20px; justify-content: space-between; color: #5d4037;">
    <div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); width: 48%; text-align: center; height: 300px;">
        <h1 style="font-size: 2rem; font-family: 'Permanent Marker', cursive; color: #f39c12;">About Us</h1>
        <p>Founded in 2022, Golden Crust Bakery began with a simple dream — to share the joy of fresh, handmade baked goods with our local community. What started in a small kitchen has since risen into a beloved neighborhood bakery, known for our warm service and even warmer pastries.</p>
        <p>Today, we’re proud to serve not only our local customers but also homes across the region through our online store. Wherever you are, we bring a little slice of homemade goodness straight to your table. 🍞💛</p>
    </div>

    <!-- Our Vision -->
    <div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); width: 48%; text-align: center; height: 300px; color: #5d4037;">
        <h2 style="font-size: 2rem; font-family: 'Permanent Marker', cursive; color: #f39c12;">Our Vision</h2>
        <p>Looking ahead, we envision Golden Crust Bakery as the go-to destination for baked goods worldwide, where innovation and tradition blend harmoniously. As we grow, we strive to uphold the values of quality, creativity, and sustainability in every product we offer.</p>
    </div>
</div>

<!-- Meet the Team -->
<div style="background-color: rgba(255, 255, 255, 0.1); padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5); margin-bottom: 40px; color: #5d4037;">
    <center><h2 style="font-size: 2rem; font-family: 'Permanent Marker', cursive; color: #f39c12;">Meet the Team</h2></center>
    <div class="team-section" style="display: flex; gap: 20px; justify-content: space-between; margin-top: 20px;">
        <?php
        foreach ($teamMembers as $member) {
            echo "
            <div class='team-member' style='text-align: center; width: 30%;'>
                <img src='{$member['image']}' alt='{$member['name']}' style='width: 100%; height: 500px; border-radius: 10px; margin-bottom: 10px;color: #5d4037;'>
                <h3 style='color: #f39c12;'>{$member['name']}</h3>
                <h2 style='color: #f39c12;'>{$member['role']}</h2>
                <p style='color: #5d4037;'>{$member['bio']}</p>
            </div>";
        }
        ?>
    </div>
</div>

<!-- Visitor Counter -->
<div class="visitor-counter" style="text-align: center; font-size: 1.2rem; color: #5d4037;">
    <p>Welcome! You are visitor number: <?php echo $visitCount; ?></p>
</div>

<!-- Quote Carousel -->
<div class="quote-carousel" style="margin-top: 50px; text-align: center;">
    <div class="quote" style="font-style: italic; color: #555; padding: 20px; border-radius: 8px; background: rgba(255, 255, 255, 0.8); margin: 10px 0;">
        <p>"The best bakery I’ve ever visited. The croissants are a dream!" - Happy Customer</p>
    </div>
    <div class="quote" style="font-style: italic; color: #555; padding: 20px; border-radius: 8px; background: rgba(255, 255, 255, 0.8); margin: 10px 0;">
        <p>"Golden Crust Bakery makes every occasion special. Their cakes are incredible!" - Satisfied Client</p>
    </div>
    <div class="quote" style="font-style: italic; color: #555; padding: 20px; border-radius: 8px; background: rgba(255, 255, 255, 0.8); margin: 10px 0;">
        <p>"I can't get enough of the freshly baked bread. Definitely a go-to spot!" - Local Fan</p>
    </div>
</div>

 <!-- Footer -->
 <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>

</body>
</html>
