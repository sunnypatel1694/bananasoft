<?php
include("connection1.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $deleteQuery = "DELETE FROM abbreviations WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
