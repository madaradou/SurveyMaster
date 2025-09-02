<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front_office/login.php'); exit();
}
require_once '../../../controllers/CampaignController.php';
require_once '../../../controllers/SurveyController.php';
$camp = new CampaignController();
$surveyCtrl = new SurveyController();
$adminId = (int)$_SESSION['user_id'];

$success = '';$error='';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'create') {
        $id = $camp->create($adminId, trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''), $_POST['start_at'] ?: null, $_POST['end_at'] ?: null, $_POST['status'] ?? 'draft');
        $success = 'Campaign created';
        header('Location: campaigns.php?campaign_id='.$id); exit();
    } elseif ($action === 'update') {
        $cid = (int)($_POST['campaign_id'] ?? 0);
        if ($cid) {
            $camp->update($cid, trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''), $_POST['start_at'] ?: null, $_POST['end_at'] ?: null, $_POST['status'] ?? 'draft');
            $success = 'Campaign updated';
        }
    } elseif ($action === 'delete') {
        $cid = (int)($_POST['campaign_id'] ?? 0);
        if ($cid) { $camp->delete($cid); $success = 'Campaign deleted'; header('Location: campaigns.php'); exit(); }
    } elseif ($action === 'link_surveys') {
        $cid = (int)($_POST['campaign_id'] ?? 0);
        $sids = array_map('intval', $_POST['survey_ids'] ?? []);
        if ($cid) { $camp->setCampaignSurveys($cid, $sids); $success = 'Linked surveys updated'; }
    } elseif ($action === 'assign_agents') {
        $cid = (int)($_POST['campaign_id'] ?? 0);
        $aids = array_map('intval', $_POST['agent_ids'] ?? []);
        if ($cid) { $camp->setCampaignAgents($cid, $aids); $success = 'Agents assigned'; }
    }
} catch (Exception $e) { $error = $e->getMessage(); }

$currentId = (int)($_GET['campaign_id'] ?? 0);
$current = $currentId ? $camp->get($currentId) : null;
$campaigns = $camp->listByAdmin($adminId);
$mySurveys = $surveyCtrl->listByAgent($adminId);
$linked = $currentId ? $camp->getCampaignSurveyIds($currentId) : [];
$agents = $camp->listAgents();
$assignedAgents = $currentId ? $camp->getCampaignAgentIds($currentId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Campaigns - Back Office</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="template/assets/logo/HS.png" type="image/png" />
    <link rel="stylesheet" href="template/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="template/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="template/assets/css/hotelia smart.min.css" />
    <link rel="stylesheet" href="template/assets/css/demo.css" />
</head>
<body>
<div class="wrapper">
    <?php include 'dashboard_side_bar.php'; ?>
    <div class="main-panel">
        <?php include 'header_bar.php'; ?>
        <div class="content">
            <div class="page-inner">
                <div class="row">
                    <div class="col-md-12">
                        <?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header"><h4 class="card-title">Campaigns</h4></div>
                                    <div class="card-body">
                                        <ul class="list-group">
                                            <?php foreach ($campaigns as $c): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <a href="campaigns.php?campaign_id=<?php echo $c['campaign_id']; ?>"><?php echo htmlspecialchars($c['title']); ?></a>
                                                    <form method="POST" onsubmit="return confirm('Delete campaign?');">
                                                        <input type="hidden" name="action" value="delete" />
                                                        <input type="hidden" name="campaign_id" value="<?php echo $c['campaign_id']; ?>" />
                                                        <button class="btn btn-danger btn-sm">Delete</button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <hr />
                                        <form method="POST">
                                            <input type="hidden" name="action" value="create" />
                                            <div class="form-group"><label>Title</label><input name="title" class="form-control" /></div>
                                            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                                            <div class="form-group"><label>Start</label><input type="datetime-local" name="start_at" class="form-control" /></div>
                                            <div class="form-group"><label>End</label><input type="datetime-local" name="end_at" class="form-control" /></div>
                                            <div class="form-group"><label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="draft">draft</option>
                                                    <option value="active">active</option>
                                                    <option value="archived">archived</option>
                                                </select>
                                            </div>
                                            <button class="btn btn-primary">Create</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header"><h4 class="card-title">Campaign Builder</h4></div>
                                    <div class="card-body">
                                        <?php if ($current): ?>
                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="action" value="update" />
                                                <input type="hidden" name="campaign_id" value="<?php echo $current['campaign_id']; ?>" />
                                                <div class="form-group"><label>Title</label><input name="title" class="form-control" value="<?php echo htmlspecialchars($current['title']); ?>" /></div>
                                                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($current['description']); ?></textarea></div>
                                                <div class="form-group"><label>Start</label><input type="datetime-local" name="start_at" class="form-control" value="<?php echo $current['start_at'] ? date('Y-m-d\TH:i', strtotime($current['start_at'])) : '';?>" /></div>
                                                <div class="form-group"><label>End</label><input type="datetime-local" name="end_at" class="form-control" value="<?php echo $current['end_at'] ? date('Y-m-d\TH:i', strtotime($current['end_at'])) : '';?>" /></div>
                                                <div class="form-group"><label>Status</label>
                                                    <select name="status" class="form-control">
                                                        <?php foreach (['draft','active','archived'] as $st): ?>
                                                            <option value="<?php echo $st; ?>" <?php echo $current['status']===$st?'selected':''; ?>><?php echo $st; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button class="btn btn-secondary">Save</button>
                                            </form>

                                            <h5>Link Surveys</h5>
                                            <form method="POST">
                                                <input type="hidden" name="action" value="link_surveys" />
                                                <input type="hidden" name="campaign_id" value="<?php echo $current['campaign_id']; ?>" />
                                                <div class="form-group">
                                                    <label>Select Surveys</label>
                                                    <select name="survey_ids[]" class="form-control" size="10" multiple>
                                                        <?php foreach ($mySurveys as $s): ?>
                                                            <option value="<?php echo $s['survey_id']; ?>" <?php echo in_array($s['survey_id'], $linked, true)?'selected':''; ?>>
                                                                <?php echo htmlspecialchars($s['title']); ?>
                                                            </option>
                                            <h5>Assign Agents</h5>
                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="action" value="assign_agents" />
                                                <input type="hidden" name="campaign_id" value="<?php echo $current['campaign_id']; ?>" />
                                                <div class="form-group">
                                                    <label>Select Agents</label>
                                                    <select name="agent_ids[]" class="form-control" size="8" multiple>
                                                        <?php foreach ($agents as $a): ?>
                                                            <option value="<?php echo $a['user_id']; ?>" <?php echo in_array($a['user_id'], $assignedAgents, true)?'selected':''; ?>>
                                                                <?php echo htmlspecialchars($a['first_name'].' '.$a['last_name'].' ('.$a['email'].')'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button class="btn btn-primary">Save Agents</button>
                                            </form>


                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <button class="btn btn-primary">Update Links</button>
                                            </form>
                                        <?php else: ?>
                                            <p>Select a campaign to edit details and link surveys.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../../js/campaigns.js"></script>
</body>
</html>

