<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Password Change
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed, $user_id);
            $upd->execute();
            $msg = "Password updated successfully!";
        } else {
            $error = "Incorrect current password.";
        }
    }

    // Email Change Request (Pending Admin Approval)
    if (isset($_POST['request_email'])) {
        $new_email = trim($_POST['new_email']);
        
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? OR pending_email = ?");
        $chk->bind_param("ss", $new_email, $new_email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "This email address is already in use or pending approval.";
        } else {
            $req = $conn->prepare("UPDATE users SET pending_email = ? WHERE id = ?");
            $req->bind_param("si", $new_email, $user_id);
            $req->execute();
            $msg = "Email change request submitted! Waiting for Admin approval.";
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container" style="max-width: 600px;">
    <h2>Account Settings</h2>
    
    <?php if (!empty($msg)): ?><p style="color: green; font-weight: bold;"><?php echo $msg; ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p style="color: red; font-weight: bold;"><?php echo $error; ?></p><?php endif; ?>

    <hr style="margin: 20px 0;">

    <h3>Change Password</h3>
    <form method="POST" style="margin-bottom: 30px;">
        <input type="password" name="old_password" placeholder="Current Password" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <input type="password" name="new_password" placeholder="New Password" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <button type="submit" name="change_password">Update Password</button>
    </form>

    <h3>Request Email Change</h3>
    <p><small>Note: Email updates require approval from the platform Administrator.</small></p>
    <form method="POST">
        <input type="email" name="new_email" placeholder="New Email Address" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <button type="submit" name="request_email">Request Email Change</button>
    </form>

    <p style="margin-top: 30px;"><a href="dashboard.php" class="btn">&larr; Back to Dashboard</a></p>
</div>

<?php ob_end_flush(); ?>
