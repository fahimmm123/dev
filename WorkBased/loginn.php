<?php
session_start();

ini_set('session.cookie_httponly', 1); // Prevent JavaScript from accessing session cookies
ini_set('session.cookie_secure', 1);   // Only transmit cookies over HTTPS
ini_set('session.use_only_cookies', 1); 
require "DB.php"; // Including database connection
$db = new DB;

$errorMessage = ''; // Initialize an empty error message

// CSRF token implementation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a CSRF token
}

if(isset($_SESSION['username'])){
    header("Location: basket.php");
}

// Process login when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Get the username and password from POST data
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $errorMessage = 'Please enter both username and password.';
    } else {
        // Connect to the database
        $conn = $db->connect();
        if (!$conn) {
            $errorMessage = 'Database connection failed!';
        } else {
            // Prepare a query to fetch the user with the provided username
            $query = $conn->prepare("SELECT * FROM Users WHERE username = :username");
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();

            // If the user exists, check the password
            if ($query->rowCount() > 0) {
                $user = $query->fetch(PDO::FETCH_ASSOC);
                // Compare the provided password with the stored hashed password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, log the user in by setting a session variable
                    $_SESSION['user_id'] = $user['uuid'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['loggedin'] = true;

                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    if(isset($_SESSION['redirected']))
                    {
                        unset($_SESSION['redirected']);
                        header('Location: loginn.php');
                    }
                    
                    header("Location: basket.php");
                    exit();
                } else {
                    $errorMessage = 'Login information did not match.';
                }
            } else {
                $errorMessage = 'Login information did not match.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Tyne Brew Coffee</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Encode+Sans+Semi+Expanded:wght@100;200;300;400;500;600;700;800;900&family=Permanent+Marker&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
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
    <div id="login">
      <h1>Login</h1>
        <form method="POST" autocomplete="off">
            <span class="fontawesome-user"></span>
            <input type="text" id="user" name="username" placeholder="Username" required autocomplete="off">
            <span class="fontawesome-lock"></span>
            <input type="password" id="pass" name="password" placeholder="Password" required autocomplete="new-password">
            <!-- CSRF Token hidden field -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="submit" value="Login">
        </form>
        <!-- Display error message if credentials are incorrect -->
        <?php if (!empty($errorMessage)): ?>
            <div style="color: red; margin-top: 10px;">
                <?php echo htmlspecialchars($errorMessage); // Sanitize output ?>
            </div>
        <?php endif; ?>
    </div>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
     <!-- Footer -->
     <footer>
        <p>&copy; <span id="year"></span> Golden Crust Bakery. All Rights Reserved.</p>
    </footer>
</body>

</html>
