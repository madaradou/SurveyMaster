<?php
require_once '../../../config.php';
require_once '../../../controllers/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userController = new UserController();
    
    // Get and sanitize input
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Update profile
    $result = $userController->updateProfile($_SESSION['user_id'], $firstName, $lastName, $email);
    
    if ($result['status']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
}

// Redirect back to profile page
header('Location: profile.php');
exit();