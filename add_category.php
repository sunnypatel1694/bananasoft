<?php
include("connection1.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = strtoupper(trim($_POST['category']));

    // Check if category already exists to avoid duplicates
    $checkQuery = "SELECT id FROM categories WHERE category = ?";
    $checkStmt = mysqli_prepare($conn, $checkQuery);

    if (!$checkStmt) {
        die("Check query failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($checkStmt, "s", $category);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        // Category already exists
        header("Location: category.php?exists=1");
        exit();
    }

    // Insert into category table
    $query = "INSERT INTO categories (category) VALUES (?)";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("SQL error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $category);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: category.php?success=1");
        exit();
    } else {
        echo "Error inserting category: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>


