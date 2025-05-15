<?php
include("connection1.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $abbreviation = $_POST['abbreviation'];
    $meaning = $_POST['meaning'];
    $category = $_POST['category'];

    // Insert new abbreviation into the abbreviations table
    $query = "INSERT INTO abbreviations (abbreviation, meaning, category) VALUES ('$abbreviation', '$meaning', '$category')";
    
    if (mysqli_query($conn, $query)) {
        echo "New abbreviation added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
