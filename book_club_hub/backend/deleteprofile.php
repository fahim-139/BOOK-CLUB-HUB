<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../frontend/login.html");
    exit();
}

// Database connection variables
$host = 'localhost';
$dbname = 'your_database_name';
$username = 'your_db_username';
$password = 'your_db_password';

$userId = $_SESSION['user_id'] ?? '';
$error = '';
$success = '';

// Handle account deletion confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        try {
            // Create database connection
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Delete user from database
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :userId");
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            // Destroy session and redirect
            session_unset();
            session_destroy();
            
            header("Location: ../frontend/goodbye.html"); // Create a goodbye page
            exit();
        } catch (Exception $e) {
            $error = "Error deleting account: " . $e->getMessage();
        }
    } else {
        // User canceled deletion
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delete Account | Book Club Hub</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../frontend/css/profile.css"> <!-- Reuse existing styles -->
  <style>
    .delete-container {
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }
    .warning-message {
      background-color: #fff3f3;
      border-left: 4px solid #ff5252;
      padding: 1rem;
      margin: 1.5rem 0;
      text-align: left;
    }
    .delete-actions {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-top: 2rem;
    }
    .btn-delete {
      background-color: #ff5252;
      color: white;
    }
    .btn-delete:hover {
      background-color: #e04040;
    }
    .btn-cancel {
      background-color: #6c757d;
      color: white;
    }
    .btn-cancel:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>
  <div class="profile-container">
    <div class="delete-container">
      <h1>Delete Your Account</h1>
      
      <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <div class="warning-message">
        <h3>⚠️ Warning: This action cannot be undone</h3>
        <p>Deleting your account will:</p>
        <ul>
          <li>Permanently remove all your personal information</li>
          <li>Delete your reading history and book club memberships</li>
          <li>Remove any posts or comments you've made</li>
        </ul>
      </div>
      
      <p>Are you sure you want to delete your account?</p>
      
      <form action="deleteprofile.php" method="post" class="delete-form">
        <div class="delete-actions">
          <button type="submit" name="confirm_delete" class="btn btn-delete">Yes, Delete My Account</button>
          <button type="submit" name="cancel" class="btn btn-cancel">No, Keep My Account</button>
        </div>
      </form>
    </div>
  </div>
  <footer class="main-footer">
    <div class="container">
        <div class="footer-links">
            <a href="#">Terms & Conditions</a>
            <a href="#">Privacy Policy</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
        </div>
        <p class="copyright">&copy; 2025 Book Club Hub. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>