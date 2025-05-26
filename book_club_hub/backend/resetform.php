<?php
if (!isset($_GET['token'])) {
    die("Invalid access.");
}
$token = $_GET['token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Reset Password</title>
  <link rel="stylesheet" href="../frontend/css/forgotpass.css">
</head>
<body>
  <div class="container">
    <h2>Reset Password</h2>
    <form action="resetform.php" method="POST">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
      <input type="password" name="password" placeholder="Enter new password" required />
      <button type="submit">Reset Password</button>
    </form>
  </div>
</body>
</html>

<?php
require '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Get email associated with token
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($email);
    if ($stmt->fetch()) {
        $stmt->close();

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        if ($stmt->execute()) {
            echo "Password reset successfully.";
            // Delete token after use
            $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
        } else {
            echo "Error updating password.";
        }
    } else {
        echo "Invalid or expired token.";
    }
}
?>