<?php
session_start();
include '../db.php';

// Sanitize & collect POST data
$firstName = $_POST['first_name'];
$lastName = $_POST['last_name'];
$email = $_POST['email'];
$userId = $_POST['user_id'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Handle optional profile picture
$profilePic = null;
if (!empty($_FILES['profile_pic']['name'])) {
    $targetDir = "uploads/";
    $fileName = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFilePath)) {
        $profilePic = $targetFilePath;
    }
}

// Insert user directly as verified
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, user_id, password, profile_pic, verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
$stmt->bind_param("ssssss", $firstName, $lastName, $email, $userId, $password, $profilePic);

if ($stmt->execute()) {
    header("Location: ../frontend/register_success.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>