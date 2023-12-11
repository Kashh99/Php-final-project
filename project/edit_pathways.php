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

$user_id = $_SESSION['user_id'];

// Retrieve user information from the database
$sql_get_user = "SELECT * FROM users WHERE id = $user_id";
$result_get_user = $conn->query($sql_get_user);

if ($result_get_user->num_rows > 0) {
    $user_data = $result_get_user->fetch_assoc();
    $user_name = $user_data['full_name'];
} else {
    // Handle the case where user data is not found
    $user_name = "Guest";
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

    <title>Learning Path Browsing</title>

</head>
<body>
<header>
    <h1>VibeSET</h1>
</header>
<nav>
<div class="profile-container">
    <!-- Left side of the navbar -->
    <span style="cursor: pointer;" onclick="location.href='homepage.php';">Welcome, <?php echo $user_name; ?></span>
</div>
     <!-- Right side of the navbar -->
     <div class="navbar-buttons">
     <a href="homepage.php"> Home</a>
     <a href="cpath.php">Create pathway</a>
        <!-- <a href="read_pathways.php">Read Pathways</a> -->
        <!-- <a href="edit_pathways.php">Edit Pathways</a> -->
        <a href="profile.php">Edit Profile</a>
        <!-- <a href="register.php">Sign-up</a> -->
        <!-- <a href="login.php">Login</a> -->
        <a href="logout.php">LogOut</a>

    
    </div>
    <!-- Display the title -->

</nav>
<h1>Pathways by <?php echo $user_name; ?></h1>

</body>
</html>

<?php
// Handle form submission for pathway editing and deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_pathway']) && isset($_POST['pathway_id'])) {
        // User clicked "Edit" for a pathway
        $pathway_id = $_POST['pathway_id'];
        // Retrieve the existing pathway details from the database
        $sql_get_pathway = "SELECT * FROM learning_paths WHERE id = $pathway_id AND user_id = {$_SESSION['user_id']}";
        $result_get_pathway = $conn->query($sql_get_pathway);

        if ($result_get_pathway->num_rows > 0) {
            $row = $result_get_pathway->fetch_assoc();
            // Display a form to edit pathway details
            ?>
            <div class="learning-path">
                <h3>Edit Pathway</h3>
                <form method="post" action="edit_pathways.php">
                    <input type="hidden" name="pathway_id" value="<?php echo $row['id']; ?>">
                    <label for="title">Title:</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required><br>

                    <label for="description">Description:</label>
                    <textarea name="description" required><?php echo htmlspecialchars($row['description']); ?></textarea><br>

                    <label for="resources">Resources (comma-separated):</label>
                    <input type="text" name="resources" value="<?php echo htmlspecialchars($row['resources']); ?>" required><br>

                    <input type="submit" name="update_pathway" value="Update Pathway">
                </form>
            </div>
            <?php
        } else {
            echo "Pathway not found.";
        }
    } elseif (isset($_POST['delete_pathway']) && isset($_POST['pathway_id'])) {
        // User clicked "Delete" for a pathway
        $pathway_id = $_POST['pathway_id'];
        // Delete the pathway from the database
        $sql_delete_pathway = "DELETE FROM learning_paths WHERE id = $pathway_id AND user_id = {$_SESSION['user_id']}";
        if ($conn->query($sql_delete_pathway) === TRUE) {
            echo "Pathway deleted successfully!";
        } else {
            echo "Error deleting pathway: " . $conn->error;
        }
    } elseif (isset($_POST['update_pathway']) && isset($_POST['pathway_id'])) {
        // User submitted the form to update the pathway
        $pathway_id = $_POST['pathway_id'];
        $new_title = $_POST['title'];
        $new_description = $_POST['description'];
        $new_resources = $_POST['resources'];

        // Update the pathway in the database
        $sql_update_pathway = "UPDATE learning_paths SET title='$new_title', description='$new_description', resources='$new_resources' WHERE id=$pathway_id AND user_id={$_SESSION['user_id']}";

        if ($conn->query($sql_update_pathway) === TRUE) {
            echo "Pathway updated successfully!";
        } else {
            echo "Error updating pathway: " . $conn->error;
        }
    }
}

// Retrieve learning paths created by the user
$sql_get_paths_by_user = "SELECT * FROM learning_paths WHERE user_id = {$_SESSION['user_id']}";
$result_get_paths_by_user = $conn->query($sql_get_paths_by_user);

// Display Learning Paths created by the user
if ($result_get_paths_by_user->num_rows > 0) {
    while ($row = $result_get_paths_by_user->fetch_assoc()) {
        echo '<div class="learning-path">';
        echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
        echo '<p>Resources: ' . htmlspecialchars($row['resources']) . '</p>';
        echo '<form method="post" action="edit_pathways.php">';
        echo '<input type="hidden" name="pathway_id" value="' . $row['id'] . '">';
        echo '<input type="submit" name="edit_pathway" value="Edit">';
        echo '</form>';
        echo '<form method="post" action="edit_pathways.php">';
        echo '<input type="hidden" name="pathway_id" value="' . $row['id'] . '">';
        echo '<input type="submit" name="delete_pathway" value="Delete">';
        echo '</form>';
        echo '</div>';
    }
} else {
    echo '<p>No learning paths created by you.</p>';
}

// Close the database connection
$conn->close();
?>
