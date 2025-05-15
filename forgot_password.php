<?php
include('connection.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Direct link to reset page with email (no token)
            $reset_link = "http://localhost/bananasoft/reset_password.php?email=" . urlencode($email);

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $_ENV['SMTP_HOST'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['SMTP_USERNAME'];
                $mail->Password   = $_ENV['SMTP_PASSWORD'];
                $mail->SMTPSecure = $_ENV['SMTP_ENCRYPTION'];
                $mail->Port       = $_ENV['SMTP_PORT'];

                $mail->setFrom($_ENV['SMTP_USERNAME'], 'BananaSoft');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password';
                $mail->Body    = "Click the link below to reset your password:<br><a href='$reset_link'>$reset_link</a>";

                $mail->send();
                $success = "Reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
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
            color: #00796b;
            margin-bottom: 20px;
        }

        input[type="email"] {
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
            background-color: #00796b;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color:rgb(0, 0, 0);
        }

        .error {
            color: #d32f2f;
            margin-bottom: 10px;
        }

        .success {
            color: #388e3c;
            margin-bottom: 10px;
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
        <h2>Forgot Password</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <form method="POST">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>

</html>
