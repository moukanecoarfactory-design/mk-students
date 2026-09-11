<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Fetch user skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY skill_name ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Profile picture
$profilePicture = (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture']))
    ? "../uploads/profiles/" . $user['profile_picture']
    : "../uploads/profiles/default-avatar.png";

$pageTitle = "Dashboard - MK Students";
require_once "../includes/header.php";
?>

<style>
    html, body {
     background: 
        linear-gradient(rgba(255, 255, 255, 0.55), rgba(245, 247, 255, 0.65)),
        url('https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1920&q=80') 
        no-repeat center center fixed !important;
    background-size: cover !important;
    min-height: 100vh !important;
}
    /* ====== Dashboard-scoped styles ====== */
    .dash-wrapper {
        max-width: 1000px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .dash-greeting {
        margin-bottom: 30px;
    }
    .dash-greeting h1 {
        font-size: 30px;
        color: #17213c;
        margin-bottom: 6px;
    }
    .dash-greeting p {
        color: #888;
        font-size: 15px;
    }
    .dash-greeting .role-badge {
        display: inline-block;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-left: 10px;
        vertical-align: middle;
    }

    .dash-card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(45, 58, 100, 0.08);
        margin-bottom: 30px;
    }

    .dash-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f0f2f5;
    }
    .dash-card-header h2 {
        font-size: 20px;
        color: #17213c;
        font-weight: 600;
    }

    .dash-actions {
        display: flex;
        gap: 10px;
    }

    .dash-btn {
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .dash-btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }
    .dash-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .dash-btn-outline {
        background: transparent;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .dash-btn-outline:hover {
        border-color: #667eea;
        color: #667eea;
    }

    /* Profile layout */
    .dash-profile {
        display: grid;
        grid-template-columns: 200px 1fr;
        gap: 35px;
        align-items: start;
    }

    .avatar-wrap {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
    }
    .avatar-wrap img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 5px solid #f0f2f5;
        box-shadow: 0 10px 25px rgba(45, 58, 100, 0.15);
    }
    .avatar-wrap::after {
        content: "";
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        z-index: -1;
        opacity: 0.15;
    }

    .info-grid {
        display: grid;
        gap: 14px;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        background: #f8faff;
        border-radius: 12px;
        border: 1px solid #eef1f8;
        transition: 0.25s;
    }
    .info-item:hover {
        border-color: #d9e1ff;
        background: #fff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.08);
    }

    .info-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .info-content {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .info-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #9aa4b8;
        font-weight: 600;
    }
    .info-value {
        color: #17213c;
        font-weight: 600;
        font-size: 14.5px;
        word-break: break-word;
    }

    /* Skills */
    .skills-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .skill-tag {
        background: rgba(102, 126, 234, 0.1);
        color: #5e7ce9;
        padding: 9px 18px;
        border-radius: 20px;
        font-size: 13.5px;
        font-weight: 600;
        border: 1px solid rgba(102, 126, 234, 0.2);
        transition: 0.2s;
    }
    .skill-tag:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        transform: translateY(-2px);
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #888;
    }
    .empty-state p {
        margin-bottom: 15px;
        font-size: 14.5px;
    }

    .dash-message {
        background: #edfff4;
        border: 1px solid #c8f0d7;
        color: #208348;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 14px;
        font-weight: 500;
    }

    /* Mobile */
    @media (max-width: 700px) {
        .dash-profile {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .dash-card-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
        .dash-actions {
            width: 100%;
            flex-wrap: wrap;
        }
    }
</style>

<main class="dash-wrapper">

    <?php if (isset($_GET['success'])): ?>
        <div class="dash-message">✅ Action completed successfully.</div>
    <?php endif; ?>

    <div class="dash-greeting">
        <h1>
            Welcome, <?php echo htmlspecialchars($user['first_name']); ?> 👋
            <span class="role-badge"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></span>
        </h1>
        <p>Here's an overview of your profile and skills.</p>
    </div>

    <!-- PROFILE CARD -->
    <section class="dash-card">
        <div class="dash-card-header">
            <h2>My Profile</h2>
            <div class="dash-actions">
                <a href="profile.php" class="dash-btn dash-btn-outline">👁 View Full</a>
                <a href="edit-profile.php" class="dash-btn dash-btn-primary">✏️ Edit Profile</a>
            </div>
        </div>

        <div class="dash-profile">
            <div class="avatar-wrap">
                <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture">
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">👤</div>
                    <div class="info-content">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">✉️</div>
                    <div class="info-content">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-content">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">🎓</div>
                    <div class="info-content">
                        <span class="info-label">Study Level</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['study_level'] ?? 'Not set'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SKILLS CARD -->
    <section class="dash-card">
        <div class="dash-card-header">
            <h2>My Skills</h2>
            <a href="skills.php" class="dash-btn dash-btn-primary">⚙️ Manage Skills</a>
        </div>

        <?php if (count($skills) > 0): ?>
            <div class="skills-preview">
                <?php foreach ($skills as $skill): ?>
                    <span class="skill-tag"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>You haven't added any skills yet.</p>
                <a href="skills.php" class="dash-btn dash-btn-primary">+ Add Your First Skill</a>
            </div>
        <?php endif; ?>
    </section>

</main>

<script src="../js/script.js"></script>
</body>
</html>