<?php
include 'db.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<h2>System Analytics & Reports</h2>

<h3>Report 1: Total Platform Summary</h3>
<?php
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$jobs_count  = $conn->query("SELECT COUNT(*) as total FROM jobs")->fetch_assoc()['total'];
$apps_count  = $conn->query("SELECT COUNT(*) as total FROM applications")->fetch_assoc()['total'];
?>
<table>
    <tr><th>Metric</th><th>Total Count</th></tr>
    <tr><td>Registered Users</td><td><?= $users_count; ?></td></tr>
    <tr><td>Active Job Postings</td><td><?= $jobs_count; ?></td></tr>
    <tr><td>Applications Submitted</td><td><?= $apps_count; ?></td></tr>
</table>

<h3>Report 2: Application Count per Job Posting</h3>
<?php
$report2 = $conn->query("SELECT j.title, COUNT(a.id) AS total_apps FROM jobs j LEFT JOIN applications a ON j.id = a.job_id GROUP BY j.id");
?>
<table>
    <tr><th>Job Title</th><th>Total Applications</th></tr>
    <?php while($r = $report2->fetch_assoc()): ?>
        <tr><td><?= htmlspecialchars($r['title']); ?></td><td><?= $r['total_apps']; ?></td></tr>
    <?php endwhile; ?>
</table>

<h3>Report 3: Active Job Seekers & Skills Directory</h3>
<?php
$report3 = $conn->query("SELECT u.fullname, u.email, p.title, p.skills FROM users u JOIN profiles p ON u.id = p.user_id WHERE u.role = 'jobseeker'");
?>
<table>
    <tr><th>Candidate Name</th><th>Email</th><th>Headline</th><th>Skills</th></tr>
    <?php while($r = $report3->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($r['fullname']); ?></td>
            <td><?= htmlspecialchars($r['email']); ?></td>
            <td><?= htmlspecialchars($r['title']); ?></td>
            <td><?= htmlspecialchars($r['skills']); ?></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
