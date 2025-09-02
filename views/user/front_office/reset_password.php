<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/', '', true, true);
    session_start();
}

require_once __DIR__ . '/../../../controllers/UserController.php';

$error = '';
$success = '';
$validToken = false;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $error = "No reset token provided. Please use the link from your email.";
} else {
    try {
        $userController = new UserController();
        $validToken = $userController->validateResetToken($token);
        
        if (!$validToken) {
            $error = "Invalid or expired reset token. Please request a new password reset.";
        }
    } catch (Exception $e) {
        error_log("Password reset validation error: " . $e->getMessage());
        $error = "An error occurred while validating your reset token. Please try again.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    if (empty($password) || empty($confirmPassword)) {
        $error = "Both password fields are required.";
    } else if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    } else {
        try {
            $userController = new UserController();
            
            if ($userController->resetPassword($token, $password)) {
                $success = "Password has been reset successfully. You can now login with your new password.";
                $userController->invalidateResetToken($token);
                // Clear any sensitive data from session
                session_regenerate_id(true);
                
                // Redirect to login page with success message
                if (!headers_sent()) {
                    header('Location: login.php?success=' . urlencode($success));
                    exit();
                }
            } else {
                $error = "Failed to reset password. Please try again.";
                error_log("Password reset failed for token: " . $token);
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $error = "An error occurred while resetting your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Hotelia Smart</title>
    <link rel="shortcut icon" type="image/icon" href="assets/HS.png"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/linearicons.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/flaticon.css">
    <link rel="stylesheet" href="assets/css/slick.css">
    <link rel="stylesheet" href="assets/css/slick-theme.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootsnav.css">
    <link rel="stylesheet" href="assets/css/stylefront3.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <style>
        body { background: linear-gradient(135deg, #e0e7ef 0%, #f5f7fa 100%); min-height: 100vh; }
        .form-section { display: flex; align-items: center; min-height: 100vh; }
        .form-container { background: #fff; border-radius: 18px; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15); padding: 40px 32px 32px 32px; margin: 0 auto; max-width: 400px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 7px; color: #2d3e50; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; }
        .btn { background-color: #4CAF50; color: white; padding: 12px 0; border: none; border-radius: 6px; cursor: pointer; width: 100%; font-size: 17px; font-weight: 600; margin-top: 10px; transition: background 0.2s; }
        .btn:hover { background: #388e3c; }
        .error { background:#ffeaea; color:#d93025; border-radius:6px; padding:10px 16px; margin-bottom:10px; text-align:center; font-weight:500; letter-spacing:0.5px; }
        .success { background:#e6f4ea; color:#188038; border-radius:6px; padding:10px 16px; margin-bottom:10px; text-align:center; font-weight:500; letter-spacing:0.5px; }
        .back-link { display: block; text-align: center; margin-top: 18px; color: #4CAF50; font-weight: 500; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .logo-box { text-align:center; margin-bottom:24px; }
        .logo-box img { width:60px; height:60px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        h2 { text-align:center; font-weight:700; color:#2d3e50; margin-bottom:18px; letter-spacing:1px; }
    </style>
</head>
<body>
    <header id="header-top" class="header-top">
        <ul>
            <li>
                <div class="header-top-left">
                    <ul>
                        <li class="select-opt">
                            <a href="#"><span class="lnr lnr-magnifier"></span></a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="head-responsive-right pull-right">
                <div class="header-top-right">
                    <ul>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="header-top-contact"><a href="profile.php">Profile</a></li>
                        <li class="header-top-contact"><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                        <li class="header-top-contact"><a href="login.php">Sign In</a></li>
                        <li class="header-top-contact"><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
        </ul>
    </header>
    <section class="form-section">
        <div class="container">
            <div class="row justify-content-center" style="display: flex; align-items: center; min-height: 100vh;">
                <div class="col-md-6" style="margin: auto;">
                    <div class="form-container">
                        <div class="logo-box">
                            <img src="assets/HS.png" alt="Hotelia Smart Logo">
                        </div>
                        <h2>Reset Password</h2>
                        <?php if ($error): ?>
                            <div class="error"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="success"><?php echo htmlspecialchars($success); ?></div>
                        <?php endif; ?>
                        <?php if ($validToken && !$success): ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>New Password:</label>
                                <input type="password" name="password" required minlength="8">
                            </div>
                            <div class="form-group">
                                <label>Confirm Password:</label>
                                <input type="password" name="confirm_password" required minlength="8">
                            </div>
                            <button type="submit" class="btn">Reset Password</button>
                        </form>
                        <?php endif; ?>
                        <a href="login.php" class="back-link">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>