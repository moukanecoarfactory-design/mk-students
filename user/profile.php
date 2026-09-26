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

// Safe profile pic fallback
$hasPic = (!empty($user['profile_picture'])
    && $user['profile_picture'] !== 'default-avatar.png'
    && file_exists("../uploads/profiles/" . $user['profile_picture']));

$profilePicture = $hasPic ? "../uploads/profiles/" . $user['profile_picture'] : null;
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MK Students</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #333;
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
            font-size: 18px;
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
            max-width: 780px;
            margin: 26px auto 40px;
            padding: 0 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a8b8ff;
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            margin-bottom: 16px;
            transition: 0.2s;
        }
        .back-link:hover { color: #fff; transform: translateX(-3px); }

        /* ============ CARD ============ */
        .profile-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.5);
            animation: fadeUp 0.65s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ---------- HEADER BANNER ---------- */
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 26px 32px;
            display: flex;
            align-items: center;
            gap: 22px;
            flex-wrap: wrap;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .profile-header::before {
            content: "";
            position: absolute;
            top: -50%; right: -20%;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 60%);
            pointer-events: none;
        }
        .profile-header > * { position: relative; z-index: 1; }

        .avatar-wrap {
            position: relative;
            width: 110px;
            height: 110px;
            flex-shrink: 0;
        }
        .avatar-wrap img,
        .avatar-fallback {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            font-size: 44px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
            backdrop-filter: blur(10px);
        }

        .profile-header-info { flex: 1; min-width: 200px; }
        .profile-header-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
            line-height: 1.15;
        }
        .profile-email {
            font-size: 13.5px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 12px;
        }
        .quick-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .quick-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 6px 13px;
            border-radius: 22px;
            font-size: 11.5px;
            font-weight: 600;
            backdrop-filter: blur(6px);
        }

        /* ---------- BODY ---------- */
        .profile-body {
            padding: 24px 32px 28px;
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 28px;
            align-items: start;
        }

        .about-section h3 {
            font-size: 12.5px;
            font-weight: 700;
            color: #55607c;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .about-section h3 i { color: #5e7ce9; font-size: 13px; }

        .about-section p {
            color: #555;
            font-size: 14px;
            line-height: 1.65;
        }
        .about-section .no-bio {
            color: #999;
            font-style: italic;
        }

        /* ---------- ACTION BUTTONS ---------- */
        .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            padding: 11px 18px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            box-shadow: 0 10px 22px rgba(102, 126, 234, 0.35);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(102, 126, 234, 0.5);
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
        .btn-danger {
            background: #fff;
            border: 1.5px solid #ffd3d3;
            color: #d93030;
        }
        .btn-danger:hover {
            background: #fff1f1;
            border-color: #d93030;
            transform: translateY(-2px);
        }

        /* ---------- FOOTER ---------- */
        .footer {
            text-align: center;
            padding: 22px 20px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 12.5px;
        }
        .footer span { color: #a8b8ff; font-weight: 700; }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 700px) {
            .navbar { padding: 12px 20px; }
            .navbar .brand { font-size: 15px; }
            .navbar .user-pill { display: none; }
            .profile-header { padding: 22px 22px; justify-content: center; text-align: center; }
            .profile-header-info { text-align: center; }
            .profile-email { justify-content: center; }
            .quick-badges { justify-content: center; }
            .profile-body {
                grid-template-columns: 1fr;
                padding: 22px 22px 26px;
            }
            .profile-actions { flex-direction: row; flex-wrap: wrap; }
            .profile-actions .btn { flex: 1; min-width: 130px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a href="dashboard.php" class="brand">
        <i class="fa-solid fa-graduation-cap"></i> MK-Students
    </a>
    <div class="right">
        <span class="user-pill">
            <i class="fa-solid fa-circle-user"></i>
            <?php echo htmlspecialchars($user['first_name']); ?>
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

    <div class="profile-card">

        <!-- HEADER BANNER -->
        <div class="profile-header">
            <div class="avatar-wrap">
                <?php if ($profilePicture): ?>
                    <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile">
                <?php else: ?>
                    <div class="avatar-fallback"><?php echo $initials; ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="profile-email">
                    <i class="fa-solid fa-envelope"></i>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <div class="quick-badges">
                    <div class="quick-badge">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <?php echo htmlspecialchars($user['study_level'] ?? 'Not set'); ?>
                    </div>
                    <div class="quick-badge">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="profile-body">
            <div class="about-section">
                <h3><i class="fa-solid fa-user"></i> About Me</h3>
                <?php if (!empty($user['bio'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php else: ?>
                    <p class="no-bio">No bio added yet. Click "Edit Profile" to add one.</p>
                <?php endif; ?>
            </div>

            <div class="profile-actions">
                <a href="edit-profile.php" class="btn btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                </a>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i> Dashboard
                </a>
                <a href="delete-account.php" class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
                    <i class="fa-solid fa-trash"></i> Delete Account
                </a>
            </div>
        </div>

    </div>

</main>

<!-- FOOTER -->
<footer class="footer">
    &copy; <?php echo date('Y'); ?> <span>MK Students</span> · All rights reserved
</footer>

</body>
</html>