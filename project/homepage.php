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

$path_id = isset($_GET['path_id']) ? $_GET['path_id'] : null;
$vote_type = isset($_GET['vote_type']) ? $_GET['vote_type'] : null;


// Retrieve user information from the database
$sql_get_user = "SELECT * FROM users WHERE id = $user_id";
$result_get_user = $conn->query($sql_get_user);

if ($result_get_user->num_rows > 0) {
    $user_data = $result_get_user->fetch_assoc();
    $user_name = $user_data['full_name'];

    // Retrieve user profile picture path
    $sql_get_picture = "SELECT image_path FROM profile_pictures WHERE user_id = $user_id";
    $result_get_picture = $conn->query($sql_get_picture);

    if ($result_get_picture->num_rows > 0) {
        $picture_data = $result_get_picture->fetch_assoc();
        $profile_picture_path = $picture_data['image_path'];
    } else {
        // Default profile picture path or placeholder image path
        $profile_picture_path = 'path_to_default_image.jpg';
    }
} else {
    // Handle the case where user data is not found
    $user_name = "Guest";
}



$sql_get_paths = "SELECT id, title, description, resources, upvotes, downvotes FROM learning_paths";

$result_get_paths = $conn->query($sql_get_paths);

// Get the search query from the GET parameters
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

// If a search query is present, modify the SQL query to include the search condition
if (!empty($searchQuery)) {
    $sql_get_paths .= " WHERE title LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%'";
    $result_get_paths = $conn->query($sql_get_paths);
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VibeSET</title>
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Comfortaa:wght@500&family=Shizuru&display=swap" rel="stylesheet"> 
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap">
<script src="./assets/js/scripts.k"></script>


</head>
<body>
<header>
    <h1>VibeSET</h1>
</header>
<nav>
<div class="profile-container">
    <!-- Left side of the navbar -->
    <span style="cursor: pointer;" onclick="location.href='homepage.php';">
            <?php
            echo '<img src="' . htmlspecialchars($profile_picture_path) . '" alt="Profile Picture">';
            echo 'Welcome, ' . $user_name;
            ?>
        </span>
</div>


     <!-- Right side of the navbar -->
     <div class="navbar-buttons">
        <!-- <a href="read_pathways.php">Read Pathways</a> -->
        <a href="edit_pathways.php">Edit Pathways</a>
        <a href="profile.php">Customize Profile</a>
        <a href="register.php">Sign-up</a>
        <!-- <a href="login.php">Login</a> -->
        <a href="logout.php">LogOut</a>
        
        
        
    </div>
</nav>

<!-- Add the dynamic search form -->
<div class="search-container">
        <input type="text" name="search" id="search" placeholder="Search by title or description" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <div class="search-results">
            <?php
            if (!empty($searchQuery)) {
                if ($result_get_paths->num_rows > 0) {
                    echo '<h2>Search Results</h2>';
                    while ($row = $result_get_paths->fetch_assoc()) {
                        echo '<div class="learning-path">';
                        echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
                        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                        $resources = explode(', ', $row['resources']);
                        echo '<p>Resources: ';
                        foreach ($resources as $resource) {
                            echo '<a href="' . htmlspecialchars($resource) . '" target="_blank">' . htmlspecialchars($resource) . '</a>, ';
                        }
                        echo '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No results found.</p>';
                }
            }
            ?>
        </div>
    </div>



<!-- Display Learning Paths -->
<div>
    <?php
    if ($result_get_paths->num_rows > 0) {
        while ($row = $result_get_paths->fetch_assoc()) {
            echo '<div class="learning-path">';
            echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
            echo '<p>' . htmlspecialchars($row['description']) . '</p>';
            $resources = explode(', ', $row['resources']);
            echo '<p>Resources: ';
            foreach ($resources as $resource) {
                echo '<a href="' . htmlspecialchars($resource) . '" target="_blank">' . htmlspecialchars($resource) . '</a>, ';
            }
            echo '</p>';

            echo '<div class="voting-buttons">';
            echo '<button class="upvote-btn" onclick="votePath(' . $row['id'] . ', \'upvote\')">&#x1F44D; Upvote</button>'; 
            echo '<button class="downvote-btn" onclick="votePath(' . $row['id'] . ', \'downvote\')">&#x1F44E; Downvote</button>';
            echo '</div>';

            echo '<div class="vote-count">';
            echo 'Upvotes: ' . $row['upvotes'] . ' | Downvotes: ' . $row['downvotes'];
            echo '</div>';

            echo '</div>';
        }
    } else {
        echo '<p>No learning paths available.</p>';
    }
    ?>
</div>

    <!-- Welcome Pop-up -->
    <div class="welcome-popup" id="welcomePopup">
        <div class="welcome-content">
            <span class="close-btn" onclick="closeWelcomePopup()">&times;</span>
            <h2>Welcome to Our Website!</h2>
            <p>Enjoy your stay and explore our content.</p>
        </div>
    </div>



<script>
    // Add event listener for input event on the search input
    var timeoutId;

    document.getElementById('search').addEventListener('input', function () {
        // Clear the previous timeout
        clearTimeout(timeoutId);

        // Set a new timeout to allow the user to finish typing
        timeoutId = setTimeout(function () {
            // Redirect to the homepage with the search query as a parameter
            window.location.href = 'homepage.php?query=' + encodeURIComponent(document.getElementById('search').value);
        }, 1000); // Adjust the delay (in milliseconds) as needed
    });

//     function votePath($pathId, $voteType) {
//     // Validate and sanitize input

//     // Update the database based on the vote type
//     if ($voteType === 'upvote') {
//         $sql_update_vote = "UPDATE learning_paths SET upvotes = upvotes + 1 WHERE id = $pathId";
//     } elseif ($voteType === 'downvote') {
//         $sql_update_vote = "UPDATE learning_paths SET downvotes = downvotes + 1 WHERE id = $pathId";
//     }

//     // Execute the update query
//     $conn->query($sql_update_vote);

//     // Return success or any relevant information
//     echo "Vote recorded successfully!";
// }


</script>



    <!-- Copyright notice -->
    <footer>
        <p>&copy; 2023 Vibeset. All rights reserved.</p>
    </footer>

</body>
</html>
