<?php
$servername = "localhost"; // your server
$username = "root";        // your username
$password = "";            // your password
$dbname = "auth_db";   // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
