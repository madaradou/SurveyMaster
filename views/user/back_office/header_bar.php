<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in and is agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front_office/login.php');
    exit();
}
?>
<div class="navbar navbar-header navbar-expand-lg" data-background-color="dark">
    <div class="container-fluid">
        <div class="navbar-minimize">
            <button class="btn btn-minimize btn-rounded btn-icon">
                <i class="gg-menu-left"></i>
            </button>
        </div>
        <a class="navbar-brand" href="users_management.php">
            <img src="template/assets/img/kaiadmin/logo_light.svg" alt="navbar brand" height="22" />
        </a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="../front_office/profile.php">My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../front_office/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<link rel="stylesheet" href="template/assets/css/kaiadmin.min.css">
<link rel="stylesheet" href="template/assets/css/plugins.min.css">
<link rel="stylesheet" href="template/assets/css/bootstrap.min.css">
<link rel="stylesheet" href="template/assets/css/demo.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">