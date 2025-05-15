<?php
include('connection.php');

// Error variable initialization
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Validate password strength (minimum 8 characters)
            if (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user into the database
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt->execute()) {
                    header('Location: login.php');
                    exit();
                } else {
                    $error = "Registration failed! Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <style>
           body {
            background-image: url('13.jpg'); /* Replace with your image URL */
            background-size: cover;                 /* Ensure the background covers the full page */
            background-position: center;            /* Center the background image */
            background-attachment: fixed;           /* Keep the background fixed while scrolling */
            height: 100vh;                          /* Full viewport height */
            margin: 0;
            display: flex;
            justify-content: flex-start;            /* Align the form to the left side */
            align-items: center;
            padding-left: 50px;                     /* Space from the left side */
        }

        /* Form container styling */
        .form-container {
            max-width: 400px;
            margin: 150px;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent white for readability */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        .error {
            color: #FF0000;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        .links {
            text-align: center;
            margin-top: 20px;
        }

        .links a {
            color: #007BFF;
            text-decoration: none;
        }

        /* Add space between Show Password and Sign Up button */
        .show-password-container {
            margin-bottom: 20px; /* Add some space below the checkbox */
        }

        .btn-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <form method="POST">
            <div class="input-group">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
            </div>

            <!-- Space between password and checkbox -->
            <div class="show-password-container">
                <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
            </div>

            <!-- Container for the Sign Up button to add margin -->
            <div class="btn-container">
                <button type="submit" class="btn-submit">Sign Up</button>
            </div>

            <?php if (!empty($error)) { echo "<p class='error'>$error</p>"; } ?>
        </form>

        <div class="links">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

    <!-- JavaScript to toggle password visibility -->
    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            var passwordField = document.getElementById('password');
            if (this.checked) {
                passwordField.type = 'text';  // Show password
            } else {
                passwordField.type = 'password';  // Hide password
            }
        });
    </script>
</body>
</html>
