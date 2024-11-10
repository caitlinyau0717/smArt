<?php
session_start();

// Database connection parameters
$db_host = "localhost";
$db_user = "your_db_username";
$db_pass = "your_db_password";
$db_name = "smart";

// Create database connection
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Function to validate and sanitize input
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Get and validate input
    $username = validate($_POST['uname']);
    $password = validate($_POST['password']);

    if (empty($username)) {
        header("Location: login.php?error=Username is required");
        exit();
    } else if (empty($password)) {
        header("Location: login.php?error=Password is required");
        exit();
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Verify password - assuming passwords are hashed in database
            if (password_verify($password, $row['password'])) {
                // Create session variables
                $_SESSION['userid'] = $row['userid'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to art.php
                header("Location: art.php");
                exit();
            } else {
                header("Location: login.php?error=Incorrect Username or Password");
                exit();
            }
        } else {
            header("Location: login.php?error=Incorrect Username or Password");
            exit();
        }
    }
} else {
    // If someone tries to access this file directly without POST data
    if (!isset($_POST['uname']) && !isset($_POST['password'])) {
        // Load the login form
        include('login-form.php');
        exit();
    }
}
?>