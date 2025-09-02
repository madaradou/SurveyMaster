<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit();
}
require_once '../../../controllers/SurveyController.php';
$controller = new SurveyController();
$clientId = (int)$_SESSION['user_id'];

$success = '';
$error = '';
$action = $_POST['action'] ?? '';
$surveyId = (int)($_POST['survey_id'] ?? ($_GET['survey_id'] ?? 0));

try {
    if ($action === 'submit_answers' && $surveyId) {
        $answers = $_POST['answers'] ?? [];
        if ($controller->saveResponse($surveyId, $clientId, $answers)) {
            $success = 'Survey submitted successfully';
        } else {
            $error = 'You are not assigned to this survey or invalid answers.';
        }
    }
} catch (Exception $ex) {
    $error = $ex->getMessage();
}

$assigned = $controller->listAssignedForClient($clientId);
$survey = $surveyId ? $controller->get($surveyId) : null;
$questions = $surveyId ? $controller->getQuestionsWithChoices($surveyId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Surveys | SurveyMaster</title>
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
        .surveys-card { background:#fff; border-radius:18px; box-shadow:0 8px 32px 0 rgba(31,38,135,0.15); padding: 32px; }
        .survey-list .list-group-item a { font-weight:600; }
        .question { padding: 12px; border: 1px solid #e0e7ef; border-radius: 8px; margin-bottom: 12px; background: #fff; }
        .question small { color:#888; }
        .selector { display:flex; gap:12px; align-items:center; margin-bottom:16px; }
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
            <div class="row justify-content-center" style="display:flex; align-items:flex-start; min-height: 80vh;">
                <div class="col-md-10" style="margin:auto;">
                    <div class="surveys-card">
                        <div style="text-align:center; margin-bottom:16px;">
                            <img src="assets/HS.png" alt="SurveyMaster Logo" style="width:56px; height:56px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        </div>
                        <h2 style="text-align:center; font-weight:700; color:#2d3e50; margin-bottom:18px; letter-spacing:1px;">My Surveys</h2>

                        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

                        <div class="row">
                            <div class="col-md-4">
                                <h4 style="font-weight:600; color:#2d3e50;">Assigned</h4>
                                <div class="selector">
                                    <select id="surveySelect" class="form-control">
                                        <option value="">Select a survey</option>
                                        <?php foreach ($assigned as $s): ?>
                                            <option value="<?php echo $s['survey_id']; ?>" <?php echo ($surveyId === (int)$s['survey_id'])?'selected':''; ?>>
                                                <?php echo htmlspecialchars($s['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary" id="openSurveyBtn">Open</button>
                                </div>
                                <ul class="list-group survey-list">
                                    <?php if (empty($assigned)): ?>
                                        <li class="list-group-item">No surveys assigned.</li>
                                    <?php else: ?>
                                        <?php foreach ($assigned as $s): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <a href="response.php?survey_id=<?php echo $s['survey_id']; ?>"><?php echo htmlspecialchars($s['title']); ?></a>
                                                <span class="badge badge-light">#<?php echo $s['survey_id']; ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-8">
                                <h4 style="font-weight:600; color:#2d3e50;">Survey</h4>
                                <?php if ($survey): ?>
                                    <div class="card" style="border:1px solid #e0e7ef; border-radius:12px;">
                                        <div class="card-body">
                                            <h4 style="margin-top:0;">Open in dedicated page</h4>
                                            <a class="btn btn-outline-primary" href="response.php?survey_id=<?php echo (int)$surveyId; ?>">Respond Now</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p>Select a survey from the left to start answering.</p>
                                <?php endif; ?>
                            </div>
                        </div>
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
        (function(){
            var sel = document.getElementById('surveySelect');
            var btn = document.getElementById('openSurveyBtn');
            if (btn && sel) {
                btn.addEventListener('click', function(){
                    var v = sel.value;
                    if (v) window.location.href = 'surveys.php?survey_id=' + encodeURIComponent(v);
                });
            }
        })();
    </script>
</body>
</html>

