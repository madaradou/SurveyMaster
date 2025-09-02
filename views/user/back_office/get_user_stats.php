<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../../../controllers/UserController.php';
$userController = new UserController();

// Get all users
$users = $userController->getAllUsers();

// Initialize counters
$activeUsers = 0;
$bannedUsers = 0;
$agentUsers = 0;
$clientUsers = 0;
$monthlyGrowth = array();

// Process user data
foreach ($users as $user) {
    // Count user status
    if ($user['banned']) {
        $bannedUsers++;
    } else {
        $activeUsers++;
    }

    // Count user roles
    if ($user['role'] === 'admin') {
        $agentUsers++;
    } else {
        $clientUsers++;
    }

    // Process registration date for growth chart
    $regDate = date('Y-m', strtotime($user['date']));
    if (!isset($monthlyGrowth[$regDate])) {
        $monthlyGrowth[$regDate] = 0;
    }
    $monthlyGrowth[$regDate]++;
}

// Sort monthly growth by date
ksort($monthlyGrowth);

// Prepare growth data for the chart
$growthData = array(
    'labels' => array_keys($monthlyGrowth),
    'values' => array_values($monthlyGrowth)
);

// Prepare response data
$response = array(
    'success' => true,
    'statusData' => array(
        'active' => $activeUsers,
        'banned' => $bannedUsers
    ),
    'roleData' => array(
        'clients' => $clientUsers,
        'agents' => $agentUsers
    ),
    'growthData' => $growthData
);

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);