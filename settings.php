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
$error = '';

// Fetch existing profile data
$p_stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$p_stmt->bind_param("i", $user_id);
$p_stmt->execute();
$profile = $p_stmt->get_result()->fetch_assoc() ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Profile Details Update
    if (isset($_POST['update_profile'])) {
        $title = trim($_POST['title']);
        $skills = trim($_POST['skills']);
        $courses = trim($_POST['courses']);
        $bio = trim($_POST['bio']);
        $profile_pic = $profile['profile_pic'] ?? null;

        // Handle Profile Picture Upload
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
            $fileName = $_FILES['profile_pic']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadFileDir = './uploads/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $newFileName = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_pic = $dest_path;
                } else {
                    $error = "Error uploading profile picture.";
                }
            } else {
                $error = "Invalid image file type. Allowed: JPG, PNG, GIF, WEBP.";
            }
        }

        if (empty($error)) {
            // Upsert profile record
            if ($profile) {
                $upd = $conn->prepare("UPDATE profiles SET title = ?, skills = ?, courses = ?, bio = ?, profile_pic = ? WHERE user_id = ?");
                $upd->bind_param("sssssi", $title, $skills, $courses, $bio, $profile_pic, $user_id);
                $upd->execute();
            } else {
                $ins = $conn->prepare("INSERT INTO profiles (user_id, title, skills, courses, bio, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->bind_param("isssss", $user_id, $title, $skills, $courses, $bio, $profile_pic);
                $ins->execute();
            }
            $msg = "Profile updated successfully!";
            
            // Refresh profile data
            $p_stmt->execute();
            $profile = $p_stmt->get_result()->fetch_assoc();
        }
    }

    // 2. Password Change
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && password_verify($old_pass, $res['password'])) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $hashed, $user_id);
            $upd->execute();
            $msg = "Password updated successfully!";
        } else {
            $error = "Incorrect current password.";
        }
    }

    // 3. Email Change Request
    if (isset($_POST['request_email'])) {
        $new_email = trim($_POST['new_email']);
        
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ? OR pending_email = ?");
        $chk->bind_param("ss", $new_email, $new_email);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error = "This email address is already in use or pending approval.";
        } else {
            $req = $conn->prepare("UPDATE users SET pending_email = ? WHERE id = ?");
            $req->bind_param("si", $new_email, $user_id);
            $req->execute();
            $msg = "Email change request submitted! Waiting for Admin approval.";
        }
    }
}
?>
<?php include 'header.php'; ?>

<div class="container" style="max-width: 650px;">
    <h2>Account Settings & Profile Management</h2>
    
    <?php if (!empty($msg)): ?><p style="color: green; font-weight: bold;"><?php echo $msg; ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p style="color: red; font-weight: bold;"><?php echo $error; ?></p><?php endif; ?>

    <hr style="margin: 20px 0;">

    <!-- PROFILE CUSTOMIZATION -->
    <h3>Edit Profile</h3>
    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 30px; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
        <?php if (!empty($profile['profile_pic'])): ?>
            <div style="margin-bottom: 15px;">
                <img src="<?php echo htmlspecialchars($profile['profile_pic']); ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #007bff;">
            </div>
        <?php endif; ?>

        <label><strong>Profile Picture:</strong></label>
        <input type="file" name="profile_pic" accept="image/*" style="display:block; width:100%; margin-bottom:12px;">

        <label><strong>Professional Title / Company Tagline:</strong></label>
        <input type="text" name="title" placeholder="<?php echo $role === 'employer' ? 'e.g. HR Manager / Tech Recruiter' : 'e.g. Full-Stack Developer'; ?>" value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>" style="display:block; width:100%; margin-bottom:12px; padding:8px;">

        <label><strong>Skills / Specializations:</strong></label>
        <input type="text" name="skills" placeholder="e.g. PHP, MySQL, Web Development, Team Management" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" style="display:block; width:100%; margin-bottom:12px; padding:8px;">

        <label><strong>Courses / Certifications / Degrees:</strong></label>
        <textarea name="courses" placeholder="e.g. B.Sc. Information Technology, AWS Certified Developer, Cisco CCNA" style="display:block; width:100%; margin-bottom:12px; padding:8px; height:70px;"><?php echo htmlspecialchars($profile['courses'] ?? ''); ?></textarea>

        <label><strong>Bio / Company Overview:</strong></label>
        <textarea name="bio" placeholder="Write a short summary about yourself or your company..." style="display:block; width:100%; margin-bottom:12px; padding:8px; height:90px;"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>

        <button type="submit" name="update_profile" class="btn" style="background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px;">Save Profile Changes</button>
    </form>

    <hr style="margin: 20px 0;">

    <!-- SECURITY SETTINGS -->
    <h3>Change Password</h3>
    <form method="POST" style="margin-bottom: 30px;">
        <input type="password" name="old_password" placeholder="Current Password" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <input type="password" name="new_password" placeholder="New Password" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <button type="submit" name="change_password">Update Password</button>
    </form>

    <h3>Request Email Change</h3>
    <p><small>Note: Email updates require approval from the platform Administrator.</small></p>
    <form method="POST">
        <input type="email" name="new_email" placeholder="New Email Address" required style="display:block; width:100%; margin-bottom:10px; padding:8px;">
        <button type="submit" name="request_email">Request Email Change</button>
    </form>

    <p style="margin-top: 30px;"><a href="dashboard.php" class="btn">&larr; Back to Dashboard</a></p>
</div>

<?php ob_end_flush(); ?>
