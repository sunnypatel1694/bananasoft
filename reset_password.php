<?php
include('connection.php');

$error = '';
$success = '';

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);

    // Optional: check if email exists in DB
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Invalid email!";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['password'];
        $confirm = $_POST['confirm_password'];

        if ($new_password !== $confirm) {
            $error = "Passwords do not match!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();
            $success = "Password updated! <a href='login.php'>Login</a>";
        }
    }
} else {
    $error = "No email provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg,rgb(105, 131, 135),rgb(0, 0,0));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h2 {
            color: #33691e;
            margin-bottom: 20px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 12px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #558b2f;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #33691e;
        }

        .error {
            color: #d32f2f;
            margin-bottom: 10px;
        }

        .success {
            color: #388e3c;
            margin-bottom: 10px;
        }

        a {
            color: #33691e;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            input, button {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>

        <?php if (!$success && isset($_GET['email'])) { ?>
        <form method="POST">
            <input type="password" name="password" required placeholder="New password">
            <input type="password" name="confirm_password" required placeholder="Confirm password">
            <button type="submit">Reset Password</button>
        </form>
        <?php } ?>
    </div>
</body>

</html>
