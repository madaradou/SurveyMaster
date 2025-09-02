<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') { header('Location: login.php'); exit(); }
require_once '../../../controllers/SurveyController.php';
$controller = new SurveyController();
$clientId = (int)$_SESSION['user_id'];

$success = '';$error='';
$surveyId = (int)($_POST['survey_id'] ?? ($_GET['survey_id'] ?? 0));
$action = $_POST['action'] ?? '';
try {
    if ($action === 'submit_answers' && $surveyId) {
        $answers = $_POST['answers'] ?? [];
        if ($controller->saveResponse($surveyId, $clientId, $answers)) {
            $success = 'Thanks! Your response was submitted.';
        } else {
            $error = 'You are not assigned to this survey or some answers were invalid.';
        }
    }
} catch (Exception $e) { $error = $e->getMessage(); }

$survey = $surveyId ? $controller->get($surveyId) : null;
$questions = $surveyId ? $controller->getQuestionsWithChoices($surveyId) : [];
if (!$survey) { header('Location: surveys.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Respond | <?php echo htmlspecialchars($survey['title'] ?? 'Survey'); ?></title>
  <link rel="shortcut icon" type="image/icon" href="assets/HS.png"/>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/stylefront3.css">
  <style>
    .container-narrow { max-width: 820px; margin: 24px auto; }
    .survey-card { background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,.07); padding:24px; }
    .question { padding: 12px; border: 1px solid #e0e7ef; border-radius: 8px; margin-bottom: 12px; background: #fff; }
    .question small { color:#7b8794; }
  </style>
</head>
<body style="background:#f5f7fa;">
<header id="header-top" class="header-top">
  <ul>
    <li><div class="header-top-left"><ul><li class="select-opt"><a href="home.php">Home</a></li></ul></div></li>
    <li class="head-responsive-right pull-right">
      <div class="header-top-right"><ul>
        <li class="header-top-contact"><a href="surveys.php">My Surveys</a></li>
        <li class="header-top-contact"><a href="logout.php">Logout</a></li>
      </ul></div>
    </li>
  </ul>
</header>

<section class="form-section" style="background: linear-gradient(135deg, #e0e7ef 0%, #f5f7fa 100%); min-height: 100vh; display: flex; align-items: center;">
  <div class="container">
    <div class="row justify-content-center" style="display:flex; align-items:flex-start; min-height: 80vh;">
      <div class="col-md-10" style="margin:auto;">
        <div class="surveys-card">
          <div style="text-align:center; margin-bottom:16px;">
            <img src="assets/HS.png" alt="SurveyMaster Logo" style="width:56px; height:56px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
          </div>
          <h2 style="text-align:center; font-weight:700; color:#2d3e50; margin-bottom:18px; letter-spacing:1px;">Respond to Survey</h2>
          <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
          <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
          <h3 style="margin-top:0; text-align:center;"><?php echo htmlspecialchars($survey['title']); ?></h3>
          <?php if (!empty($survey['description'])): ?><p style="text-align:center; color:#555;"><?php echo nl2br(htmlspecialchars($survey['description'])); ?></p><?php endif; ?>
          <form method="POST" id="responseForm" novalidate>
            <input type="hidden" name="action" value="submit_answers" />
            <input type="hidden" name="survey_id" value="<?php echo $surveyId; ?>" />
            <?php foreach ($questions as $q): ?>
              <div class="question">
                <?php $qid = (int)$q['question_id']; ?>
                <div><strong><?php echo htmlspecialchars($q['question_text']); ?></strong></div>
                <small>Type: <?php echo htmlspecialchars($q['question_type']); ?></small>
                <div class="mt-2">
                  <?php if ($q['question_type'] === 'yes_no'): ?>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="q<?php echo $qid; ?>_yes" name="answers[<?php echo $qid; ?>]" value="yes" required>
    <label class="form-check-label" for="q<?php echo $qid; ?>_yes">Yes</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="q<?php echo $qid; ?>_no" name="answers[<?php echo $qid; ?>]" value="no">
    <label class="form-check-label" for="q<?php echo $qid; ?>_no">No</label>
  </div>
<?php elseif ($q['question_type'] === 'text'): ?>
  <textarea name="answers[<?php echo $qid; ?>]" class="form-control" style="max-width:540px; display:block; position:relative; z-index:1; resize:vertical; background:#f8fafc; color:#222;" rows="3" placeholder="Type your answer here..." required></textarea>
<?php elseif ($q['question_type'] === 'rating_1_5'): ?>
  <select name="answers[<?php echo $qid; ?>]" class="form-control" style="max-width:220px; display:inline-block;" required>
    <option value="">Select rating</option>
    <?php for ($i=1; $i<=5; $i++): ?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php endfor; ?>
  </select>
<?php else: ?>
  <input type="text" name="answers[<?php echo $qid; ?>]" class="form-control" style="max-width:540px; display:block; position:relative; z-index:1; background:#f8fafc; color:#222;" placeholder="Type your answer here..." required />
<?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (!empty($questions)): ?>
              <button type="submit" class="btn btn-primary" style="background:#2d3e50; border-color:#2d3e50; font-weight:700; border-radius:6px; padding:10px 16px;">Submit Survey</button>
            <?php endif; ?>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Simple client-side validation for required answers
(function(){
  var form = document.getElementById('responseForm');
  if(!form) return;
  form.addEventListener('submit', function(e){
    var invalid = [];
    // find all question blocks
    document.querySelectorAll('.question').forEach(function(q){
      var radios = q.querySelectorAll('input[type="radio"][name^="answers["]');
      var select = q.querySelector('select[name^="answers[""]');
      var textarea = q.querySelector('textarea[name^="answers[""]');
      var textInput = q.querySelector('input[type="text"][name^="answers["], input[type="number"][name^="answers["]');
      var ok = false;
      if (select) {
        ok = !!select.value;
        select.classList.toggle('is-invalid', !ok);
      } else if (textarea) {
        ok = textarea.value.trim().length > 0;
        textarea.classList.toggle('is-invalid', !ok);
      } else if (textInput) {
        ok = textInput.value.trim().length > 0;
        textInput.classList.toggle('is-invalid', !ok);
      } else if (radios.length) {
        radios.forEach(function(inp){ if (inp.checked) ok = true; });
      }
      if (!ok) invalid.push(q);
    });
    if (invalid.length) {
      e.preventDefault();
      invalid[0].scrollIntoView({behavior:'smooth', block:'center'});
      // flash border
      invalid[0].style.boxShadow = '0 0 0 3px rgba(220,53,69,.25)';
      setTimeout(function(){ invalid[0].style.boxShadow=''; }, 1200);
    }
  });
})();
</script>

<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

