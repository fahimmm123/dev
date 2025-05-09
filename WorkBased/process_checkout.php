<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "DB.php"; // Ensure DB connection is established
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: loginn.php?redirect=checkout.php");
    exit();
}

// Get the user's ID from session
$user_id = $_SESSION['user_id'];
$total_price = 50.00;  // This would come from your basket calculation logic (use session or calculate total from cart items)

// Connect to the database
$conn = (new DB())->connect();

// Check if the cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("Your cart is empty.");
}

// 1. Insert the order into the `orders` table
$sql = "INSERT INTO orders (user_id, total_price, order_date) VALUES (:user_id, :total_price, :order_date)";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
$stmt->bindParam(':order_date', $order_date, PDO::PARAM_STR);
$order_date = date('Y-m-d H:i:s');  // Current date and time for the order

try {
    $stmt->execute();  // Execute the statement to insert the order
    $order_id = $conn->lastInsertId(); // Get the last inserted order ID

    // 2. Insert order items
    $basket = $_SESSION['cart'];  // Get the cart items from session

    foreach ($basket as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price_per_item = $item['price'];  // Assuming each item has a 'price' field
        $total_item_price = $quantity * $price_per_item;

        // Prepare the SQL query to insert order items
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price_per_item, total_price) 
                VALUES (:order_id, :product_id, :quantity, :price_per_item, :total_item_price)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':price_per_item', $price_per_item, PDO::PARAM_STR);
        $stmt->bindParam(':total_item_price', $total_item_price, PDO::PARAM_STR);
        
        // Execute the insertion of each order item
        $stmt->execute();
    }

    // 3. Set checkout success message and store order ID for confirmation
    $_SESSION['checkout_success'] = "Checkout was successful! Thank you for your purchase.";
    $_SESSION['order_id'] = $order_id;  // Store the order ID for confirmation
    $_SESSION['order_date'] = $order_date;  // Store the order date

    // Optionally clear the cart after checkout
    unset($_SESSION['cart']);

    // Redirect to order confirmation page or order history page
    header("Location: orderhistory.php");
    exit();
} catch (PDOException $e) {
    // Handle database insertion error
    echo "Error: " . $e->getMessage();  // Print out the error message for debugging
    exit();
}
?>
