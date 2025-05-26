<?php
session_start();
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
include '../db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        // Update reset token and expiry
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();
        $stmt->close();

        // Send reset email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'fmd4965@gmail.com';  
            $mail->Password = 'idmd rzuk curz upjm';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // use STARTTLS for port 587
            $mail->Port = 587;

            $mail->setFrom('fmd4965@gmail.com', 'BookClubHub');
            $mail->addAddress($email);

            $resetLink = "http://localhost/book_club_hub/backend/reset_password.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body    = "Hi,<br><br>Click <a href='$resetLink'>here</a> to reset your password.<br><br>If you did not request this, please ignore this email.";

            $mail->send();
            echo "Reset link sent. Check your inbox.";
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }
} else {
    echo "Invalid request method.";
}
?>