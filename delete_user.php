<?php
session_start();
include("connection.php");

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Delete the user from the database
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);

    if ($stmt->execute()) {
        // Redirect to the user table with a success message
        header("Location: user.php?deleted=1");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
}
?>
