<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);
if ($targetId <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

// Fetch skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY skill_name ASC");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$profilePic = (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture']))
    ? "../uploads/profiles/" . $user['profile_picture']
    : "../uploads/profiles/default-avatar.png";

$pageTitle = "View User - mk-students";
require_once "../includes/header.php";
?>

<main class="page-container">

    <a href="dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to User Management
    </a>

    <div class="dash-card profile-page-card">

        <!-- PROFILE HEADER -->
        <div class="profile-header-section">
            <div class="profile-image-container">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" class="profile-image profile-image-large" alt="Profile">
            </div>
            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="profile-email">
                    <i class="fa-solid fa-envelope"></i>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <div class="profile-quick-info">
                    <div class="quick-badge">
                        <i class="fa-solid fa-book"></i>
                        <?php echo htmlspecialchars($user['study_level'] ?? 'Not set'); ?>
                    </div>
                    <div class="quick-badge">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>
                    </div>
                    <div class="quick-badge">
                        <i class="fa-solid fa-circle-check"></i>
                        <?php echo ucfirst(htmlspecialchars($user['status'] ?? 'active')); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- TWO-COLUMN BODY -->
        <div class="profile-body-section">

            <!-- LEFT: About + Registered -->
            <div class="about-section">
                <h3><i class="fa-solid fa-user"></i> About</h3>
                <p>
                    <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : "No bio provided."; ?>
                </p>

                <h3 style="margin-top: 25px;"><i class="fa-solid fa-calendar-days"></i> Registered On</h3>
                <p><?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>

            <!-- RIGHT: Skills -->
            <div class="skills-panel">
                <h3><i class="fa-solid fa-lightbulb"></i> Skills</h3>
                <?php if (count($skills) > 0): ?>
                    <div class="skills-preview">
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag">
                                <i class="fa-solid fa-check"></i>
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open empty-icon"></i>
                        <p>No skills recorded yet.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="profile-actions-row">
            <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                <i class="fa-solid fa-pen-to-square"></i> Edit User
            </a>
            <a href="toggle-user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">
                <i class="fa-solid fa-power-off"></i>
                <?php echo ($user['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate'; ?>
            </a>
            <a href="delete-user.php?id=<?php echo $user['id']; ?>"
               class="btn btn-outline danger"
               onclick="return confirm('Delete this user?');">
                <i class="fa-solid fa-trash"></i> Delete
            </a>
        </div>

    </div>
</main>

<?php require_once "../includes/footer.php"; ?>