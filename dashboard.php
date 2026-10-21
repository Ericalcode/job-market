<?php
// 1. Output buffering and action logic MUST be at the very top
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Job Application Action (?apply=ID)
if (isset($_GET['apply']) && $role === 'jobseeker') {
    $job_id = intval($_GET['apply']);
    
    // Check if already applied
    $chk = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ?");
    $chk->bind_param("ii", $job_id, $user_id);
    $chk->execute();
    $chk->store_result();
    
    if ($chk->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO applications (job_id, jobseeker_id) VALUES (?, ?)");
        $ins->bind_param("ii", $job_id, $user_id);
        $ins->execute();
    }
    
    // Redirect safely BEFORE any HTML output
    header("Location: dashboard.php?applied=success");
    exit();
}

// Handle Employer Job Posting POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'employer' && isset($_POST['post_job'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    
    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $description, $location);
    $stmt->execute();
    
    header("Location: dashboard.php?posted=success");
    exit();
}
?>
<!-- 2. Now it is safe to include header.php and render the dashboard HTML -->
<?php include 'header.php'; ?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?> (<?php echo ucfirst($role); ?>)</h2>
    
    <?php if (isset($_GET['applied'])): ?>
        <p style="color: green;">Successfully applied for the job!</p>
    <?php endif; ?>

    <?php if ($role === 'employer'): ?>
        <h3>Post a New Job</h3>
        <form method="POST" action="dashboard.php">
            <input type="text" name="title" placeholder="Job Title" required>
            <textarea name="description" placeholder="Job Description" required></textarea>
            <input type="text" name="location" placeholder="Location" required>
            <button type="submit" name="post_job">Post Job</button>
        </form>

        <h3>Your Posted Jobs</h3>
        <!-- Fetch and list employer jobs here -->

    <?php else: ?>
        <h3>Available Jobs</h3>
        <?php
        $jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");
        while($job = $jobs->fetch_assoc()):
        ?>
            <div class="job-card" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px;">
                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <a href="dashboard.php?apply=<?php echo $job['id']; ?>" class="btn">Apply Now</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <p style="margin-top: 20px;"><a href="logout.php">Logout</a></p>
</div>

<?php 
ob_end_flush(); 
?>
