<?php
session_start();  // Start the session to access session variables

// Include the database connection file
include("connection.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the user's email from the session
$userEmail = $_SESSION['email'];

// Define records per page
$recordsPerPage = isset($_GET['recordsPerPage']) ? (int)$_GET['recordsPerPage'] : 10;

// Get the current page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Fetch user data from the database
$query = "SELECT * FROM users LIMIT $offset, $recordsPerPage"; // Pagination query
$result = mysqli_query($conn, $query);

// Get total number of users for pagination
$totalQuery = "SELECT COUNT(*) AS total FROM users";
$totalResult = mysqli_query($conn, $totalQuery);
$totalRow = mysqli_fetch_assoc($totalResult);
$totalUsers = $totalRow['total'];
$totalPages = ceil($totalUsers / $recordsPerPage);
?>

<?php include("header1.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Table</title>
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
            margin-top: auto;
            text-align: center;
            background-color: #343a40;
            color: white;
            padding: 7px;
            width: 100%;
        }

        body {
            background-image: url('11.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            margin: 0;
        }

        .main-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .table-striped {
            width: 100%;
            height: auto;
        }

        .container {
            margin-top: 100px;
        }

        .exportbutton {
            margin-left: 5px;
        }
    </style>
</head>
<body>
<div class="main-container">
    <div class="container mt-3">
        <h2 class="mb-2">User Table</h2>

        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#addUserModal">Add New User</button>

        <!-- Row for search and export buttons -->
        <div class="row mb-3">
            <!-- Left column for Search and Pagination -->
            <div class="col-6">
                <!-- Search Input (small size) -->
                <input type="text" id="searchInput" class="form-control form-control mb-3" placeholder="Search in Table...">
            </div>

            <!-- Right column for Export buttons -->
            <div class="col-6">
                <!-- Export Buttons -->
                <button id="exportPdf" class="btn btn-danger mb-3">Export to PDF</button><br />
            </div>

            <!-- Records per page dropdown (small size) -->
            <select id="recordsPerPage" class="form-select form-select-sm mb-3" aria-label="Records per page" style="margin-left: 15px">
                <option value="10" <?= $recordsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                <option value="20" <?= $recordsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                <option value="30" <?= $recordsPerPage == 30 ? 'selected' : ''; ?>>30</option>
            </select>
        </div>

        <table id="tableData" class="table table-striped table-bordered" style="background-color:white">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['role'] . "</td>";
                        echo "<td>
                                <button class='btn btn-primary btn-sm editBtn' data-bs-toggle='modal' data-bs-target='#editModal' 
                                        data-id='".$row['id']."' data-name='".$row['username']."' data-email='".$row['email']."'>Edit</button> 
                                 <button class='btn btn-danger btn-sm deleteBtn' data-bs-toggle='modal' data-bs-target='#deleteModal' 
                                 data-id='".$row['id']."'>Delete</button>
                              </td>";
                       
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No users found</td></tr>";
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

<!-- Modal for adding a new user -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true" style="margin-top: 100px">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addUserForm" method="POST" action="add_user.php">
          <div class="mb-3">
            <label for="newUserName" class="form-label">Name</label>
            <input type="text" class="form-control" id="newUserName" name="username" required>
          </div>

          <div class="mb-3">
            <label for="newUserEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="newUserEmail" name="email" required>
          </div>

          <div class="mb-3">
            <label for="newUserPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="newUserPassword" name="password" required>
          </div>

          <button type="submit" class="btn btn-primary">Add User</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for editing user -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true" style="margin-top: 100px">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editForm" method="POST" action="update_user.php">
          <input type="hidden" id="userId" name="user_id">
          <div class="mb-3">
            <label for="userName" class="form-label">Name</label>
            <input type="text" class="form-control" id="userName" name="username" required>
          </div>
          <div class="mb-3">
            <label for="userEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="userEmail" name="email" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button type="submit" class="btn btn-danger">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for confirming delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" style="margin-top: 100px">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this user?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <a href="#" id="confirmDeleteButton" class="btn btn-danger">Delete</a>
      </div>
    </div>
  </div>
</div>

<?php include('footer1.php'); ?>

<script>
    // Populate modal with user data
    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var userId = button.data('id');
        var userName = button.data('name');
        var userEmail = button.data('email');
        
        // Set values in the modal
        var modal = $(this);
        modal.find('#userId').val(userId);
        modal.find('#userName').val(userName);
        modal.find('#userEmail').val(userEmail);
    });
</script>

<script>
   // JavaScript to handle the deletion modal
$('#deleteModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var userId = button.data('id'); // Extract user ID from the button's data-id attribute
    
    // Set the href for the delete confirmation button in the modal
    var modal = $(this);
    modal.find('#confirmDeleteButton').attr('href', 'delete_user.php?id=' + userId);
});
</script>

<script>
// Live search functionality
$(document).ready(function() {
    $('#searchInput').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#tableData tbody tr').each(function() {
            var row = $(this);
            var userName = row.find('td:eq(1)').text().toLowerCase(); // Name column
            var userEmail = row.find('td:eq(2)').text().toLowerCase(); // Email column
            if (userName.indexOf(searchTerm) > -1 || userEmail.indexOf(searchTerm) > -1) {
                row.show();
            } else {
                row.hide();
            }
        });
    });
});
</script>
<script>
    // Export to PDF functionality
    $(document).ready(function () {
        $('#exportPdf').on('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const tableData = [];

            // Collect table rows excluding the "Actions" column (index 3)
            $('#tableData tbody tr:visible').each(function () {
                const row = $(this);
                const rowData = [];
                row.find('td').each(function (index) {
                    if (index !== 3) { // Skip Actions column
                        rowData.push($(this).text().trim());
                    }
                });
                tableData.push(rowData);
            });

            // Generate table using autoTable
            doc.autoTable({
                head: [['User ID', 'Name', 'Email', 'Role']], // Column titles
                body: tableData,
                startY: 20,
                theme: 'grid',
                headStyles: { fillColor: [41, 128, 185] }, // Optional: blue header
            });

            doc.save('user_table.pdf');
        });
    });
</script>

</body>
</html>
