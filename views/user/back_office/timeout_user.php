<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once '../../../controllers/UserController.php';
$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(['status' => false, 'message' => 'Unauthorized access (session check failed)']);
        exit();
    }
    if (!isset($input) || !is_array($input)) {
        echo json_encode(['status' => false, 'message' => 'No input data received or input is not valid JSON']);
        exit();
    }
    if (isset($input['action']) && $input['action'] === 'timeout') {
        if (!isset($input['user_id'])) {
            echo json_encode(['status' => false, 'message' => 'Missing user_id parameter']);
            exit();
        }
        if (!isset($input['duration'])) {
            echo json_encode(['status' => false, 'message' => 'Missing duration parameter']);
            exit();
        }
        $userId = intval($input['user_id']);
        $duration = intval($input['duration']);
        $result = $userController->timeoutUser($userId, $duration);
        if (!is_array($result)) {
            echo json_encode(['status' => false, 'message' => 'Controller did not return an array']);
            exit();
        }
        echo json_encode($result);
        exit();
    } elseif (isset($input['action']) && $input['action'] === 'reactivate') {
        if (!isset($input['user_id'])) {
            echo json_encode(['status' => false, 'message' => 'Missing user_id parameter for reactivation']);
            exit();
        }
        $userId = intval($input['user_id']);
        $result = $userController->reactivateUser($userId);
        if (!is_array($result)) {
            echo json_encode(['status' => false, 'message' => 'Controller did not return an array for reactivation']);
            exit();
        }
        echo json_encode($result);
        exit();
    } elseif (isset($input['action']) && $input['action'] === 'stop_timeout') {
        if (!isset($input['user_id'])) {
            echo json_encode(['status' => false, 'message' => 'Missing user_id parameter for stop_timeout']);
            exit();
        }
        $userId = intval($input['user_id']);
        // Set is_timed_out to 0 and clear timeout_until
        $sql = "UPDATE user SET is_timed_out = 0, timeout_until = NULL WHERE user_id = :user_id";
        require_once '../../../config/database.php';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $success = $stmt->execute();
        if ($success) {
            echo json_encode(['status' => true, 'message' => 'Timeout stopped successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to stop timeout']);
        }
        exit();
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid request: action missing or unknown']);
        exit();
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit();
}