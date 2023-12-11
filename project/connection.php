<?php
// Specify the server name, username, password, and database name
$server_name = "localhost";
$user_name = "root";
$password = "";
$database_name = "project"; // Replace with your actual database name

// Create a connection to the MySQL database
$connection = new mysqli($server_name, $user_name, $password, $database_name);

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

echo "Connected successfully";

// Perform your database operations here

// Close the connection when you are done
$connection->close();
?>