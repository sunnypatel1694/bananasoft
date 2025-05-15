<?php
include('connection.php');
include('functions.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate password length (between 8 and 16 characters)
    if (strlen($password) < 8 || strlen($password) > 16) {
        $error = "Password must be between 8 and 16 characters.";
    } else {
        // Prepare the SQL query to fetch the user by email
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // If user exists and the password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Start the session if not already started
            session_start();

            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];  // Store email in session
            $_SESSION['role'] = $user['role'];  // Store role in session

            // Set a session variable for the login message (this will be checked after redirect)
            $_SESSION['login_message'] = $user['role'];  // Store the role for showing the message

            // Redirect to login.php to show the message
            header('Location: login.php');  // Redirect to the same page to show the message after login
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Set the background image */
        body {
            background-image: url('13.jpg'); /* Replace with your image URL */
            background-size: cover;                 /* Ensure the background covers the full page */
            background-position: center;            /* Center the background image */
            background-attachment: fixed;           /* Keep the background fixed while scrolling */
            height: 100vh;                          /* Full viewport height */
            margin: 0;
            display: flex;
            justify-content: flex-start;           /* Align form to the left side */
            align-items: center;
        }

        /* Form container styling */
        .form-container {
            max-width: 400px;
            margin: 200px;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white for readability */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 15px;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }

        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #45a049;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #007BFF;
            text-decoration: none;
        }

        /* Show password checkbox styling */
        .show-password-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }

        .show-password-container input {
            margin-right: 5px;
        }

        .btn-container {
            margin-top: 20px;
        }

        /* Add styling for the error message */
        .error {
            color: #FF0000;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form method="POST">
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="show-password-container">
                <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
            </div>

            <!-- Container for the submit button to add margin -->
            <div class="btn-container">
                <button type="submit" class="btn-submit">Login</button>
            </div>

            <!-- Display error message if exists -->
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
        </form>

        <div class="links">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p><br />
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>

    <script>
        // JavaScript to toggle password visibility
        document.getElementById('showPassword').addEventListener('change', function() {
            var passwordField = document.getElementById('password');
            if (this.checked) {
                passwordField.type = 'text'; // Show password
            } else {
                passwordField.type = 'password'; // Hide password
            }
        });
    </script>

    <?php
    // Check if a login message is set in the session
    if (isset($_SESSION['login_message'])) {
        $role = $_SESSION['login_message'];
        
        // Set a message based on user role
        if ($role == 'admin') {
            // Show an alert with a welcome message for the admin role
            echo "<script>
                    alert('Welcome Admin!');
                    window.location.href = 'abbreviation.php';  // Redirect to abbreviation.php after the alert is closed
                  </script>";
        } else {
            // Show an alert with a welcome message for the user role
            echo "<script>
                    alert('Welcome User!');
                    window.location.href = 'abbreviation.php';  // Redirect to abbreviation.php after the alert is closed
                  </script>";
        }
        
        // Unset the session message after displaying it
        unset($_SESSION['login_message']);
    }
    ?>
</body>
</html>
