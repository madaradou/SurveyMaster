<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../front_office/login.php');
    exit();
}
require_once '../../../config.php';
$conn = config::getConnexion();
$reply_sent = false;
$reply_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message_id'], $_POST['reply_content'])) {
    $reply_message_id = intval($_POST['reply_message_id']);
    $reply_content = trim($_POST['reply_content']);
    if ($reply_message_id && $reply_content !== '') {
        $stmtMsg = $conn->prepare("SELECT sender_id FROM support_messages WHERE message_id = :mid");
        $stmtMsg->execute(['mid' => $reply_message_id]);
        $msg_row = $stmtMsg->fetch();
        if ($msg_row) {
            $sender_id = $_SESSION['user_id'];
            $receiver_id = $msg_row['sender_id'];
            $stmt = $conn->prepare("INSERT INTO support_messages (sender_id, receiver_id, message_content) VALUES (:sid, :rid, :content)");
            if ($stmt->execute(['sid' => $sender_id, 'rid' => $receiver_id, 'content' => $reply_content])) {
                $reply_sent = true;
            } else {
                $reply_error = 'Failed to send reply.';
            }
        } else {
            $reply_error = 'Original message not found.';
        }
    } else {
        $reply_error = 'Please enter a reply.';
    }
}
$admin_id = $_SESSION['user_id'];
$sql = "SELECT m.*, u.first_name, u.last_name, u.email FROM support_messages m JOIN user u ON m.sender_id = u.user_id WHERE m.receiver_id = :admin_id ORDER BY m.created_at DESC";
$stmtList = $conn->prepare($sql);
$stmtList->execute(['admin_id' => $admin_id]);
$messages = $stmtList->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Agent Messages - Hotelia Smart Agent Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="template/assets/logo/HS.png" type="image/png" />
    <script src="template/assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["template/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>
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
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Received Messages</h4>
                                    <a href="users_management.php" class="btn btn-secondary" style="margin-top:10px;">Back to Dashboard</a>
                                </div>
                                <div class="card-body">
                                    <?php if ($reply_sent): ?>
                                        <div class="alert alert-success">Reply sent successfully!</div>
                                    <?php elseif ($reply_error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($reply_error); ?></div>
                                    <?php endif; ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>From</th>
                                                    <th>Email</th>
                                                    <th>Message</th>
                                                    <th>Date</th>
                                                    <th>Reply</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($messages)): ?>
                                                    <tr><td colspan="5" style="color:#888; text-align:center;">No messages received yet.</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($messages as $msg): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']); ?></td>
                                                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                                            <td style="max-width:300px;white-space:pre-line;"><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></td>
                                                            <td><?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?></td>
                                                            <td>
                                                                <form method="POST" class="reply-form" style="margin-bottom:0;">
                                                                    <input type="hidden" name="reply_message_id" value="<?php echo $msg['message_id']; ?>">
                                                                    <div class="form-group">
                                                                        <textarea name="reply_content" class="form-control" rows="2" placeholder="Type your reply..."></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-primary btn-sm">Send Reply</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
