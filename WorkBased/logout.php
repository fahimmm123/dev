<?php
session_start();  // Start the session

// Unset all session variables
//session_unset();

// Destroy the session
//session_destroy();

unset($_SESSION['user_id']);
unset($_SESSION['username']);
$_SESSION['loggedin'] = false;


// Redirect to the login page
header("Location: index.php");
exit();
?>
