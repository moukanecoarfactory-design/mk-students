<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$profilePicture = (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) 
    ? $user['profile_picture'] 
    : "default-avatar.png";

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstName = trim($_POST['first_name'] ?? "");
    $lastName = trim($_POST['last_name'] ?? "");
    $phone = trim($_POST['phone'] ?? "");
    $studyLevel = $_POST['study_level'] ?? "";
    $bio = trim($_POST['bio'] ?? "");

    if (empty($firstName) || empty($lastName) || empty($studyLevel)) {
        header("Location: edit-profile.php?error=empty_fields");
        exit();
    }

    // Handle Image Upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['profile_picture']['size'] > $maxSize) {
            die("The image must be smaller than 5 MB.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['profile_picture']['tmp_name']);
        $allowedTypes = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];

        if (!array_key_exists($mimeType, $allowedTypes)) {
            die("Only JPG, PNG and WEBP images are allowed.");
        }

        $newFileName = "user_" . $userId . "_" . time() . "." . $allowedTypes[$mimeType];
        $uploadPath = "../uploads/profiles/" . $newFileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadPath)) {
            if ($profilePicture !== 'default-avatar.png' && file_exists("../uploads/profiles/" . $profilePicture)) {
                unlink("../uploads/profiles/" . $profilePicture);
            }
            $profilePicture = $newFileName;
        }
    }

    // Update User Info
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, study_level=?, bio=?, profile_picture=? WHERE id=?");
    $stmt->bind_param("ssssssi", $firstName, $lastName, $phone, $studyLevel, $bio, $profilePicture, $userId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['first_name'] = $firstName; 
    header("Location: dashboard.php?success=profile_updated");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - mk-students</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="dashboard-body">

<!-- TOP NAVBAR -->
<nav class="dashboard-navbar">
    <a href="dashboard.php" class="navbar-brand">
        <i class="fa-solid fa-graduation-cap"></i> StudentHub
    </a>
    <div class="navbar-right">
        <span class="navbar-user">
            <i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($_SESSION['first_name']); ?>
        </span>
        <a href="../logout.php" class="navbar-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

        <div class="dash-card">
            <div class="card-top">
                <h2><i class="fa-solid fa-user-pen"></i> Edit Profile</h2>
            </div>

            <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
                
                <!-- Profile Picture Upload Area -->
                <div class="edit-profile-photo-section">
                    <div class="edit-profile-photo-wrapper">
                        <img src="../uploads/profiles/<?php echo htmlspecialchars($profilePicture); ?>" class="edit-profile-photo" id="imagePreview">
                        <label for="profile_picture" class="edit-photo-overlay">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" name="profile_picture" id="profile_picture" accept=".jpg,.jpeg,.png,.webp" hidden>
                    <p class="photo-hint">Click the camera to change your photo</p>
                </div>

                <!-- Form Grid -->
                <div class="edit-form-grid">
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Study Level *</label>
                        <select name="study_level" required>
                            <option value="" disabled selected>Select study level</option>
                            <?php
                            $levels = ["Bac", "Bac+2", "Bac+3", "Bac+5", "Other"];
                            foreach ($levels as $level) {
                                $selected = ($user['study_level'] === $level) ? "selected" : "";
                                echo "<option value='$level' $selected>$level</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Bio</label>
                        <textarea name="bio" rows="5" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ""); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- FOOTER -->
<footer class="dashboard-footer">
    <p>&copy; <?php echo date('Y'); ?> Mk.Students. All rights reserved.</p>
</footer>

<script>
    // Preview Profile Picture on upload
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('imagePreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

</body>
</html>