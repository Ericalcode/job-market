<?php
// 1. Output buffering and session handling at the very top
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
?>
<?php include 'header.php'; ?>

<div class="container">
    <h2>System Reports & Analytics</h2>
    <p>Academic SQL Reporting Dashboard for Job & Talent Matching Platform</p>
    <hr style="margin: 20px 0;">

    <!-- Report 1: Job Application Statistics (SQL Aggregation) -->
    <h3>1. Job Applications Summary (Applications per Job)</h3>
    <?php
    $sql_report1 = "SELECT j.id, j.title, j.location, u.fullname AS employer_name, 
                           COUNT(a.id) AS total_applications 
                    FROM jobs j 
                    JOIN users u ON j.employer_id = u.id 
                    LEFT JOIN applications a ON j.id = a.job_id 
                    GROUP BY j.id, j.title, j.location, u.fullname 
                    ORDER BY total_applications DESC";
    $result1 = $conn->query($sql_report1);
    ?>
    
    <?php if ($result1 && $result1->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>Job ID</th>
                    <th>Job Title</th>
                    <th>Location</th>
                    <th>Employer</th>
                    <th>Total Applicants</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result1->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['location']); ?></td>
                    <td><?php echo htmlspecialchars($row['employer_name']); ?></td>
                    <td><strong><?php echo $row['total_applications']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No job postings or applications found yet.</p>
    <?php endif; ?>

    <!-- Report 2: Platform User Summary -->
    <h3>2. User Breakdown by Role</h3>
    <?php
    $sql_report2 = "SELECT role, COUNT(id) AS total_users FROM users GROUP BY role";
    $result2 = $conn->query($sql_report2);
    ?>
    
    <?php if ($result2 && $result2->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0" style="width:50%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th>Role</th>
                    <th>Total Users</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result2->fetch_assoc()): ?>
                <tr>
                    <td><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                    <td><strong><?php echo $row['total_users']; ?></strong></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="dashboard.php" class="btn">&larr; Back to Dashboard</a></p>
</div>

<?php 
ob_end_flush(); 
?>
