<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only admin can change roles
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

// Set user role to regular client
$result = $userController->setUserRole($userId, 'client');

if ($result['status']) {
    echo json_encode(['success' => true, 'message' => 'User role updated to regular client successfully']);
} else {
    echo json_encode(['success' => false, 'message' => $result['message']]);
}