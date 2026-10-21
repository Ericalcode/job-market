```php
<?php
include 'db.php';
include 'header.php';

$role = isset($_GET['role']) && in_array($_GET['role'], ['jobseeker', 'employer'])
    ? $_GET['role']
    : 'jobseeker';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $raw_password = $_POST['password'] ?? '';
    $selected_role = $_POST['role'] ?? 'jobseeker';
    $skills   = trim($_POST['skills'] ?? '');
    $title    = trim($_POST['title'] ?? '');

    // Validate the selected role
    if (!in_array($selected_role, ['jobseeker', 'employer'])) {
        $error = "Invalid account role.";
    } elseif (
        !empty($fullname) &&
        !empty($email) &&
        !empty($raw_password) &&
        !empty($title) &&
        !empty($skills)
    ) {

        if (strlen($raw_password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {

            $password = password_hash(
                $raw_password,
                PASSWORD_DEFAULT
            );

            try {
                // Insert user account
                $stmt = $conn->prepare(
                    "INSERT INTO users (fullname, email, password, role)
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssss",
                    $fullname,
                    $email,
                    $password,
                    $selected_role
                );

                $stmt->execute();

                $user_id = $conn->insert_id;

                // Create the corresponding profile
                $stmt_p = $conn->prepare(
                    "INSERT INTO profiles (user_id, title, skills)
                     VALUES (?, ?, ?)"
                );

                $stmt_p->bind_param(
                    "iss",
                    $user_id,
                    $title,
                    $skills
                );

                $stmt_p->execute();

                // Redirect after successful registration
                header("Location: login.php?msg=registered");
                exit();

            } catch (mysqli_sql_exception $e) {

                if ($e->getCode() == 1062) {
                    $error = "That email address is already registered. Please use another one or log in.";
                } else {
                    // Log technical details instead of exposing them
                    error_log($e->getMessage());
                    $error = "An error occurred during registration. Please try again.";
                }
            }
        }

    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<h2>Register as <?= htmlspecialchars(ucfirst($role)); ?></h2>

<?php if ($error): ?>
    <p class="error">
        <?= htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<form method="POST" onsubmit="return validateForm();">

    <input
        type="hidden"
        name="role"
        value="<?= htmlspecialchars($role); ?>"
    >

    <label>Full Name:</label>
    <input type="text" name="fullname" id="fullname" required>

    <label>Email Address:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" id="password" required>

    <label>
        <?= $role === 'jobseeker'
            ? 'Professional Title / Headline:'
            : 'Company / Organization Name:'; ?>
    </label>
    <input type="text" name="title" required>

    <label>
        <?= $role === 'jobseeker'
            ? 'Skills (comma separated):'
            : 'Industry Focus:'; ?>
    </label>
    <textarea name="skills" required></textarea>

    <button type="submit">Create Account</button>

</form>

<script>
function validateForm() {
    let pass = document.getElementById('password').value;

    if (pass.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
```
