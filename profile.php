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

$user_id =$_SESSION['user_id'];
$role =$_SESSION['role'];
$msg = '';$error = '';

// Fetch existing profile data
$stmt =$conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);$stmt->execute();
$profile =$stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $skills = trim($_POST['skills']);
    $courses = trim($_POST['courses']);
    $bio = trim($_POST['bio']);
    $profile_pic =$profile['profile_pic'] ?? 'default_avatar.png';

    // Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) &&$_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath =$_FILES['profile_pic']['tmp_name'];
        $fileName =$_FILES['profile_pic']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {$uploadFileDir = './uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $newFileName = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;
            $dest_path = $uploadFileDir .$newFileName;

            if (move_uploaded_file($fileTmpPath,$dest_path)) {
                $profile_pic =$newFileName;
            } else {
                $error = "Error moving uploaded image file.";
            }
        } else {
            $error = "Invalid image type. Only JPG, PNG, and WEBP files are allowed.";
        }
    }

    if (empty($error)) {
        if ($profile) {
            // Update existing profile
            $upd =$conn->prepare("UPDATE profiles SET title = ?, skills = ?, courses = ?, bio = ?, profile_pic = ? WHERE user_id = ?");
            $upd->bind_param("sssssi", $title, $skills,$courses, $bio,$profile_pic, $user_id);$upd->execute();
        } else {
            // Insert new profile
            $ins =$conn->prepare("INSERT INTO profiles (user_id, title, skills, courses, bio, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
            $ins->bind_param("isssss", $user_id, $title,$skills, $courses,$bio, $profile_pic);$ins->execute();
        }
        $msg = "Profile updated successfully!";

        // Refresh stored profile variable
        $stmt->execute();
        $profile =$stmt->get_result()->fetch_assoc();
    }
}
?>
<?php include 'header.php'; ?>

<div class="container" style="max-width: 650px;">
    <h2>Edit Profile (<?php echo ucfirst($role); ?>)</h2>

    <?php if (!empty($msg)): ?><p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
    <?php if (!empty($error)): ?><p style="color: red; font-weight: bold;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
        <!-- Current Profile Picture Preview -->
        <div style="text-align: center; margin-bottom: 20px;">
            <?php 
                $pic_path = !empty($profile['profile_pic']) && file_exists('./uploads/' .$profile['profile_pic']) 
                    ? './uploads/' . htmlspecialchars($profile['profile_pic']) 
                    : 'https://via.placeholder.com/120?text=No+Pic';
            ?>
            <img src="<?php echo $pic_path; ?>" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #007bff;">
            <div style="margin-top: 10px;">
                <label><strong>Profile Picture:</strong></label><br>
                <input type="file" name="profile_pic" accept="image/*" style="margin-top: 5px;">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Professional Title / Company Tagline:</strong></label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($profile['title'] ?? ''); ?>" placeholder="<?php echo $role === 'employer' ? 'e.g. Lead Tech Recruiter at Acme' : 'e.g. Full-Stack Web Developer'; ?>" style="width:100%; padding:8px; margin-top:5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Key Skills / Core Competencies:</strong></label>
            <input type="text" name="skills" value="<?php echo htmlspecialchars($profile['skills'] ?? ''); ?>" placeholder="e.g. PHP, MySQL, Web Security, Project Management" style="width:100%; padding:8px; margin-top:5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Completed Courses & Certifications:</strong></label>
            <textarea name="courses" rows="3" placeholder="e.g. BSc Computer Science, AWS Certified Developer, Cisco CCNA" style="width:100%; padding:8px; margin-top:5px;"><?php echo htmlspecialchars($profile['courses'] ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom: 15px;">
            <label><strong>Biography / Overview:</strong></label>
            <textarea name="bio" rows="4" placeholder="Brief summary about yourself or your organization..." style="width:100%; padding:8px; margin
