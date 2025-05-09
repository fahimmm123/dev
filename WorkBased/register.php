<?php
session_start();
if(isset($_SESSION['username'])){
    header("Location: basket.php");
}

require "DB.php";
require "products.php";

$db = new DB;
$errorMessage = '';  // Store error messages
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <title>Tyne Brew Coffee</title>
    <link rel="stylesheet" href="style.css">
</head>

<body><div class="navbar">
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

    <div id="register">
    <h1>Register</h1>
        <form method="post">
            <span class="fontawesome-user"></span>
            <input type="email" placeholder="Email.." name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <span class="fontawesome-user"></span>
            <input type="text" placeholder="Username.." name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
            <span class="fontawesome-user"></span>
            <input type="password" placeholder="Password.." name="password" required>
            <span class="fontawesome-user"></span>
            <input type="password" placeholder="Confirm Password.." name="confpass" required>
            <input type="submit" value="Register" name="register">
        </form>

        <div id="register-output">
        <?php
        if (isset($_POST['register'])) {
            // Set post variables
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $confpass = $_POST['confpass'];

            // Validation checks
            if (empty($email) || empty($username) || empty($password) || empty($confpass)) {
                $errorMessage = 'Please fill in all fields!';
            } elseif ($password != $confpass) {
                $errorMessage = 'Your passwords do not match!';
            } elseif (strlen($email) < 5 || strlen($email) > 50) {
                $errorMessage = 'Your email does not meet the length requirements!';
            } elseif (strlen($username) < 3 || strlen($username) > 12) {
                $errorMessage = 'Your username does not meet the length requirements!';
            } elseif (strlen($password) < 3 || strlen($password) > 100) {
                $errorMessage = 'Your password does not meet the length requirements!';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'Invalid Email';
            } elseif (!preg_match("/^[a-zA-Z0-9_]*$/", $username)) {
                $errorMessage = 'Invalid username!';
            }

            // If there are no validation errors, proceed to insert into the database
            if (empty($errorMessage)) {
                // Database connection
                $conn = $db->connect();
                if (!$conn) {
                    $errorMessage = 'Database failed to connect!';
                } else {
                    // Check if email or username is already in use
                    $emailQuery = $conn->prepare("SELECT * FROM Users WHERE email = :email OR username = :username");
                    $emailQuery->bindParam(':email', $email, PDO::PARAM_STR);
                    $emailQuery->bindParam(':username', $username, PDO::PARAM_STR);
                    $emailQuery->execute();

                    if ($emailQuery->rowCount() > 0) {
                        $errorMessage = 'Email or username is already in use';
                    } else {
                        // Generate UUID
                        $uuid = uniqid('', true);  // Simple UUID generation
                        
                        // Encrypt the password
                        $encryptedPassword = password_hash($password, PASSWORD_DEFAULT);

                        // Insert user into the database
                        $insertQuery = $conn->prepare("INSERT INTO Users (email, username, password, UUID) VALUES (:email, :username, :password, :uuid)");
                        $insertQuery->bindParam(":email", $email, PDO::PARAM_STR);
                        $insertQuery->bindParam(":username", $username, PDO::PARAM_STR);
                        $insertQuery->bindParam(":password", $encryptedPassword, PDO::PARAM_STR);
                        $insertQuery->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                        $insertQuery->execute();

                        echo 'Registration successful! You can now log in.';
                    }
                }
            }

            // Display the error message if it exists
            if (!empty($errorMessage)) {
                echo '<p style="color: red;">' . htmlspecialchars($errorMessage) . '</p>';
            }
        }
        ?>
        </div>
    </div>
    <br><br><br><br><br><br>
  <!-- Footer -->
  <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
</body>
</html>
