<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0, '/', '', true, true);
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Hotelia Smart</title>
    <link rel="shortcut icon" type="image/icon" href="assets/HS.png"/>
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
    <section class="top-area">
        <div class="header-area">
            <nav class="navbar navbar-default bootsnav navbar-sticky navbar-scrollspy" data-minus-value-desktop="70" data-minus-value-mobile="55" data-speed="1000">
                <div class="container">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-menu">
                            <i class="fa fa-bars"></i>
                        </button>
                        <a class="navbar-brand" href="home.php">Hotelia<span>Smart</span></a>
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
                            <img src="assets/HS.png" alt="Hotelia Smart Logo" style="width:60px; height:60px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        </div>
                        <h2 style="text-align:center; font-weight:700; color:#2d3e50; margin-bottom:18px; letter-spacing:1px;">Create Your Account</h2>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="error-message" style="background:#ffeaea; color:#d93025; border-radius:6px; padding:10px 16px; margin-bottom:10px; text-align:center; font-weight:500; letter-spacing:0.5px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="signup.php" style="margin-top:24px;">
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">First Name:</label>
                                <input type="text" name="first_name" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Last Name:</label>
                                <input type="text" name="last_name" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Email:</label>
                                <input type="text" name="email" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <div class="form-group" style="margin-bottom:18px;">
                                <label style="font-weight:600; color:#2d3e50;">Password:</label>
                                <input type="password" name="password" style="width:100%; padding:10px 12px; border-radius:6px; border:1px solid #e0e7ef; background:#f7fafd; font-size:16px; margin-top:6px;">
                            </div>
                            <button type="submit" class="welcome-hero-btn" style="width:100%; background:#2d3e50; color:#fff; font-weight:700; border-radius:6px; padding:12px 0; font-size:18px; letter-spacing:1px; margin-bottom:10px; box-shadow:0 2px 8px rgba(45,62,80,0.08);">Register</button>
                        </form>
                        <p style="text-align:center; margin-top:10px;">Already have an account? <a href="login.php" style="color:#2d3e50; font-weight:600;">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootsnav.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="../../../js/user.js"></script>
</body>
</html>