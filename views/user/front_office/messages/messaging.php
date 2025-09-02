<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../front_office/login.php');
    exit();
}

require_once '../../../../config.php';
$conn = config::getConnexion();

// Fetch all agents
$admins = [];
$admin_sql = "SELECT user_id, first_name, last_name, email FROM user WHERE role = 'agent'";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->execute();
$admins = $admin_stmt->fetchAll();

// Handle message sending
$message_sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['message_content'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id']);
    $message_content = trim($_POST['message_content']);
    if ($receiver_id && $message_content !== '') {
        $stmt = $conn->prepare("INSERT INTO support_messages (sender_id, receiver_id, message_content) VALUES (:sid, :rid, :content)");
        if ($stmt->execute(['sid' => $sender_id, 'rid' => $receiver_id, 'content' => $message_content])) {
            $message_sent = true;
        } else {
            $error = 'Failed to send message.';
        }
    } else {
        $error = 'Please select an admin and enter a message.';
    }
}

// Fetch messages sent and received by the user
$user_id = $_SESSION['user_id'];
$messages_sql = "SELECT m.*, u1.first_name AS sender_first, u1.last_name AS sender_last, u2.first_name AS receiver_first, u2.last_name AS receiver_last FROM support_messages m JOIN user u1 ON m.sender_id = u1.user_id JOIN user u2 ON m.receiver_id = u2.user_id WHERE m.sender_id = :uid OR m.receiver_id = :uid ORDER BY m.created_at DESC";
$msg_stmt = $conn->prepare($messages_sql);
$msg_stmt->execute(['uid' => $user_id]);
$messages = $msg_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Messaging</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/stylefront3.css">
    <style>
        .msg-container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 32px; }
        .msg-form { margin-bottom: 32px; }
        .msg-list { max-height: 350px; overflow-y: auto; margin-bottom: 24px; }
        .msg-item { border-bottom: 1px solid #e0e7ef; padding: 12px 0; }
        .msg-item:last-child { border-bottom: none; }
        .msg-meta { font-size: 13px; color: #888; }
        .msg-content { margin: 6px 0 0 0; font-size: 16px; }
        .msg-sent { background: #e6f4ea; border-radius: 8px; padding: 10px; }
        .msg-received { background: #f7fafd; border-radius: 8px; padding: 10px; }
        .msg-status { font-size: 12px; color: #888; float: right; }
    </style>
</head>
<body style="background: #f5f7fa;">
    <div class="msg-container">
        <h2 style="text-align:center; font-weight:700; color:#2d3e50; margin-bottom:24px;">Contact Agent Support</h2>
        <?php if ($message_sent): ?>
            <div class="alert alert-success">Message sent successfully!</div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" class="msg-form">
            <div class="form-group">
                <label for="receiver_id">Send to Agent:</label>
                <select name="receiver_id" id="receiver_id" class="form-control" required>
                    <option value="">Select Agent</option>
                    <?php foreach ($admins as $admin): ?>
                        <option value="<?php echo $admin['user_id']; ?>"><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'] . ' (' . $admin['email'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="message_content">Message:</label>
                <textarea name="message_content" id="message_content" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
            <a href="../profile.php" class="btn btn-secondary">Back to Profile</a>
        </form>
        <h4 style="margin-top:32px;">Your Messages</h4>
        <div class="msg-list">
            <?php if (empty($messages)): ?>
                <div style="color:#888; text-align:center;">No messages yet.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="msg-item <?php echo $msg['sender_id'] == $user_id ? 'msg-sent' : 'msg-received'; ?>">
                        <div class="msg-meta">
                            <strong><?php echo $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($msg['sender_first'] . ' ' . $msg['sender_last']); ?></strong>
                            to
                            <strong><?php echo $msg['receiver_id'] == $user_id ? 'You' : htmlspecialchars($msg['receiver_first'] . ' ' . $msg['receiver_last']); ?></strong>
                            <span class="msg-status">[<?php echo ucfirst($msg['status']); ?>]</span>
                            <span style="float:right; color:#aaa; font-size:12px; margin-left:8px;">
                                <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                            </span>
                        </div>
                        <div class="msg-content"><?php echo nl2br(htmlspecialchars($msg['message_content'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
