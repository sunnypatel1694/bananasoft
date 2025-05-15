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

include("header1.php");
include("connection1.php");

// Default records per page
$recordsPerPage = isset($_GET['recordsPerPage']) ? $_GET['recordsPerPage'] : 10;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch categories
$queryCategory = "SELECT DISTINCT category FROM abbreviations";
$resultCategory = mysqli_query($conn, $queryCategory);
$categories = [];
if ($resultCategory) {
    while ($row = mysqli_fetch_assoc($resultCategory)) {
        $categories[] = $row['category'];
    }
}

// Calculate total records and pages
$queryTotal = "SELECT COUNT(DISTINCT category) AS total FROM abbreviations WHERE category LIKE '%$searchTerm%'";

$resultTotal = mysqli_query($conn, $queryTotal);
$totalRecords = mysqli_fetch_assoc($resultTotal)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
$startFrom = ($currentPage - 1) * $recordsPerPage;

// Fetch records
$query = "SELECT MIN(id) as id, category FROM abbreviations WHERE category LIKE '%$searchTerm%' GROUP BY category LIMIT $startFrom, $recordsPerPage";

$result = mysqli_query($conn, $query);
?>
<?php
if (isset($_GET['exists']) && $_GET['exists'] == 1) {
    echo "<script>alert('Category already exists!');</script>";
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo "<script>alert('Category added successfully!');</script>";
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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

        .table-striped{
            width: 600px;
            height: 100px;
        }

        .exportbutton{
            margin-left: 5px;
        }

        .container{
            margin-top: 150px;
        }
        .editmodal{
            padding: 70px;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="container mt-3">
        <h2 class="mb-2">Category Table</h2>
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#exampleModalCategory" data-bs-whatever="@mdo">Add Category</button>

        <!-- Modal for adding a new category -->
        <div class="modal fade" id="exampleModalCategory" tabindex="-1" aria-labelledby="exampleModalCategory" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content container">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCategory">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="add_category.php" id="addForm">
                            <div class="mb-3">
                                <label for="category" class="form-label"><b>Category</b></label>
                                <input type="text" class="form-control uppercase-input" id="category" name="category" value="" pattern="[a-zA-Z\s]+" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
<!-- Edit Category Modal -->
<div class="modal fade editmodal" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="update_category.php" method="POST">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Category</label>
                        <input type="text" class="form-control uppercase-input" id="editCategory" name="category" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
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
        <!-- Search and Pagination Controls -->
        <div class="row mb-3">
            <div class="col-6">
                <!-- Search Input -->
                <form method="GET" action="">
                    <input type="text" id="searchInput" name="search" class="form-control mb-3" placeholder="Search in Table...">

                    <!-- Records per page dropdown -->
                    <select id="recordsPerPage" name="recordsPerPage" class="form-select form-select-sm mb-3" onchange="this.form.submit()">
                        <option value="10" <?= $recordsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?= $recordsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="30" <?= $recordsPerPage == 30 ? 'selected' : ''; ?>>30</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Table -->
        <table id="tableData" class="table table-striped table-bordered" style="background-color: white" align="center">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= strtoupper($row['category']) ?></td>
                <td>
                     <button class="btn btn-primary btn-sm editBtn" data-id="<?= $row['id'] ?>" data-category="<?= htmlspecialchars($row['category'], ENT_QUOTES) ?>">Edit</button>
                     
                    <button class="btn btn-danger btn-sm deleteBtn" data-id="<?= $row['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php } ?>
        </tbody>
        </table>

        <!-- Pagination -->
        <div class="row mb-3">
            <div class="col-12">
                <nav id="paginationNav" aria-label="Page navigation">
                    <ul class="pagination pagination-sm">
                        <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $currentPage - 1; ?>&recordsPerPage=<?= $recordsPerPage; ?>&search=<?= urlencode($searchTerm); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>&recordsPerPage=<?= $recordsPerPage; ?>&search=<?= urlencode($searchTerm); ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $currentPage + 1; ?>&recordsPerPage=<?= $recordsPerPage; ?>&search=<?= urlencode($searchTerm); ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<?php include('footer1.php'); ?>
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
    document.addEventListener("DOMContentLoaded", function () {
    // Select all Edit buttons
    document.querySelectorAll(".editBtn").forEach(button => {
        button.addEventListener("click", function () {
            let id = this.getAttribute("data-id");
            let category = this.getAttribute("data-category");

            // Populate modal fields
            document.getElementById("editId").value = id;
            document.getElementById("editCategory").value = category;

            // Open the modal
            var editModal = new bootstrap.Modal(document.getElementById("editModal"));
            editModal.show();
        });
    });
});
</script>
<script>
    // Delete Button click handler
document.querySelectorAll('.deleteBtn').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        // Store the ID for the delete action
        document.getElementById('deleteConfirmBtn').setAttribute('data-id', id);
    });
});

// Delete Confirmation
document.getElementById('deleteConfirmBtn').addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    // Send a request to delete the record from the database
    fetch(`delete.php?id=${id}`, {
        method: 'POST',
    }).then(response => {
        if (response.ok) {
            // Reload the page or update the table dynamically
            location.reload();
        }
    });
});
</script>
<script>
    $(document).ready(function() {
        // Open edit modal
        $(document).on("click", ".editBtn", function() {
            let id = $(this).data("id");
            let category = $(this).data("category");
            $("#categoryId").val(id);
            $("#categoryName").val(category);
            $("#categoryModal").modal("show");
        });

        // Handle form submission for add/update
        $("#categoryForm").submit(function(event) {
            event.preventDefault();
            let formData = $(this).serialize();
            $.post("manage_category.php", formData, function(response) {
                location.reload();
            });
        });

        // Delete category
        $(document).on("click", ".deleteBtn", function() {
            let id = $(this).data("id");
            if (confirm("Are you sure you want to delete this category?")) {
                $.post("delete_category.php", { id: id }, function(response) {
                    location.reload();
                });
            }
        });
    });
</script>
</body>
</html>
