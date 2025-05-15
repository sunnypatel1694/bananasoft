<?php
session_start();  // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the user's email from the session
$userEmail = $_SESSION['email'];
?>

<?php 
    include("header1.php"); 
    include("connection1.php"); 
?>
<?php
// Fetch the categories for the abbreviation form
$categoryQuery = "SELECT category FROM categories";
$categoryResult = mysqli_query($conn, $categoryQuery);
$categories = [];

if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row['category'];
}
}
?>
<?php
    // Check if the form is submitted to add a new record
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['abbreviation']) && isset($_POST['meaning']) && isset($_POST['category'])) {
        // Retrieve form data and sanitize input
        $abbreviation = mysqli_real_escape_string($conn, strtoupper($_POST['abbreviation']));
        $meaning = mysqli_real_escape_string($conn, $_POST['meaning']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        
        // Prepare SQL to insert new record into abbreviations table
        $insertQuery = "INSERT INTO abbreviations (abbreviation, meaning, category) VALUES ('$abbreviation', '$meaning', '$category')";

        if (mysqli_query($conn, $insertQuery)) {
            // Record inserted successfully, redirect to the same page to refresh the table
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            // If error occurs, show an error message
            echo "<script>alert('Error adding record: " . mysqli_error($conn) . "');</script>";
        }
    }
?>

<?php

    // Default records per page is 20
    $recordsPerPage = isset($_GET['recordsPerPage']) ? $_GET['recordsPerPage'] : 20;

    // Calculate total records
    $queryTotal = "SELECT COUNT(*) AS total FROM abbreviations";
    $resultTotal = mysqli_query($conn, $queryTotal);
    $totalRecords = mysqli_fetch_assoc($resultTotal)['total'];

    // Calculate total pages
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Get current page
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
    $startFrom = ($currentPage - 1) * $recordsPerPage;

    // Query to fetch records with limit
    $query = "SELECT * FROM abbreviations LIMIT $startFrom, $recordsPerPage";
    $result = mysqli_query($conn, $query);

    $categories = [];
    $queryCategory = "SELECT DISTINCT category FROM abbreviations";
    $resultCategory = mysqli_query($conn, $queryCategory);
    if ($resultCategory) {
        while ($row = mysqli_fetch_assoc($resultCategory)) {
            $categories[] = $row['category']; // Store categories in the array
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Table with Search, Pagination, and Export</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/SheetJS/0.17.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.21/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/SheetJS/0.17.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    
    <link rel="stylesheet" href="style1.css"> 
    <style>
        footer {
            margin-top: auto; /* Push footer to the bottom */
            text-align: center; /* Center footer text */
            background-color: #343a40; /* Dark background for footer */
            color: white; /* White text */
            padding: 7px; /* Some padding for better visibility */
            width: 100%;
        }

        body {
            background-image: url('11.jpg'); /* Set your image path here */
            background-size: cover;          /* Ensures the image covers the entire screen */
            background-position: center;     /* Centers the background image */
            background-attachment: fixed;    /* Keeps the image fixed during scrolling */
            height: 100vh;                   /* Ensures the body takes up the full height */
            margin: 0; 
            flex 1;                      /* Removes default margin from the body */
        }

        /* Styling for the main form container */
        .main-container {
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white background for readability */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); /* Optional shadow for the form */
            margin-top: 30px; /* Adds space from the top of the screen */
        }
        
        .uppercase-input {
            text-transform: uppercase;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background-color: #f1f1f1;
        }

        /* Logout button styling */
        .logout-button {
            background-color: red;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
        
        .logout-button:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="main-container">
    <div class="container mt-3">
        <h2 class="mb-2">Abbreviation Table</h2>
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exampleModal" data-bs-whatever="@mdo">Add New Abbreviation</button>
             <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add New Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

      <div class="modal-body">
        <form method="POST" action="" id="addForm">
          <div class="mb-3">
          <label for="abbreviation" class="form-label"><b>Abbreviation</b></label>
          <input type="text" class="form-control uppercase-input" id="abbreviation" name="abbreviation" value="<?php echo isset($row['abbreviation']) ? $row['abbreviation'] : ''; ?>" required>
          </div>

          <div class="mb-3">
          <label for="meaning" class="form-label"><b>Meaning</b></label>
                <input type="text" class="form-control uppercase-input" id="meaning" name="meaning" value="<?php echo isset($row['meaning']) ? $row['meaning'] : ''; ?>" required>
          </div>

          <div class="mb-3">
          <label for="category" class="form-label"><b>Category</b></label>
          <select class="form-control" id="category" name="category" required>
                <option value="">Select Category</option>
                <?php
                    $catQuery = "SELECT * FROM categories ORDER BY category ASC";
                    $catResult = mysqli_query($conn, $catQuery);
                    while ($catRow = mysqli_fetch_assoc($catResult)) {
                        echo "<option value=\"" . strtoupper($catRow['category']) . "\">" . strtoupper($catRow['category']) . "</option>";
                    }
                ?>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="editAddBtn">Submit</button>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Edit Modal -->
<div class="modal fade" style="margin-top: 100px" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="" method="POST">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label for="editAbbreviation" class="form-label">Abbreviation</label>
                        <input type="text" class="form-control" id="editAbbreviation" name="abbreviation" required>
                    </div>
                    <div class="mb-3">
                        <label for="editMeaning" class="form-label">Meaning</label>
                        <input type="text" class="form-control" id="editMeaning" name="meaning" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <select class="form-control" id="editCategory" name="category" required>
                            <option value="">Select Category</option>
                            <!-- Populate categories dynamically -->
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="editSubmitBtn">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" style="margin-top:100px">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this record?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

        <!-- Row for search and export buttons -->
        <div class="row mb-3">
            <!-- Left column for Search and Pagination -->
            <div class="col-6">
                <!-- Search Input (small size) -->
                <input type="text" id="searchInput" class="form-control form-control mb-3" placeholder="Search in Table...">

                <!-- Records per page dropdown (small size) -->
                <select id="recordsPerPage" class="form-select form-select-sm mb-3" aria-label="Records per page">
                        <option value="10" <?= $recordsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?= $recordsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="30" <?= $recordsPerPage == 30 ? 'selected' : ''; ?>>30</option>
                    </select>
            </div>

            <!-- Right column for Export buttons -->
            <div class="col-6">
                <!-- Export Buttons -->
                <button id="exportPdf" class="btn btn-danger mb-3">Export to PDF</button><br />
            </div>
        </div>

        <!-- Table -->
        <table id="tableData" class="table table-striped table-bordered" style="background-color:white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Abbreviation</th>
                        <th>Meaning</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . strtoupper($row['abbreviation']) . "</td>";
                                echo "<td>" . strtoupper($row['meaning']) . "</td>";
                                echo "<td>" . strtoupper($row['category']) . "</td>";
                                echo "<td>
                                    <button class='btn btn-primary btn-sm editBtn' data-bs-toggle='modal' data-bs-target='#editModal' data-id='".$row['id']."' data-abbreviation='".$row['abbreviation']."' data-meaning='".$row['meaning']."' data-category='".$row['category']."'>Edit</button>
                                    <button class='btn btn-danger btn-sm deleteBtn' data-id='".$row['id']."'>Delete</button>
                                </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>No data found</td></tr>";
                        }
                    ?>
                </tbody>
            </table>

        <!-- Pagination -->
            <div class="row mb-3">
                <div class="col-12">
                    <nav id="paginationNav" aria-label="Page navigation">
                        <ul class="pagination pagination-sm">
                            <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $currentPage - 1; ?>&recordsPerPage=<?= $recordsPerPage; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $i; ?>&recordsPerPage=<?= $recordsPerPage; ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $currentPage + 1; ?>&recordsPerPage=<?= $recordsPerPage; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
<?php include('footer1.php'); ?>
<script>
        // When the records per page dropdown changes, reload the page
        document.getElementById('recordsPerPage').addEventListener('change', function() {
            const recordsPerPage = this.value;
            window.location.href = "?page=1&recordsPerPage=" + recordsPerPage;
        });
    </script>

<script>
        {
            // Calculate the pagination range
            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedData = data.slice(startIndex, endIndex);

            // Loop through data and add rows to the table
            paginatedData.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `   
                    <td>${item.id}</td>
                    <td>${item.abbreviation}</td>
                    <td>${item.meaning}</td>
                    <td>${item.category}</td>
                    <td><button class="btn btn-primary btn-sm">View</button></td>
                `;
                tableBody.appendChild(row);
            });

            // Update pagination
            updatePagination(data.length);
        }

        // Function to handle pagination
        function updatePagination(totalItems) {
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            const paginationNav = document.querySelector("#paginationNav ul");

            // Enable/Disable Previous/Next buttons
            document.getElementById("previousPage").classList.toggle("disabled", currentPage === 1);
            document.getElementById("nextPage").classList.toggle("disabled", currentPage === totalPages);

            // Clear previous pagination
            paginationNav.innerHTML = ""; 

            // Add Previous Button
            const prevItem = document.createElement("li");
            prevItem.classList.add("page-item");
            prevItem.innerHTML = `<a class="page-link" href="#" id="previousPage">Previous</a>`;
            prevItem.querySelector("a").addEventListener("click", function () {
                if (currentPage > 1) {
                    currentPage--;
                    loadTableData(filteredData);
                }
            });
            paginationNav.appendChild(prevItem);

            // Add Page Numbers (if needed)
            for (let i = 1; i <= totalPages; i++) {
                const pageItem = document.createElement("li");
                pageItem.classList.add("page-item");
                pageItem.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                pageItem.querySelector("a").addEventListener("click", function () {
                    currentPage = i;
                    loadTableData(filteredData);
                });
                paginationNav.appendChild(pageItem);
            }

            // Add Next Button
            const nextItem = document.createElement("li");
            nextItem.classList.add("page-item");
            nextItem.innerHTML = `<a class="page-link" href="#" id="nextPage">Next</a>`;
            nextItem.querySelector("a").addEventListener("click", function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadTableData(filteredData);
                }
            });
            paginationNav.appendChild(nextItem);
        }
        // Handle records per page selection
        document.getElementById("recordsPerPage").addEventListener("change", function () {
            itemsPerPage = parseInt(this.value, 10);
            loadTableData(filteredData);
        });

        // PDF Export functionality
        document.getElementById("exportPdf").addEventListener("click", function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text("Table Data", 10, 10);
            let yOffset = 20;

            filteredData.forEach(item => {
                doc.text(`${item.id} - ${item.name} - ${item.email} - ${item.age}`, 10, yOffset);
                yOffset += 10;
            });

            doc.save("table_data.pdf");
        });

        // Fetch and load static data on page load
        loadTableData(filteredData);

         // Ensure abbreviation and meaning fields are uppercase
            document.getElementById('abbreviation').addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

            document.getElementById('meaning').addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });

        // Edit Button click handler
        document.querySelectorAll('.editBtn').forEach(button => {
        button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const abbreviation = this.getAttribute('data-abbreviation');
        const meaning = this.getAttribute('data-meaning');
        const category = this.getAttribute('data-category');

        // Set the values in the modal fields
        document.getElementById('editId').value = id;
        document.getElementById('editAbbreviation').value = abbreviation;
        document.getElementById('editMeaning').value = meaning;
        
        const categoryDropdown = document.getElementById('editCategory');
        for (let i = 0; i < categoryDropdown.options.length; i++) {
            if (categoryDropdown.options[i].value === category) {
                categoryDropdown.selectedIndex = i;
                break;
            }
        }
    });
});


// Edit Form Submit
document.getElementById('editSubmitBtn').addEventListener('click', function() {
    const formData = new FormData(document.getElementById('editForm'));

    // Send a request to update the record
    fetch('edit.php', {
        method: 'POST',
        body: formData,
    }).then(response => {
        if (response.ok) {
            // Reload the page or update the table dynamically
            location.reload();
        }
    });
});
    </script>
    <script>
        document.getElementById("searchInput").addEventListener("keyup", function () {
        const searchText = this.value.toLowerCase(); // Get the search text, converted to lowercase
        const rows = document.querySelectorAll("#tableData tbody tr"); // Get all table rows in the tbody

        rows.forEach(row => {
        const abbreviation = row.querySelector("td:nth-child(2)").textContent.toLowerCase(); // Get the abbreviation from the second column
        const meaning = row.querySelector("td:nth-child(3)").textContent.toLowerCase(); // Get the meaning from the third column
        const category = row.querySelector("td:nth-child(4)").textContent.toLowerCase(); 
        // Check if the search text is found in any of the columns
        if (abbreviation.includes(searchText) || meaning.includes(searchText) || category.includes(searchText)) {
            row.style.display = ""; // Show the row if search matches
        } else {
            row.style.display = "none"; // Hide the row if search doesn't match
        }
    });
});
</script>

<script>
        document.querySelectorAll('.editBtn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const abbreviation = this.getAttribute('data-abbreviation');
        const meaning = this.getAttribute('data-meaning');
        const category = this.getAttribute('data-category');

        // Set the values in the modal fields
        document.getElementById('editId').value = id;
        document.getElementById('editAbbreviation').value = abbreviation;
        document.getElementById('editMeaning').value = meaning;
        
        // Set the category dropdown
        const categoryDropdown = document.getElementById('editCategory');
        for (let i = 0; i < categoryDropdown.options.length; i++) {
            if (categoryDropdown.options[i].value === category) {
                categoryDropdown.selectedIndex = i;
                break;
            }
        }
    });
});

   // Populate the category dropdown in the edit modal dynamically
const editCategoryDropdown = document.getElementById('editCategory');
<?php
    // Echo out the PHP array into a JavaScript array
    echo 'const categories = ' . json_encode($categories) . ';';
?>

// Clear existing options and populate
editCategoryDropdown.innerHTML = ''; // Clear existing options
categories.forEach(category => {
    const option = document.createElement('option');
    option.value = category;
    option.textContent = category.toUpperCase();
    editCategoryDropdown.appendChild(option);
});

// Ensure abbreviation and meaning fields are uppercase
document.getElementById('editAbbreviation').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

document.getElementById('editMeaning').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

document.getElementById('editSubmitBtn').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent default form submission
    const formData = new FormData(document.getElementById('editForm'));

    // Send the updated data to the server (edit.php)
    fetch('edit.php', {
        method: 'POST',
        body: formData,
    }).then(response => {
        if (response.ok) {
            // If the response is OK, reload the page or update the table dynamically
            location.reload();
        } else {
            alert("Error updating the record.");
        }
    }).catch(error => {
        alert("An error occurred: " + error.message);
    });
});
</script>


<script>
    // Export to PDF Button Event Listener
    document.getElementById('exportPdf').addEventListener('click', function() {
    const { jsPDF } = window.jspdf; // jsPDF instance
    const doc = new jsPDF();
    
    // Get the table element
    const table = document.getElementById('tableData');
    
    // Table Header
    let headers = [];
    let headersRow = table.rows[0].cells;
    for (let i = 0; i < headersRow.length; i++) {
        headers.push(headersRow[i].innerText);
    }

    // Table Rows Data
    let rows = [];
    for (let i = 1; i < table.rows.length; i++) { // Start from 1 to skip header row
        let row = [];
        let rowData = table.rows[i].cells;
        for (let j = 0; j < rowData.length - 1; j++) { // Skip the last cell (Actions column)
            row.push(rowData[j].innerText);
        }
        rows.push(row);
    }

    // Add table to PDF
    doc.autoTable({
        head: [headers],  // Table headers
        body: rows,       // Table rows data
        theme: 'striped', // Optional: Add striped table style
        margin: { top: 20 },
        headStyles: { fillColor: [22, 160, 133] }, // Optional: Custom header color
    });
    
    // Save the PDF file
    doc.save('abbreviations_table.pdf');
});
</script>
<script>
$(document).ready(function() {
    $(".deleteBtn").click(function() {
        var id = $(this).data("id"); // Get ID from button

        if (confirm("Are you sure you want to delete this record?")) {
            $.ajax({
                url: "delete.php", 
                type: "POST",
                data: { id: id },
                success: function(response) {
                    if (response.trim() == "success") {
                        alert("Record deleted successfully!");
                        location.reload(); // Reload page to update table
                    } else {
                        alert("Error deleting record!");
                    }
                }
            });
        }
    });
});
</script>

</body>
</html>




