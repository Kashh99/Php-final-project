<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Handle the case where the user is not logged in
    echo 'You must be logged in to vote.';
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$path_id = isset($_GET['path_id']) ? $_GET['path_id'] : null;
$vote_type = isset($_GET['vote_type']) ? $_GET['vote_type'] : null;

// Check if $path_id and $vote_type are not null before proceeding
if ($path_id === null || $vote_type === null) {
    echo 'Invalid parameters (path_id or vote_type is null).';
    exit();
}

// Check if the user has already voted for this pathway
$sql_check_vote = "SELECT * FROM user_votes WHERE user_id = $user_id AND path_id = $path_id";
$result_check_vote = $conn->query($sql_check_vote);

if ($result_check_vote === false) {
    echo 'Error checking vote: ' . $conn->error;
    echo 'Reached point A';
} else {
    if ($result_check_vote->num_rows > 0) {
        // The user has already voted for this pathway
        echo 'You have already voted for this pathway.';
        echo 'Reached point B';
    } else {
        // Insert the vote into the user_votes table
        $sql_insert_vote = "INSERT INTO user_votes (user_id, path_id, vote_type) VALUES ($user_id, $path_id, '$vote_type')";
        $result_insert_vote = $conn->query($sql_insert_vote);

        // Update the pathway's upvotes or downvotes count
        $sql_update_count = "UPDATE learning_paths SET ";
        if ($vote_type == 'upvote') {
            $sql_update_count .= "upvotes = upvotes + 1 ";
        } elseif ($vote_type == 'downvote') {
            $sql_update_count .= "downvotes = downvotes + 1 ";
        }
        $sql_update_count .= "WHERE id = $path_id";
        $result_update_count = $conn->query($sql_update_count);

        if ($result_insert_vote === false || $result_update_count === false) {
            echo 'Error recording vote: ' . $conn->error;
            echo 'Reached point C';
        } else {
            echo 'Vote recorded successfully.';
            echo 'Reached point D';
        }
    }
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn->close();
?>
