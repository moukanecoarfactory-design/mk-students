<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

// Get user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$profilePicture = (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) 
    ? "../uploads/profiles/" . $user['profile_picture'] 
    : "../uploads/profiles/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - mk-students</title>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="dashboard-body">

<!-- TOP NAVBAR -->
<nav class="dashboard-navbar">
    <a href="dashboard.php" class="navbar-brand">
        <i class="fa-solid fa-graduation-cap"></i> MK-STUDENTS
    </a>
    <div class="navbar-right">
        <span class="navbar-user">
            <i class="fa-solid fa-circle-user"></i> <?php echo htmlspecialchars($user['first_name']); ?>
        </span>
        <a href="../logout.php" class="navbar-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        
        <!-- BACK LINK -->
        <a href="dashboard.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>

        <!-- PROFILE CARD -->
        <div class="dash-card profile-page-card">
            
            <!-- PROFILE HEADER SECTION -->
            <div class="profile-header-section">
                <div class="profile-image-container">
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" class="profile-image profile-image-large">
                </div>
                <div class="profile-header-info">
                    <h1><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h1>
                    <p class="profile-email"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="profile-quick-info">
                        <div class="quick-badge">
                            <i class="fa-solid fa-book"></i> <?php echo htmlspecialchars($user['study_level'] ?? 'Not set'); ?>
                        </div>
                        <div class="quick-badge">
                            <i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PROFILE BODY SECTION -->
            <div class="profile-body-section">
                <div class="about-section">
                    <h3><i class="fa-solid fa-user"></i> About Me</h3>
                    <p><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "No bio added yet."; ?></p>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="profile-actions">
                    <a href="edit-profile.php" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i> Edit Profile</a>
                    <a href="dashboard.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="dashboard-footer">
    <p>&copy; <?php echo date('Y'); ?> Mk.Students. All rights reserved.</p>
</footer>

</body>
</html>