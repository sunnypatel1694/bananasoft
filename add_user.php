<?php
session_start();
include("connection.php");

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password

    // Insert the new user into the database
    $query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    if (mysqli_query($conn, $query)) {
        // Redirect to the user table page (user.php)
        header("Location: user.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
