
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <b><a class="navbar-brand">Dashboard</a></b>
    
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <?php if (!isset($_SESSION['user_id'])): ?>
          <!-- Show 'User' link only if the user is not logged in -->
          <li class="nav-item">
            <a class="nav-link" href="user.php">User</a>
          </li>
        <?php endif; ?>

        <!-- Other links -->
        <li class="nav-item">
          <b><a class="nav-link" href="abbreviation.php">Abbreviations</a></b>
        </li>
        <li class="nav-item">
          <b><a class="nav-link" href="category.php">Categories</a></b>
        </li>
        
        <!-- Admin-specific link (only visible to admins) -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
          <li class="nav-item">
            <b><a class="nav-link" href="user.php">User</a></b>
          </li>
        <?php endif; ?>
      </ul>

      <!-- User's Welcome message and Logout button -->
      <?php if (isset($_SESSION['user_id'])): ?>
        <form action="user pannel/search_engine.php" style="margin-left: 5px;" class="d-flex align-items-center">
          <i class="fa-solid fa-user me-2"></i>  <!-- Add user icon here -->
          <b>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></b>
          <button class="btn btn-danger ms-3" type="submit">Logout</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</nav>