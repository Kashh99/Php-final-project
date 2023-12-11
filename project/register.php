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
$fullName = $email = $password = $confirmPassword = "";
$fullNameErr = $emailErr = $passwordErr = $confirmPasswordErr = $registrationError = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Full Name
    if (empty($_POST["fullName"])) {
        $fullNameErr = "Full Name is required";
    } else {
        $fullName = test_input($_POST["fullName"]);
        // Check if name contains only letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/", $fullName)) {
            $fullNameErr = "Only letters and white space allowed";
        }
    }

    // Validate Email
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

    // Validate Confirm Password
    if (empty($_POST["confirmPassword"])) {
        $confirmPasswordErr = "Please confirm the password";
    } else {
        $confirmPassword = test_input($_POST["confirmPassword"]);
        // Check if passwords match
        if ($confirmPassword !== $password) {
            $confirmPasswordErr = "Passwords do not match";
        }
    }

    // If there are no validation errors, check if the email is already registered
    if (empty($fullNameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
        $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
        $result_check_email = $conn->query($sql_check_email);

        if ($result_check_email->num_rows > 0) {
            // Email is already registered
            echo "Email is already in use. Please use a different email.";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user data into the 'users' table
            $sql_insert_user = "INSERT INTO users (full_name, email, password) VALUES ('$fullName', '$email', '$hashedPassword')";

            if ($conn->query($sql_insert_user) === TRUE) {
                // Registration successful
                echo "Registration successful";

                // Retrieve the user ID and store it in the session
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;

                // Redirect to the login page
                header("Location: profile.php");
                exit();
            } else {
                echo "Error: " . $sql_insert_user . "<br>" . $conn->error;
            }
        }
    }
}

// Close the database connection
$conn->close();

// Function to clean and validate input data
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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

    <title>User Registration</title>
</head>
<body>
<header>
    <h1>VibeSET</h1>
</header>

<div class="registerbackg">
<img src="./assets/img/registerb.jpg" alt="My Image">
    <div class="register-form">
        <h2>User Registration</h2>

        <!-- Display message to prompt registration if not logged in -->
        <div class="register-message">
            <?php
            if (!isset($_SESSION['user_id'])) {
                echo '<p>If you are already registered, please <a href="login.php">Login here</a>.</p>';
            }
            ?>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <!-- Full Name -->
            <label for="fullName">Full Name:</label>
            <input type="text" id="fullName" name="fullName" value="<?php echo $fullName; ?>">
            <span class="error"><?php echo $fullNameErr; ?></span>
            <br>

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

            <!-- Confirm Password -->
            <label for="confirmPassword">Confirm Password:</label>
            <input type="password" id="confirmPassword" name="confirmPassword">
            <span class="error"><?php echo $confirmPasswordErr; ?></span>
            <br>

            <input type="submit" value="Register">
        </form>
    </div>
</div>


</body>
</html>
