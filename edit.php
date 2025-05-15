<?php
// edit_abbreviation.php
include("connection1.php");

if (isset($_POST['id']) && isset($_POST['abbreviation']) && isset($_POST['meaning']) && isset($_POST['category'])) {
    $id = $_POST['id'];
    $abbreviation = $_POST['abbreviation'];
    $meaning = $_POST['meaning'];
    $category = $_POST['category'];

    // Update query
    $query = "UPDATE abbreviations SET abbreviation = '$abbreviation', meaning = '$meaning', category = '$category' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        // Redirect or success message
        echo "Record updated successfully!";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
