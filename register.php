<?php
// 1. Output buffering and database logic first (BEFORE any HTML output)
ob_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    // Grab role from form or URL parameter safely
    $role = $_POST['role'] ?? ($_GET['role'] ?? 'jobseeker');

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "That email address is already registered. Please login instead.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $password, $role);
        
        if ($stmt->execute()) {
            // 2. Redirect BEFORE including header.php or printing any HTML
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<!-- 3. Now it is safe to include header.php and render the HTML form -->
<?php include 'header.php'; ?>

<div class="container">
    <h2>Register (<?php echo htmlspecialchars(ucfirst($_GET['role'] ?? 'Jobseeker')); ?>)</h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="register.php?role=<?php echo htmlspecialchars($_GET['role'] ?? 'jobseeker'); ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($_GET['role'] ?? 'jobseeker'); ?>">
        
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit">Create Account</button>
    </form>
</div>

<?php 
ob_end_flush(); 
?>
