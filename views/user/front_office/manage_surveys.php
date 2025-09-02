<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') { header('Location: login.php'); exit(); }
require_once '../../../controllers/SurveyController.php';
require_once '../../../controllers/CampaignController.php';
$controller = new SurveyController();
$campaignCtl = new CampaignController();
$agentId = (int)$_SESSION['user_id'];

$success = '';$error='';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
try {
    if ($action === 'create_survey') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $campaignIds = array_map('intval', $_POST['campaign_ids'] ?? []);
        if ($title === '') throw new Exception('Title is required');
        if (empty($campaignIds)) throw new Exception('Link at least one campaign you are assigned to');
        $newId = $controller->create($agentId, $title, $description);
        // Link to campaigns (only those assigned to the agent are shown in UI)
        $campaignCtl->setSurveyCampaigns($newId, $campaignIds);
        $success = 'Survey created';
        header('Location: manage_surveys.php?survey_id='.$newId); exit();
    } elseif ($action === 'update_survey') {
        $sid = (int)($_POST['survey_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $campaignIds = array_map('intval', $_POST['campaign_ids'] ?? []);
        if ($sid && $title !== '') {
            $controller->update($sid, $title, $description);
            $campaignCtl->setSurveyCampaigns($sid, $campaignIds);
            $success = 'Survey updated';
        }
    } elseif ($action === 'delete_survey') {
        $sid = (int)($_POST['survey_id'] ?? 0);
        if ($sid) { $controller->delete($sid); $success = 'Survey deleted'; header('Location: manage_surveys.php'); exit(); }
    } elseif ($action === 'add_question') {
        $sid = (int)($_POST['survey_id'] ?? 0);
        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] ?? '';
        if ($sid && $text !== '' && in_array($type, ['yes_no','text','rating_1_5','multiple_choice'], true)) {
            $controller->addQuestion($sid, $type, $text, (int)($_POST['position'] ?? 0));
            $success = 'Question added';
        } else { throw new Exception('Invalid question'); }
    } elseif ($action === 'update_question') {
        $qid = (int)($_POST['question_id'] ?? 0);
        $text = trim($_POST['question_text'] ?? '');
        $type = $_POST['question_type'] ?? '';
        if ($qid && $text !== '' && in_array($type, ['yes_no','text','rating_1_5','multiple_choice'], true)) {
            $controller->updateQuestion($qid, $type, $text, (int)($_POST['position'] ?? 0));
            $success = 'Question updated';
        }
    } elseif ($action === 'delete_question') {
        $qid = (int)($_POST['question_id'] ?? 0);
        if ($qid) { $controller->deleteQuestion($qid); $success = 'Question deleted'; }
    } elseif ($action === 'add_choice') {
        $qid = (int)($_POST['question_id'] ?? 0);
        $text = trim($_POST['choice_text'] ?? '');
        if ($qid && $text !== '') { $controller->addChoice($qid, $text, (int)($_POST['position'] ?? 0)); $success = 'Choice added'; }
    } elseif ($action === 'update_choice') {
        $cid = (int)($_POST['choice_id'] ?? 0); $text = trim($_POST['choice_text'] ?? '');
        if ($cid && $text !== '') { $controller->updateChoice($cid, $text, (int)($_POST['position'] ?? 0)); $success = 'Choice updated'; }
    } elseif ($action === 'delete_choice') {
        $cid = (int)($_POST['choice_id'] ?? 0); if ($cid) { $controller->deleteChoice($cid); $success = 'Choice deleted'; }
    } elseif ($action === 'add_choice_multi') {
        $qid = (int)($_POST['question_id'] ?? 0);
        $texts = $_POST['choice_text'] ?? [];
        $positions = $_POST['position'] ?? [];
        if ($qid && is_array($texts)) {
            $added = 0;
            foreach ($texts as $idx => $t) {
                $t = trim($t);
                if ($t === '') continue;
                $pos = isset($positions[$idx]) ? (int)$positions[$idx] : 0;
                $controller->addChoice($qid, $t, $pos);
                $added++;
            }
            if ($added > 0) { $success = 'Choices added'; }
        }
    } elseif ($action === 'assign_clients') {
        $sid = (int)($_POST['survey_id'] ?? 0);
        $clients = array_map('intval', $_POST['client_ids'] ?? []);
        if ($sid) { $controller->assignToClients($sid, $clients); $success = 'Assignments updated'; }
    }
} catch (Exception $ex) { $error = $ex->getMessage(); }

$currentSurveyId = (int)($_GET['survey_id'] ?? 0);
$currentSurvey = $currentSurveyId ? $controller->get($currentSurveyId) : null;
$surveys = $controller->listByAgent($agentId);
$questions = $currentSurveyId ? $controller->listQuestions($currentSurveyId) : [];
$clients = $controller->listClients();
$assigned = $currentSurveyId ? $controller->listAssignments($currentSurveyId) : [];
$agentCampaigns = $campaignCtl->listForAgentMember($agentId);
$currentSurveyCampaignIds = $currentSurveyId ? $campaignCtl->getSurveyCampaignIds($currentSurveyId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Surveys | SurveyMaster</title>
    <link rel="shortcut icon" type="image/icon" href="assets/HS.png"/>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/stylefront3.css">
    <style>
        .flex { display:flex; gap:16px; }
        .col { flex:1; min-width: 280px; }
        .card { margin-bottom: 16px; border-radius: 12px; }
        .list-group-item small { color:#888; }
        .badge-type { text-transform: uppercase; font-size: 11px; }
    </style>
</head>
<body style="background:#f5f7fa;">
<?php /* header */ ?>
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
                    <li class="header-top-contact"><a href="home.php">Home</a></li>
                    <li class="header-top-contact"><a href="profile.php">Profile</a></li>
                    <li class="header-top-contact"><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </li>
    </ul>
</header>
<div class="container" style="max-width:1100px; margin:24px auto;">
    <h2>Manage Surveys</h2>
    <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="flex">
        <div class="col">
            <div class="card">
                <div class="card-header"><h4 class="card-title">My Surveys</h4></div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($surveys as $s): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="manage_surveys.php?survey_id=<?php echo $s['survey_id']; ?>"><?php echo htmlspecialchars($s['title']); ?></a>
                                <form method="POST" onsubmit="return confirm('Delete survey?');">
                                    <input type="hidden" name="action" value="delete_survey" />
                                    <input type="hidden" name="survey_id" value="<?php echo $s['survey_id']; ?>" />
                                    <button class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr />
                    <form method="POST" class="mt-2">
                        <div class="alert alert-info" id="ms-validation-msg" style="display:none;"></div>

                        <input type="hidden" name="action" value="create_survey" />
                        <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" required /></div>
                        <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                        <div class="form-group"><label>Link to Campaign(s)</label>
                            <select name="campaign_ids[]" class="form-control" multiple size="6">
                                <?php foreach ($agentCampaigns as $c): ?>
                                    <option value="<?php echo $c['campaign_id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary">Create Survey</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Survey Builder</h4></div>
                <div class="card-body">
                    <?php if ($currentSurvey): ?>
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="update_survey" />


                            <input type="hidden" name="survey_id" value="<?php echo $currentSurvey['survey_id']; ?>" />
                            <div class="form-group"><label>Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($currentSurvey['title']); ?>" required /></div>
                            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($currentSurvey['description']); ?></textarea></div>
                            <div class="form-group"><label>Campaigns</label>
                                <select name="campaign_ids[]" class="form-control" multiple size="6">
                                    <?php foreach ($agentCampaigns as $c): ?>
                                        <option value="<?php echo $c['campaign_id']; ?>" <?php echo in_array($c['campaign_id'], $currentSurveyCampaignIds, true)?'selected':''; ?>>
                                            <?php echo htmlspecialchars($c['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-secondary">Save</button>

                        <h5>Responses</h5>
                        <div class="table-responsive">
                          <table class="table table-sm table-bordered">
                            <thead><tr><th>#</th><th>Client</th><th>Email</th><th>Date</th><th>Details</th></tr></thead>
                            <tbody>
                              <?php foreach ($controller->listResponsesSummary($currentSurvey['survey_id']) as $r): ?>
                                <tr>
                                  <td><?php echo (int)$r['response_id']; ?></td>
                                  <td><?php echo htmlspecialchars(($r['first_name']??'').' '.($r['last_name']??'')); ?></td>
                                  <td><?php echo htmlspecialchars($r['email'] ?? ''); ?></td>
                                  <td><?php echo htmlspecialchars($r['created_at'] ?? ''); ?></td>
                                  <td>
                                    <button type="button" class="btn btn-link p-0" onclick="toggleResp('<?php echo $r['response_id']; ?>')">View</button>
                                  </td>
                                </tr>
                                <tr id="resp-row-<?php echo $r['response_id']; ?>" style="display:none;">
                                  <td colspan="5">
                                    <ul class="list-group">
                                      <?php foreach ($controller->listResponseAnswers((int)$r['response_id']) as $a): ?>
                                        <li class="list-group-item">
                                          <strong><?php echo htmlspecialchars($a['question_text']); ?></strong>
                                          <?php if ($a['question_type']==='yes_no'): ?>
                                            — Answer: <?php echo $a['yes_no'] ? 'Yes' : 'No'; ?>
                                          <?php elseif ($a['question_type']==='rating_1_5'): ?>
                                            — Rating: <?php echo (int)$a['rating']; ?>/5
                                          <?php elseif ($a['question_type']==='multiple_choice'): ?>
                                            — Choice: <?php echo htmlspecialchars($a['choice_text'] ?? ('#'.$a['choice_id'])); ?>
                                          <?php else: ?>
                                            — Answer: <?php echo htmlspecialchars($a['answer_text']); ?>
                                          <?php endif; ?>
                                        </li>
                                      <?php endforeach; ?>
                                    </ul>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>

                        </form>

                        <h5>Questions</h5>
                        <ul class="list-group mb-3">
                            <?php foreach ($questions as $q): ?>
                                <li class="list-group-item">


                                    <form method="POST" class="d-flex align-items-center" style="gap:8px;">
                                        <input type="hidden" name="action" value="update_question" />
                                        <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>" />
                                        <input type="number" name="position" class="form-control" style="width:80px;" value="<?php echo (int)$q['position']; ?>" />
                                        <select name="question_type" class="form-control" style="width:200px;">
                                            <option value="yes_no" <?php echo $q['question_type']==='yes_no'?'selected':''; ?>>Yes/No</option>
                                            <option value="text" <?php echo $q['question_type']==='text'?'selected':''; ?>>Text</option>
                                            <option value="rating_1_5" <?php echo $q['question_type']==='rating_1_5'?'selected':''; ?>>Rating 1-5</option>
                                        </select>
                                        <input type="text" name="question_text" class="form-control" value="<?php echo htmlspecialchars($q['question_text']); ?>" />
                                        <button class="btn btn-secondary btn-sm">Update</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Delete question?');" class="mt-1">
                                        <input type="hidden" name="action" value="delete_question" />
                                        <input type="hidden" name="question_id" value="<?php echo $q['question_id']; ?>" />
                                        <button class="btn btn-danger btn-sm">Delete</button>
                                    </form>

                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <form method="POST" class="d-flex align-items-center" style="gap:8px;" id="addQuestionForm">
                            <input type="hidden" name="action" value="add_question" />
                            <input type="hidden" name="survey_id" value="<?php echo $currentSurvey['survey_id']; ?>" />
                            <input type="number" name="position" class="form-control" style="width:80px;" value="0" />
                            <select name="question_type" class="form-control" style="width:200px;">
                                <option value="yes_no">Yes/No</option>
                                <option value="text">Text</option>
                                <option value="rating_1_5">Rating 1-5</option>
                            </select>
                            <input type="text" name="question_text" class="form-control" placeholder="Question text (end with ?)" />
                            <button class="btn btn-primary">Add Question</button>
                        </form>
                    <?php else: ?>
                        <p>Select a survey to edit its questions.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Assign to Clients</h4></div>
                <div class="card-body">
                    <?php if ($currentSurvey): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="assign_clients" />
                            <input type="hidden" name="survey_id" value="<?php echo $currentSurvey['survey_id']; ?>" />
                            <div class="form-group">
                                <label>Select Clients</label>
                                <select name="client_ids[]" class="form-control" multiple size="10">
                                    <?php foreach ($clients as $cl): ?>
                                        <option value="<?php echo $cl['user_id']; ?>" <?php echo in_array($cl['user_id'], $assigned, true)?'selected':''; ?>>
                                            <?php echo htmlspecialchars($cl['first_name'].' '.$cl['last_name'].' ('.$cl['email'].')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary">Save Assignments</button>
                        </form>
                    <?php else: ?>
                        <p>Create or select a survey to assign it to clients.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
<script>
function toggleResp(id){
  var row = document.getElementById('resp-row-'+id);
  if (!row) return; row.style.display = row.style.display==='none' ? '' : 'none';
}
</script>

    </div>
</div>
<script src="assets/js/bootstrap.min.js"></script>
<script>
// Client-side form validation (controle de saisie)
(function(){
  document.addEventListener('submit', function(e){
    var f = e.target; if (!f || !f.querySelector) return;
    var actionEl = f.querySelector('input[name="action"]');
    if (!actionEl) return;
    var action = actionEl.value;

    // Create survey: require title and at least one campaign
    if (action === 'create_survey') {
      var msg = document.getElementById('ms-validation-msg');
      var title = f.querySelector('input[name="title"]');
      var campSel = f.querySelector('select[name="campaign_ids[]"]');
      var errors = [];
      if (!title || !title.value.trim()) errors.push('Title is required');
      if (!campSel || (campSel.selectedOptions && campSel.selectedOptions.length === 0)) errors.push('Select at least one campaign');
      if (errors.length) {
        e.preventDefault();
        if (msg) {
          msg.style.display = 'block'; msg.className = 'alert alert-danger'; msg.innerHTML = errors.join('<br>');
        }
        (title && !title.value.trim() ? title : campSel).scrollIntoView({behavior:'smooth', block:'center'});
        return;
      }
    }

    // Add/update question: require text, valid type, and ensure it ends with '?'
    if (action === 'add_question' || action === 'update_question') {
      var qText = f.querySelector('input[name="question_text"]');
      var qType = f.querySelector('select[name="question_type"]');
      var msgTxt = [];
      if (!qText || !qText.value.trim()) msgTxt.push('Question text is required');
      if (!qType || !qType.value) msgTxt.push('Select a question type');
      if (qText && qText.value.trim() && !/\?$/ .test(qText.value.trim())) {
        msgTxt.push("Question text must end with '?'");
      }
      if (msgTxt.length) {
        e.preventDefault(); alert(msgTxt.join('\n')); qText && qText.focus(); return;
      }
    }
  });
})();
</script>

</body>
</html>

