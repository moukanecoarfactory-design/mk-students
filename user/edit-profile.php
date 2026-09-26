<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

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
    $lastName  = trim($_POST['last_name'] ?? "");
    $phone     = trim($_POST['phone'] ?? "");
    $studyLevel = $_POST['study_level'] ?? "";
    $bio       = trim($_POST['bio'] ?? "");

    if (empty($firstName) || empty($lastName) || empty($studyLevel)) {
        header("Location: edit-profile.php?error=empty_fields");
        exit();
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 5 * 1024 * 1024;
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
    <title>Edit Profile - MK Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 15%, rgba(102, 126, 234, 0.35), transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(118, 75, 162, 0.4), transparent 45%),
                linear-gradient(135deg, #1e2340 0%, #2d1b4e 50%, #1e2340 100%);
            background-attachment: fixed;
        }

        /* ============ NAVBAR ============ */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 40px;
            background: rgba(20, 25, 55, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .navbar .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
            font-weight: 800;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.3px;
        }
        .navbar .brand i {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.45);
        }
        .navbar .right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .navbar .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            color: #fff;
            font-size: 13.5px;
            font-weight: 600;
        }
        .navbar .user-pill i { color: #a8b8ff; font-size: 14px; }

        .navbar .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ff9b9b;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            border-radius: 10px;
            transition: 0.22s;
        }
        .navbar .logout-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #ffb3b3;
            transform: translateY(-2px);
        }

        /* ============ PAGE ============ */
        .page-wrap {
    max-width: 860px;
    margin: 30px auto 50px;
    padding: 0 20px;
}

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a8b8ff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 22px;
            transition: 0.2s;
        }
        .back-link:hover { color: #fff; transform: translateX(-3px); }

        /* Card */
        .edit-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: fadeUp 0.7s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Card header with gradient */
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 34px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .card-header .h-icon {
            width: 48px; height: 48px;
            border-radius: 13px;
            background: rgba(255, 255, 255, 0.2);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            backdrop-filter: blur(10px);
        }
        .card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .card-header p {
            font-size: 13px;
            opacity: 0.9;
            margin: 2px 0 0;
        }

        /* Card body */
        .card-body {
    padding: 26px 34px 30px;
}

        /* ============ AVATAR UPLOAD ============ */
        .avatar-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 26px;
}
        .avatar-wrap {
    position: relative;
    width: 120px;
    height: 120px;
}
        .avatar-wrap::before {
            content: "";
            position: absolute;
            inset: -10px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #667eea, #a78bfa, #764ba2, #667eea);
            animation: spin 8s linear infinite;
            filter: blur(3px);
            opacity: 0.7;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .avatar-wrap img {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
            background: #f0f2f8;
        }

        .avatar-overlay {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            border: 3px solid #fff;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
            transition: 0.22s;
            z-index: 2;
        }
        .avatar-overlay:hover {
            transform: scale(1.12);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.7);
        }

        .photo-hint {
            margin-top: 16px;
            font-size: 13px;
            color: #888;
            font-weight: 500;
        }

        /* ============ FORM ============ */
        .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
        .form-field.full { grid-column: span 2; }

        .form-field label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #555;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .form-field input,
        .form-field select,
        .form-field textarea {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            font-size: 14.5px;
            font-family: inherit;
            background: #f8faff;
            color: #27324d;
            transition: 0.22s;
            outline: none;
            resize: vertical;
        }
        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }
        .form-field textarea {
    min-height: 85px;
    line-height: 1.55;
}

        /* ============ ACTIONS ============ */
        .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1px solid #eef1f8;
}
        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: 0.25s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 9px;
        }
        .btn-outline {
            background: #fff;
            border: 1.5px solid #e1e5eb;
            color: #555;
        }
        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.35);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(102, 126, 234, 0.5);
        }

        /* ============ FOOTER ============ */
        .footer {
            text-align: center;
            padding: 30px 20px;
            color: rgba(255, 255, 255, 0.45);
            font-size: 13px;
        }
        .footer span { color: #a8b8ff; font-weight: 700; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 700px) {
            .navbar { padding: 12px 20px; }
            .navbar .brand { font-size: 16px; }
            .navbar .user-pill { display: none; }
            .card-body { padding: 26px 22px 30px; }
            .card-header { padding: 22px 24px; }
            .form-grid { grid-template-columns: 1fr; }
            .form-field.full { grid-column: span 1; }
            .form-actions { flex-direction: column-reverse; }
            .btn { width: 100%; justify-content: center; }
            .avatar-wrap { width: 120px; height: 120px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="dashboard.php" class="brand">
        <i class="fa-solid fa-graduation-cap"></i> StudentHub
    </a>
    <div class="right">
        <span class="user-pill">
            <i class="fa-solid fa-circle-user"></i>
            <?php echo htmlspecialchars($_SESSION['first_name']); ?>
        </span>
        <a href="../logout.php" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<!-- PAGE -->
<main class="page-wrap">

    <a href="dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="edit-card">

        <!-- Card header -->
        <div class="card-header">
            <div class="h-icon"><i class="fa-solid fa-user-pen"></i></div>
            <div>
                <h1>Edit Profile</h1>
                <p>Update your personal information and profile picture</p>
            </div>
        </div>

        <!-- Card body -->
        <div class="card-body">

            <form action="edit-profile.php" method="POST" enctype="multipart/form-data">

                <!-- Avatar -->
                <div class="avatar-zone">
                    <div class="avatar-wrap">
                        <img src="../uploads/profiles/<?php echo htmlspecialchars($profilePicture); ?>"
                             id="imagePreview"
                             alt="Profile">
                        <label for="profile_picture" class="avatar-overlay" title="Change photo">
                            <i class="fa-solid fa-camera"></i>
                        </label>
                    </div>
                    <input type="file" name="profile_picture" id="profile_picture"
                           accept=".jpg,.jpeg,.png,.webp" hidden>
                    <p class="photo-hint">📷 Click the camera icon to change your photo</p>
                </div>

                <!-- Fields -->
                <div class="form-grid">

                    <div class="form-field">
                        <label>First Name *</label>
                        <input type="text" name="first_name"
                               value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>

                    <div class="form-field">
                        <label>Last Name *</label>
                        <input type="text" name="last_name"
                               value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>

                    <div class="form-field">
                        <label>Phone Number</label>
                        <input type="text" name="phone"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                               placeholder="Not set">
                    </div>

                    <div class="form-field">
                        <label>Study Level *</label>
                        <select name="study_level" required>
                            <option value="" disabled <?php echo empty($user['study_level']) ? 'selected' : ''; ?>>
                                Select study level
                            </option>
                            <?php
                            $levels = ["Bac", "Bac+1", "Bac+2", "Bac+3", "Bac+4", "Bac+5", "Doctorat"];
                            foreach ($levels as $level) {
                                $selected = ($user['study_level'] === $level) ? "selected" : "";
                                echo "<option value=\"$level\" $selected>$level</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-field full">
                        <label>Bio</label>
                        <textarea name="bio" rows="5"
                                  placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ""); ?></textarea>
                    </div>

                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>

            </form>

        </div>

    </div>

</main>

<footer class="footer">
    © <?php echo date('Y'); ?> <span>MK Students</span> · All rights reserved
</footer>

<script>
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