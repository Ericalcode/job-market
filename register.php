<?php
include 'db.php';
include 'header.php';

$role = isset($_GET['role']) && in_array($_GET['role'], ['jobseeker', 'employer']) ? $_GET['role'] : 'jobseeker';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $selected_role = $_POST['role'];
    $skills   = trim($_POST['skills']);
    $title    = trim($_POST['title']);

    if (!empty($fullname) && !empty($email) && !empty($_POST['password'])) {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $password, $selected_role);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            // Create corresponding profile record
            $stmt_p = $conn->prepare("INSERT INTO profiles (user_id, title, skills) VALUES (?, ?, ?)");
            $stmt_p->bind_param("iss", $user_id, $title, $skills);
            $stmt_p->execute();

            header("Location: login.php?msg=registered");
            exit;
        } else {
            $error = "Email already exists!";
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<h2>Register as <?= ucfirst($role); ?></h2>
<?php if($error): ?><p class="error"><?= $error; ?></p><?php endif; ?>

<form method="POST" onsubmit="return validateForm();">
    <input type="hidden" name="role" value="<?= htmlspecialchars($role); ?>">
    
    <label>Full Name:</label>
    <input type="text" name="fullname" id="fullname" required>

    <label>Email Address:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" id="password" required>

    <label><?= $role === 'jobseeker' ? 'Professional Title / Headline:' : 'Company / Organization Name:'; ?></label>
    <input type="text" name="title" required>

    <label><?= $role === 'jobseeker' ? 'Skills (comma separated):' : 'Industry Focus:'; ?></label>
    <textarea name="skills" required></textarea>

    <button type="submit">Create Account</button>
</form>

<script>
function validateForm() {
    let pass = document.getElementById('password').value;
    if(pass.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
