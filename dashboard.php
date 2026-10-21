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
$msg = '';

// --- ADMIN ACTIONS ---
if ($role === 'admin') {
    // Approve Email Change
    if (isset($_GET['approve_email'])) {
        $target_id = intval($_GET['approve_email']);
        $q = $conn->prepare("SELECT pending_email FROM users WHERE id = ?");
        $q->bind_param("i", $target_id);
        $q->execute();
        $res = $q->get_result()->fetch_assoc();
        if ($res && $res['pending_email']) {
            $new_email = $res['pending_email'];
            $upd = $conn->prepare("UPDATE users SET email = ?, pending_email = NULL WHERE id = ?");
            $upd->bind_param("si", $new_email, $target_id);
            $upd->execute();
            $msg = "Email change approved successfully!";
        }
    }

    // Delete User
    if (isset($_GET['delete_user'])) {
        $del_id = intval($_GET['delete_user']);
        if ($del_id !== $user_id) {
            $del = $conn->prepare("DELETE FROM users WHERE id = ?");
            $del->bind_param("i", $del_id);
            $del->execute();
            $msg = "User account deleted.";
        }
    }

    // Add Subscription Fee Record
    if (isset($_POST['add_subscription'])) {
        $sub_user_id = intval($_POST['user_id']);
        $amount = floatval($_POST['amount']);
        $sub_stmt = $conn->prepare("INSERT INTO subscriptions (user_id, amount, status) VALUES (?, ?, 'active')");
        $sub_stmt->bind_param("id", $sub_user_id, $amount);
        $sub_stmt->execute();
        $msg = "Subscription fee added successfully!";
    }

    // Create User via Admin
    if (isset($_POST['create_user'])) {
        $name = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $new_role = $_POST['new_role'];

        $ins = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $ins->bind_param("ssss", $name, $email, $pass, $new_role);
        $ins->execute();
        $msg = "New user account created successfully!";
    }
}

// --- JOBSEEKER JOB APPLICATION WITH EMAIL NOTIFICATION ---
if (isset($_GET['apply']) && $role === 'jobseeker') {
    $job_id = intval($_GET['apply']);
    
    // Fetch job and employer details
    $j_stmt = $conn->prepare("SELECT j.title, u.email AS employer_email, u.fullname AS employer_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ?");
    $j_stmt->bind_param("i", $job_id);
    $j_stmt->execute();
    $job_info = $j_stmt->get_result()->fetch_assoc();

    // Fetch applicant details
    $u_stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
    $u_stmt->bind_param("i", $user_id);
    $u_stmt->execute();
    $applicant_info = $u_stmt->get_result()->fetch_assoc();

    if ($job_info && $applicant_info) {
        $chk = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND jobseeker_id = ?");
        $chk->bind_param("ii", $job_id, $user_id);
        $chk->execute();
        $chk->store_result();
        
        if ($chk->num_rows === 0) {
            $ins = $conn->prepare("INSERT INTO applications (job_id, jobseeker_id) VALUES (?, ?)");
            $ins->bind_param("ii", $job_id, $user_id);
            $ins->execute();

            // Send Application Email to Employer
            $emp_subject = "New Job Application Received: " . $job_info['title'];
            $emp_message = "Hello " . $job_info['employer_name'] . ",\n\nYou have received a new application for your job posting: " . $job_info['title'] . ".\n\nApplicant Name: " . $applicant_info['fullname'] . "\nApplicant Email: " . $applicant_info['email'] . "\n\nLog in to your dashboard to review.";
            @mail($job_info['employer_email'], $emp_subject, $emp_message);

            // Send Acknowledgement Email to Employee / Jobseeker
            $seeker_subject = "Application Confirmation: " . $job_info['title'];
            $seeker_message = "Hello " . $applicant_info['fullname'] . ",\n\nYour application for '" . $job_info['title'] . "' has been successfully submitted to " . $job_info['employer_name'] . ".\n\nThank you for using CareerConnect!";
            @mail($applicant_info['email'], $seeker_subject, $seeker_message);
        }
    }
    header("Location: dashboard.php?applied=success");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?> (<?php echo ucfirst($role); ?>)</h2>
    
    <div style="margin: 15px 0;">
        <a href="directory.php" class="btn" style="margin-right: 10px;">View User Directory</a>
        <a href="reports.php" class="btn" style="margin-right: 10px;">SQL Reports</a>
        <a href="settings.php" class="btn">Account Settings</a>
    </div>

    <?php if (!empty($msg) || isset($_GET['msg'])): ?>
        <p style="color: green; font-weight: bold;"><?php echo $msg ?: "Action completed successfully!"; ?></p>
    <?php endif; ?>

    <!-- ================= ADMIN PANEL ================= -->
    <?php if ($role === 'admin'): ?>
        <hr>
        <h3>Admin Management Panel</h3>

        <!-- Pending Email Approvals -->
        <h4>Pending Email Change Requests</h4>
        <?php
        $pendings = $conn->query("SELECT id, fullname, email, pending_email FROM users WHERE pending_email IS NOT NULL");
        ?>
        <?php if ($pendings->num_rows > 0): ?>
            <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                <tr style="background:#f2f2f2;"><th>Name</th><th>Current Email</th><th>Requested New Email</th><th>Action</th></tr>
                <?php while($p = $pendings->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                    <td><strong><?php echo htmlspecialchars($p['pending_email']); ?></strong></td>
                    <td><a href="dashboard.php?approve_email=<?php echo $p['id']; ?>" style="color:green; font-weight:bold;">Approve Email</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No pending email change requests.</p>
        <?php endif; ?>

        <!-- Create User Form -->
        <h4>Create New User Account</h4>
        <form method="POST" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <input type="text" name="fullname" placeholder="Full Name" required style="margin-right:10px; padding:6px;">
            <input type="email" name="email" placeholder="Email Address" required style="margin-right:10px; padding:6px;">
            <input type="password" name="password" placeholder="Password" required style="margin-right:10px; padding:6px;">
            <select name="new_role" style="padding:6px; margin-right:10px;">
                <option value="jobseeker">Jobseeker</option>
                <option value="employer">Employer</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="create_user">Create User</button>
        </form>

        <!-- Manage Subscriptions Form -->
        <h4>Assign Subscription Fee</h4>
        <form method="POST" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:20px;">
            <select name="user_id" required style="padding:6px; margin-right:10px;">
                <option value="">Select User</option>
                <?php 
                $usrs = $conn->query("SELECT id, fullname, email, role FROM users");
                while($u = $usrs->fetch_assoc()):
                ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['fullname']) . ' (' . ucfirst($u['role']) . ')'; ?></option>
                <?php endwhile; ?>
            </select>
            <input type="number" step="0.01" name="amount" placeholder="Amount (e.g. 50.00)" required style="padding:6px; margin-right:10px;">
            <button type="submit" name="add_subscription">Add Subscription Fee</button>
        </form>

        <!-- All Users Table -->
        <h4>All Users & Account Management</h4>
        <?php
        $all_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
        ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr style="background:#f2f2f2;"><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
            <?php while($usr = $all_users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $usr['id']; ?></td>
                <td><?php echo htmlspecialchars($usr['fullname']); ?></td>
                <td><?php echo htmlspecialchars($usr['email']); ?></td>
                <td><?php echo ucfirst($usr['role']); ?></td>
                <td>
                    <a href="dashboard.php?delete_user=<?php echo $usr['id']; ?>" onclick="return confirm('Delete this user?');" style="color:red;">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Subscription Records -->
        <h4>Subscription Records</h4>
        <?php
        $subs = $conn->query("SELECT s.*, u.fullname, u.email FROM subscriptions s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC");
        ?>
        <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
            <tr style="background:#f2f2f2;"><th>User</th><th>Email</th><th>Amount</th><th>Status</th><th>Date</th></tr>
            <?php while($sub = $subs->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['fullname']); ?></td>
                <td><?php echo htmlspecialchars($sub['email']); ?></td>
                <td>$<?php echo number_format($sub['amount'], 2); ?></td>
                <td><?php echo ucfirst($sub['status']); ?></td>
                <td><?php echo $sub['created_at']; ?></td>
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
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <p style="margin-top: 30px;"><a href="logout.php">Logout</a></p>
</div>

<?php 
ob_end_flush(); 
?>
