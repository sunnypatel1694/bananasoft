<?php
session_start();  // Start the session

// Destroy the session and unset all session variables
session_unset();
session_destroy();

// Redirect to login page after logout
header("Location: login.php");
exit();
?>
