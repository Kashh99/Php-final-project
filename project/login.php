<?php

session_start(); // Start the session

$servername = "localhost";  // Change this if your MySQL server is on a different host
$username = "root";         // Change this if your MySQL username is different
$password = "";             // Change this if your MySQL password is different
$dbname = "project";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables to store user input and error messages
$email = $password = "";
$emailErr = $passwordErr = "";

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// If the form is submitted, process the login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        // Check if email address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // Validate Password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);
        // Check if password is at least 8 characters long
        if (strlen($password) < 8) {
            $passwordErr = "Password must be at least 8 characters long";
        }
    }

    // If there are no validation errors, check user existence in the database
    if (empty($emailErr) && empty($passwordErr)) {
        // Check if the user exists in the 'users' table using a prepared statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User found, check the password
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Password is correct, set session and redirect to the profile page
                $_SESSION['user_id'] = $row['id'];
                header("Location: homepage.php");
                exit();
            } else {
                // Password is incorrect
                echo "Incorrect password. Please try again.";
            }
        } else {
            // User not found
            echo "User not found. <a href='register.php'>Register here</a>.";
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Comfortaa:wght@500&family=Shizuru&display=swap" rel="stylesheet"> 
    <title>User Login</title>
</head>
<body>
<header>
    <h1>VibeSET</h1>

</header>
<div class="loginbackg">
    <img src="./assets/img/loginb.jpg" alt="My Image">
    <div class="login-form">
        <h2>User Login</h2>
        
        <!-- Display message to prompt registration if not logged in -->
        <div class="register-message">
            <?php
            if (!isset($_SESSION['user_id'])) {
                echo '<p>If you are not registered, please <a href="register.php">register here</a>.</p>';
            }
            ?>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Email -->
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" value="<?php echo $email; ?>">
            <span class="error"><?php echo $emailErr; ?></span>
            <br>

            <!-- Password -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <span class="error"><?php echo $passwordErr; ?></span>
            <br>

            <input type="submit" value="Login">
        </form>
    </div>
</div>
</body>
</html>

