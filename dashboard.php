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
$role = $_SESSION['role'];

// --- 1. HANDLE ACTIONS ---

// Jobseeker or Employer requesting job removal / completion
if (isset($_GET['request_remove']) && ($role === 'jobseeker' || $role === 'employer')) {
    $job_id = intval($_GET['request_remove']);
    $stmt = $conn->prepare("UPDATE jobs SET status = 'pending_removal' WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    header("Location: dashboard.php?msg=requested");
    exit();
}

// Admin approving job removal / closing
if (isset($_GET['admin_delete_job']) && $role === 'admin') {
    $job_id = intval($_GET['admin_delete_job']);
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    header("Location: dashboard.php?msg=job_removed");
    exit();
}

// Admin deleting a user
if (isset($_GET['admin_delete_user']) && $role === 'admin') {
    $del_user_id = intval($_GET['admin_delete_user']);
    if ($del_user_id !== $user_id) { // Prevent self-deletion
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $del_user_id);
        $stmt->execute();
    }
    header("Location: dashboard.php?msg=user_removed");
    exit();
}

// Job Application Action
if (isset($_GET['apply']) && $role === 'jobseeker') {
    $job_id = intval($_GET['apply']);
    $chk = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ?");
    $chk->bind_param("ii", $job_id, $user_id);
    $chk->execute();
    $chk->store_result();
    
    if ($chk->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO applications (job_id, jobseeker_id) VALUES (?, ?)");
        $ins->bind_param("ii", $job_id, $user_id);
        $ins->execute();
    }
    header("Location: dashboard.php?applied=success");
    exit();
}

// Employer Posting a Job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'employer' && isset($_POST['post_job'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    
    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("isss", $user_id, $title, $description, $location);
    $stmt->execute();
    
    header("Location: dashboard.php?posted=success");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?> (<?php echo ucfirst($role); ?>)</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green; font-weight: bold;">Action completed successfully!</p>
    <?php endif; ?>

    <!-- ================= ADMIN VIEW ================= -->
    <?php if ($role === 'admin'): ?>
        <hr>
        <h3>Admin Panel: Platform Overview</h3>
        
        <h4>All Registered Employers</h4>
        <?php
        $employers = $conn->query("SELECT id, fullname, email, created_at FROM users WHERE role = 'employer'");
        ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr style="background:#f2f2f2;"><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Action</th></tr>
            <?php while($emp = $employers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $emp['id']; ?></td>
                <td><?php echo htmlspecialchars($emp['fullname']); ?></td>
                <td><?php echo htmlspecialchars($emp['email']); ?></td>
                <td><?php echo $emp['created_at']; ?></td>
                <td><a href="dashboard.php?admin_delete_user=<?php echo $emp['id']; ?>" onclick="return confirm('Delete this employer?');" style="color:red;">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h4>All Registered Employees (Jobseekers)</h4>
        <?php
        $seekers = $conn->query("SELECT id, fullname, email, created_at FROM users WHERE role = 'jobseeker'");
        ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr style="background:#f2f2f2;"><th>ID</th><th>Name</th><th>Email</th><th>Joined</th><th>Action</th></tr>
            <?php while($seek = $seekers->fetch_assoc()): ?>
            <tr>
                <td><?php echo $seek['id']; ?></td>
                <td><?php echo htmlspecialchars($seek['fullname']); ?></td>
                <td><?php echo htmlspecialchars($seek['email']); ?></td>
                <td><?php echo $seek['created_at']; ?></td>
                <td><a href="dashboard.php?admin_delete_user=<?php echo $seek['id']; ?>" onclick="return confirm('Delete this jobseeker?');" style="color:red;">Delete</a></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <h4>All Posted Jobs & Removal Requests</h4>
        <?php
        $all_jobs = $conn->query("SELECT j.*, u.fullname AS employer_name FROM jobs j JOIN users u ON j.employer_id = u.id ORDER BY j.created_at DESC");
        ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr style="background:#f2f2f2;"><th>ID</th><th>Title</th><th>Employer</th><th>Status</th><th>Action</th></tr>
            <?php while($j = $all_jobs->fetch_assoc()): ?>
            <tr <?php echo ($j['status'] === 'pending_removal') ? 'style="background-color: #fff3cd;"' : ''; ?>>
                <td><?php echo $j['id']; ?></td>
                <td><?php echo htmlspecialchars($j['title']); ?></td>
                <td><?php echo htmlspecialchars($j['employer_name']); ?></td>
                <td><strong><?php echo ucfirst($j['status']); ?></strong></td>
                <td>
                    <a href="dashboard.php?admin_delete_job=<?php echo $j['id']; ?>" onclick="return confirm('Remove/Close this job posting?');" style="color:red; font-weight:bold;">Remove Job</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

    <!-- ================= EMPLOYER VIEW ================= -->
    <?php elseif ($role === 'employer'): ?>
        <h3>Post a New Job</h3>
        <form method="POST" action="dashboard.php" style="margin-bottom: 20px;">
            <input type="text" name="title" placeholder="Job Title" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
            <textarea name="description" placeholder="Job Description" required style="display:block; width:100%; margin-bottom:10px; padding:8px;"></textarea>
            <input type="text" name="location" placeholder="Location" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
            <button type="submit" name="post_job">Post Job</button>
        </form>

        <h3>Your Job Postings</h3>
        <?php
        $my_jobs = $conn->prepare("SELECT * FROM jobs WHERE employer_id = ? ORDER BY created_at DESC");
        $my_jobs->bind_param("i", $user_id);
        $my_jobs->execute();
        $res = $my_jobs->get_result();
        while($job = $res->fetch_assoc()):
        ?>
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; background: #fff;">
                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($job['status']); ?></p>
                <?php if ($job['status'] === 'active'): ?>
                    <a href="dashboard.php?request_remove=<?php echo $job['id']; ?>" class="btn" style="background:#d9534f; color:#fff; padding:5px 10px; text-decoration:none;">Request Admin Removal (Completed)</a>
                <?php else: ?>
                    <span style="color:orange;">Removal requested / Pending Admin Review</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

    <!-- ================= JOBSEEKER VIEW ================= -->
    <?php else: ?>
        <h3>Available Jobs</h3>
        <?php
        $jobs = $conn->query("SELECT j.*, u.fullname AS employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.status = 'active' ORDER BY j.created_at DESC");
        while($job = $jobs->fetch_assoc()):
        ?>
            <div style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; background: #fff;">
                <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                <p><em>Posted by: <?php echo htmlspecialchars($job['employer_name']); ?></em></p>
                <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                
                <a href="dashboard.php?apply=<?php echo $job['id']; ?>" class="btn">Apply Now</a>
                <a href="dashboard.php?request_remove=<?php echo $job['id']; ?>" onclick="return confirm('Request admin to close/remove this job as completed/satisfied?');" style="margin-left: 15px; color: #d9534f;">Request Completion/Removal</a>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="reports.php">View SQL Reports</a> | <a href="logout.php">Logout</a></p>
</div>

<?php 
ob_end_flush(); 
?>
