<?php
include("connection1.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $category = strtoupper($_POST['category']); // Convert category to uppercase

    // Update query to modify the category
    $query = "UPDATE abbreviations SET category = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $category, $id);

    if (mysqli_stmt_execute($stmt)) {
        // Successfully updated
        echo "Category updated successfully!";
    } else {
        echo "Error updating category.";
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<?php
include("connection1.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $category = strtoupper(trim($_POST['category']));

    $query = "UPDATE abbreviations SET category = '$category' WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Category updated successfully!'); window.location.href='category.php';</script>";
    } else {
        echo "<script>alert('Update failed.'); window.location.href='category.php';</script>";
    }
}
?>

