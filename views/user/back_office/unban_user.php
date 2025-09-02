<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

require_once '../../../controllers/UserController.php';
$userController = new UserController();

// Unban the user
$result = $userController->unbanUser($userId);

if ($result['status']) {
    echo json_encode(['success' => true, 'message' => 'User unbanned successfully']);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}