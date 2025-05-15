<?php
// Start session and include database connection
session_start();
include("connection.php");

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form values and sanitize
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validate inputs (you can add more validation if needed)
    if (empty($username) || empty($email)) {
        $_SESSION['message'] = "All fields are required.";
        header("Location: user_table.php"); // redirect back to the main page
        exit();
    }

    // Optional: check for duplicate email (exclude current user)
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->bind_param("si", $email, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['message'] = "Email already in use by another user.";
        header("Location: user_table.php");
        exit();
    }

    // Prepare the update statement
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully.";
    } else {
        $_SESSION['message'] = "Error updating user: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the main table page
    header("Location: user.php");
    exit();
} else {
    // Invalid request
    $_SESSION['message'] = "Invalid request.";
    header("Location: user_table.php");
    exit();
}
?>
