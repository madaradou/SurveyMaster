<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user = [
    'first_name' => $_SESSION['first_name'],
    'last_name' => $_SESSION['last_name'],
    'email' => $_SESSION['email'],
    'role' => $_SESSION['role'],
    'account_type' => isset($_SESSION['account_type']) ? $_SESSION['account_type'] : 'normal'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | SurveyMaster</title>
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
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="header-top-contact"><a href="../back_office/users_management.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li class="header-top-contact"><a href="profile.php">Profile</a></li>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'client'): ?>
                        <li class="header-top-contact"><a href="messages/messaging.php">Help</a></li>
                        <?php endif; ?>
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
    <section class="top-area">
        <div class="header-area">
            <nav class="navbar navbar-default bootsnav navbar-sticky navbar-scrollspy" data-minus-value-desktop="70" data-minus-value-mobile="55" data-speed="1000">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                            <i class="fa fa-bars"></i>
                        </button>
                        <a class="navbar-brand" href="home.php">Survey<span>Master</span></a>
                    </div>
                    <div class="collapse navbar-collapse menu-ui-design" id="navbar-menu">
                        <ul class="nav navbar-nav navbar-right" data-in="fadeInDown" data-out="fadeOutUp">
                        <li class="header-top-contact"><a href="home.php">home</a></li>
                            <li class="header-top-contact"><a href="store.php">store</a></li>
                            <li class="header-top-contact"><a href="explore.php">explore</a></li>
                            <li class="header-top-contact"><a href="reviews.php">review</a></li>
                            <li class="header-top-contact"><a href="blog.php">blog</a></li>
                            <li class="header-top-contact"><a href="forum.php">forum</a></li>
                            <li class="header-top-contact"><a href="Aboutus.php">about us</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
        <div class="clearfix"></div>
    </section>
    <section class="form-section" style="background: linear-gradient(135deg, #e0e7ef 0%, #f5f7fa 100%); min-height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="row justify-content-center" style="display: flex; align-items: center; min-height: 100vh;">
                <div class="col-md-6" style="margin: auto;">
                    <div class="form-container" style="background: #fff; border-radius: 18px; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.15); padding: 40px 32px 32px 32px; margin: 0 auto;">
                        <div style="text-align:center; margin-bottom:24px;">
                            <img src="assets/HS.png" alt="SurveyMaster Logo" style="width:60px; height:60px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        </div>
                        <h2 style="text-align:center; font-weight:700; color:#2d3e50; margin-bottom:18px; letter-spacing:1px;">Profile</h2>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="success-message" style="background:#e6f4ea; color:#188038; border-radius:6px; padding:10px 16px; margin-bottom:10px; text-align:center; font-weight:500; letter-spacing:0.5px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="error-message" style="background:#ffeaea; color:#d93025; border-radius:6px; padding:10px 16px; margin-bottom:10px; text-align:center; font-weight:500; letter-spacing:0.5px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="update.php" id="updateForm" style="margin-top:24px;">
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">First Name:</label>
                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Last Name:</label>
                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Email:</label>
                                <input type="text" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Role:</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['role']); ?>" disabled style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Account Type:</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['account_type']); ?>" disabled style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="profile-action-buttons" style="display:flex; flex-direction:row; justify-content:center; gap:20px; margin-top:24px;">
                                <button type="submit" class="welcome-hero-btn" style="background: url('assets/img/profile-update-icon.png') no-repeat left center, #2d3e50; background-size:24px 24px; background-position:16px center; padding-left:48px; width:220px; color:#fff; font-weight:700; border-radius:6px; padding:12px 0; font-size:18px; letter-spacing:1px; margin-bottom:10px; box-shadow:0 2px 8px rgba(45,62,80,0.08); display:flex; align-items:center; justify-content:center;">
                                    Update Profile
                                </button>
                                <a href="home.php" class="welcome-hero-btn" style="background: url('assets/img/home-icon.png') no-repeat left center, #003087; background-size:24px 24px; background-position:16px center; padding-left:48px; width:180px; color:#fff; font-weight:700; border-radius:6px; padding:12px 0; font-size:18px; letter-spacing:1px; margin-bottom:10px; box-shadow:0 2px 8px rgba(0,48,135,0.08); display:flex; align-items:center; justify-content:center;">
                                    Home
                                </a>
                                <a href="logout.php" class="welcome-hero-btn" style="background: url('assets/img/logout-icon.png') no-repeat left center, #f44336; background-size:24px 24px; background-position:16px center; padding-left:48px; width:180px; color:#fff; font-weight:700; border-radius:6px; padding:12px 0; font-size:18px; letter-spacing:1px; margin-bottom:10px; box-shadow:0 2px 8px rgba(244,67,54,0.08); display:flex; align-items:center; justify-content:center;">
                                    Logout
                                </a>
                            </div>
                        </form>
                        <div id="update-error" class="error-message" style="display:none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    (function() {
        var form = document.getElementById('updateForm');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            var firstName = form.elements['first_name'].value.trim();
            var lastName = form.elements['last_name'].value.trim();
            var email = form.elements['email'].value.trim();
            var errorMessages = [];
            if (firstName.length === 0) {
                errorMessages.push('First name is required.');
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(firstName)) {
                errorMessages.push('First name contains invalid characters.');
            } else if (firstName.length < 2) {
                errorMessages.push('First name must be at least 2 characters.');
            }
            if (lastName.length === 0) {
                errorMessages.push('Last name is required.');
            } else if (!/^[A-Za-zÀ-ÿ' -]+$/.test(lastName)) {
                errorMessages.push('Last name contains invalid characters.');
            } else if (lastName.length < 2) {
                errorMessages.push('Last name must be at least 2 characters.');
            }
            var emailPattern = /^\S+@\S+\.\S+$/;
            if (email.length === 0) {
                errorMessages.push('Email is required.');
            } else if (!emailPattern.test(email)) {
                errorMessages.push('Invalid email format.');
            }
            var errorDiv = document.getElementById('update-error');
            if (errorMessages.length > 0) {
                e.preventDefault();
                errorDiv.innerHTML = errorMessages.join('<br>');
                errorDiv.style.display = 'block';
            } else {
                errorDiv.style.display = 'none';
            }
        });
    })();
    </script>
</body>
</html>