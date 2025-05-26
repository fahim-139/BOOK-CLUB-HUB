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

// Initialize variables
$errors = [];
$validatedData = [];
$userId = $_SESSION['user_id'] ?? '';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate each field
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Validate First Name (2-50 characters, letters and basic punctuation)
    if (empty($firstName)) {
        $errors['firstName'] = 'First name is required';
    } elseif (!preg_match('/^[a-zA-Z \'.-]{2,50}$/', $firstName)) {
        $errors['firstName'] = 'First name must be 2-50 characters with only letters, spaces, and basic punctuation';
    } else {
        $validatedData['firstName'] = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
    }

    // Validate Last Name (2-50 characters, letters and basic punctuation)
    if (empty($lastName)) {
        $errors['lastName'] = 'Last name is required';
    } elseif (!preg_match('/^[a-zA-Z \'.-]{2,50}$/', $lastName)) {
        $errors['lastName'] = 'Last name must be 2-50 characters with only letters, spaces, and basic punctuation';
    } else {
        $validatedData['lastName'] = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email already exists (excluding current user)
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :userId");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $errors['email'] = 'This email is already registered to another account';
            } else {
                $validatedData['email'] = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Error validating email: ' . $e->getMessage();
        }
    }

    // Validate Bio (optional, but limit length)
    if (!empty($bio)) {
        if (strlen($bio) > 500) {
            $errors['bio'] = 'Bio must be 500 characters or less';
        } else {
            $validatedData['bio'] = htmlspecialchars($bio, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $validatedData['bio'] = '';
    }

    // If no errors, proceed to update profile
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET 
                                  first_name = :firstName, 
                                  last_name = :lastName, 
                                  email = :email, 
                                  bio = :bio 
                                  WHERE user_id = :userId");
            
            $stmt->bindParam(':firstName', $validatedData['firstName']);
            $stmt->bindParam(':lastName', $validatedData['lastName']);
            $stmt->bindParam(':email', $validatedData['email']);
            $stmt->bindParam(':bio', $validatedData['bio']);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();

            // Update session variables
            $_SESSION['first_name'] = $validatedData['firstName'];
            $_SESSION['last_name'] = $validatedData['lastName'];
            $_SESSION['email'] = $validatedData['email'];
            $_SESSION['bio'] = $validatedData['bio'];

            // Redirect to profile with success message
            header("Location: profile.php?success=1");
            exit();
        } catch (PDOException $e) {
            $errors['database'] = 'Error updating profile: ' . $e->getMessage();
        }
    }

    // If we have errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
        $_SESSION['profile_old_data'] = [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'bio' => $bio
        ];
        header("Location: editprofile.php");
        exit();
    }
} else {
    // Not a POST request, redirect to edit page
    header("Location: editprofile.php");
    exit();
}