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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: login.php");
    exit();
}

// Retrieve user information from the session
$user_id = $_SESSION['user_id'];

// Retrieve user information from the database
$sql_get_user = "SELECT * FROM users WHERE id = $user_id";
$result_get_user = $conn->query($sql_get_user);

if ($result_get_user->num_rows > 0) {
    $user_data = $result_get_user->fetch_assoc();
    $user_name = $user_data['full_name'];
} else {
    $user_name = "Guest";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resources = $_POST['resources'];

    // Insert the data into the database
    $sql_insert_data = "INSERT INTO learning_paths (user_id, title, description, resources) VALUES ('$user_id', '$title', '$description', '$resources')";

    if ($conn->query($sql_insert_data) === TRUE) {
        echo "Learning path created successfully!";
        header("Location: homepage.php");
    } else {
        echo "Error: " . $sql_insert_data . "<br>" . $conn->error;
    }
}



// Define upload directory and allowed file types
$upload_dir = 'uploads/';
$allowed_types = array('jpg', 'jpeg', 'png', 'gif');

// Process file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];

    // Validate file type
    $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_type, $allowed_types)) {
        echo "Invalid file type. Allowed types: " . implode(', ', $allowed_types);
        exit();
    }

    // Generate a unique filename
    $file_name = 'profile_' . $user_id . '.' . $file_type;
    $file_path = $upload_dir . $file_name;

    // Move the uploaded file to the target directory
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Store the file path in the database
        $servername = "localhost:3307";
        $username = "root";
        $password = "";
        $dbname = "project";

        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql_insert_picture = "INSERT INTO profile_pictures (user_id, image_path) VALUES ('$user_id', '$file_path')";
        if ($conn->query($sql_insert_picture) === TRUE) {
            echo "Profile picture uploaded successfully!";
            header("Location: profile.php");
        } else {
            echo "Error uploading profile picture: " . $conn->error;
        }
    } else {
        echo "Error uploading file.";
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Comfortaa:wght@500&display=swap" rel="stylesheet">

    <title>User Profile</title>

    <script>
        function uploadFile() {
            document.getElementById("file-upload").click();
        }
    </script>
</head>
<body>
<header>
    <h1>VibeSET</h1>
</header>

<!-- Navbar -->
<nav>
    <div class="profile-container">
        <!-- Left side of the navbar -->
        <Welcome, style="cursor: pointer;" onclick="location.href='homepage.php';">Welcome, <?php echo $user_name; ?></span>
    </div>

    <!-- Right side of the navbar -->
    <div class="navbar-buttons">
        <!-- Add the profile picture upload form -->
        <div class="upload-container">
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="form_type" value="profile_picture">
            <label for="file-upload" class="custom-file-upload">
                Upload Profile Picture
            </label>
            <input id="file-upload" type="file" name="profile_picture" accept="image/*" style="display:none;">
            <input type="submit" value="Submit">
        </form>
        </div>
    </div>
</nav>

<!-- Learning Path Creation Form -->
<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="form_type" value="learning_path">
    <label for="title">Title:</label>
    <input type="text" name="title" required><br>

    <label for="description">Description:</label>
    <textarea name="description" required></textarea><br>

    <label for="resources">Resources (comma-separated):</label>
    <input type="text" name="resources" required><br>

    <input type="submit" value="Create Learning Path">
</form>

</body>
</html>
