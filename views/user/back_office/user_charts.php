<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front_office/login.php');
    exit();
}

require_once '../../../controllers/UserController.php';
$userController = new UserController();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Statistics - Admin Dashboard</title>
-    <link rel="stylesheet" href="stylesback.css">
-    <link rel="stylesheet" href="social_style.css">
+    <link rel="stylesheet" href="template/assets/css/bootstrap.min.css">
+    <link rel="stylesheet" href="template/assets/css/plugins.min.css">
+    <link rel="stylesheet" href="template/assets/css/kaiadmin.min.css">
+    <link rel="stylesheet" href="template/assets/css/demo.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'dashboard_side_bar.php'; ?>
    <?php include 'header_bar.php'; ?>

    <div class="main-content">
        <div class="stats-container">
            <div class="stats-row">
                <div class="stats-card">
                    <h3>User Status Distribution</h3>
                    <div class="chart-container">
                        <canvas id="userStatusChart"></canvas>
                    </div>
                </div>
                <div class="stats-card">
                    <h3>User Role Distribution</h3>
                    <div class="chart-container">
                        <canvas id="userRoleChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="stats-row">
                <div class="stats-card full-width">
                    <h3>User Growth Over Time</h3>
                    <div class="chart-container">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .stats-container {
        padding: 20px;
    }

    .stats-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .stats-card {
        flex: 1;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .stats-card.full-width {
        width: 100%;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }

    .stats-card h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        text-align: center;
    }

    @media (max-width: 768px) {
        .stats-row {
            flex-direction: column;
        }

        .stats-card {
            width: 100%;
        }

        .chart-container {
            height: 250px;
        }
    }
    </style>

    <script src="user_charts.js"></script>
</body>
</html>