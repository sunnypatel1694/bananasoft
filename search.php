<?php
include("connection1.php");

if (isset($_POST['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_POST['search']);

    // Modify the query to only search in the abbreviation column
    $query = "SELECT * FROM abbreviations WHERE abbreviation LIKE '%$searchTerm%'";
    $result = mysqli_query($conn, $query);

    // Prepare table data for AJAX response
    $output = '';
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= "<tr>";
            $output .= "<td>" . strtoupper($row['id']) . "</td>";
            $output .= "<td>" . strtoupper($row['abbreviation']) . "</td>";
            $output .= "<td>" . strtoupper($row['meaning']) . "</td>";
            $output .= "<td>" . strtoupper($row['category']) . "</td>";
            $output .= "</tr>";
        }
    } else {
        $output = "<tr><td colspan='4' class='text-center'>No data found</td></tr>";
    }

    // Return the updated table data
    echo json_encode($output);
}
?>
