<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// EMPLOYER: Post new job
if ($role === 'employer' && isset($_POST['post_job'])) {
    $title = trim($_POST['title']);
    $location = trim($_POST['location']);
    $desc = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, location, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $location, $desc);
    $stmt->execute();
}

// JOBSEEKER: Apply for job
if ($role === 'jobseeker' && isset($_GET['apply'])) {
    $job_id = intval($_GET['apply']);
    $stmt = $conn->prepare("INSERT INTO applications (job_id, jobseeker_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $job_id, $user_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}
?>

<h2>Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?>!</h2>

<?php if ($role === 'employer'): ?>
    <h3>Post a New Job Listing</h3>
    <form method="POST">
        <input type="hidden" name="post_job" value="1">
        <label>Job Title:</label>
        <input type="text" name="title" required>
        <label>Location:</label>
        <input type="text" name="location" required>
        <label>Description:</label>
        <textarea name="description" required></textarea>
        <button type="submit">Publish Job</button>
    </form>

    <h3>Your Job Listings & Applicants</h3>
    <?php
    $sql = "SELECT j.title, u.fullname AS candidate, u.email, p.skills, a.applied_at 
            FROM jobs j 
            LEFT JOIN applications a ON j.id = a.job_id 
            LEFT JOIN users u ON a.jobseeker_id = u.id 
            LEFT JOIN profiles p ON u.id = p.user_id 
            WHERE j.employer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    ?>
    <table>
        <tr><th>Job Title</th><th>Candidate</th><th>Email</th><th>Skills</th><th>Applied Date</th></tr>
        <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td><?= htmlspecialchars($row['candidate'] ?: 'No Applicants Yet'); ?></td>
                <td><?= htmlspecialchars($row['email'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($row['skills'] ?: '-'); ?></td>
                <td><?= htmlspecialchars($row['applied_at'] ?: '-'); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

<?php else: ?>
    <h3>Available Opportunities</h3>
    <?php
    $res = $conn->query("SELECT j.*, u.fullname AS company FROM jobs j JOIN users u ON j.employer_id = u.id ORDER BY created_at DESC");
    ?>
    <div class="card-grid">
        <?php while($job = $res->fetch_assoc()): ?>
            <div class="card">
                <h4><?= htmlspecialchars($job['title']); ?></h4>
                <p><strong>Company:</strong> <?= htmlspecialchars($job['company']); ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($job['location']); ?></p>
                <p><?= htmlspecialchars($job['description']); ?></p>
                <a href="dashboard.php?apply=<?= $job['id']; ?>" class="btn">Apply Now</a>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>
</body>
</html>
