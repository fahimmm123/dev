<?php
    
    session_start();

    if(!isset($_SESSION['orderProcessed']) || $_SESSION['orderProcessed'] !== true)
    {
        header('Location: index.php');
    }
    else
    {
        unset($_SESSION['orderProcessed']);
        $orderID = htmlspecialchars($_GET['order_id']);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Thank You For Your Order</h1>

    <p>Your order ID is <?= $orderID ?></p>

    <a href="index.php"><button>Return Home</button></a>
     <!-- Footer -->
     <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
</body>
</html>