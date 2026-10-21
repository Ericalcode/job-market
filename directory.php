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

$current_user_role = $_SESSION['role'] ?? 'jobseeker';

// Helper function to mask user IDs for non-admin users (e.g., 12 becomes 1*2, 12345 becomes 1***5)
function mask_user_id($id, $role) {
    if ($role === 'admin') {
        return $id; // Admin sees real ID
    }
    $str = (string)$id;
    $len = strlen($str);
    if ($len <= 2) {
        return $str[0] . '*';
    }
    return $str[0] . str_repeat('*', $len - 2) . $str[$len - 1];
}
?>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Platform Directory (Profiles)</h2>
    <p>Browse active employers and jobseekers on the platform.</p>
    <hr style="margin: 20px 0;">

    <?php
    $sql = "SELECT u.id, u.fullname, u.email, u.role, p.title, p.skills, p.bio 
            FROM users u 
            LEFT JOIN profiles p ON u.id = p.user_id 
            WHERE u.role != 'admin' 
            ORDER BY u.created_at DESC";
    $result = $conn->query($sql);
    ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            <?php while($row = $result->fetch_assoc()): ?>
                <div style="border: 1px solid #ccc; padding: 15px; border-radius: 5px; background: #fff;">
                    <p style="font-size: 0.9em; color: #666;"><strong>User ID:</strong> <?php echo mask_user_id($row['id'], $current_user_role); ?></p>
                    <h4><?php echo htmlspecialchars($row['fullname']); ?></h4>
                    <p><strong>Role:</strong> <span style="text-transform: uppercase; color: #007bff;"><?php echo $row['role']; ?></span></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                    <p><strong>Professional Title:</strong> <?php echo htmlspecialchars($row['title'] ?? 'Not specified'); ?></p>
                    <p><strong>Skills:</strong> <?php echo htmlspecialchars($row['skills'] ?? 'N/A'); ?></p>
                    <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($row['bio'] ?? 'No bio provided.')); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No member profiles found.</p>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="dashboard.php" class="btn">&larr; Back to Dashboard</a></p>
</div>

<?php ob_end_flush(); ?>
