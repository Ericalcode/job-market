<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$error = '';
$requested_role = $_GET['role'] ?? 'jobseeker';

// Security check for admin registration
if ($requested_role === 'admin') {
    $secret_key = $_GET['key'] ?? '';
    // Define your secret key here
    if ($secret_key !== 'admin_secret_2026') { 
        header("Location: login.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'jobseeker';

    // Prevent unauthorized post tampering of roles
    if ($role === 'admin' && ($_GET['key'] ?? '') !== 'admin_secret_2026') {
        $role = 'jobseeker';
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "That email address is already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $password, $role);
        
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Register <?php echo ucfirst(htmlspecialchars($requested_role)); ?></h2>
    
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($requested_role); ?>">
        
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        
        <button type="submit">Create <?php echo ucfirst(htmlspecialchars($requested_role)); ?> Account</button>
    </form>
</div>

<?php ob_end_flush(); ?>
