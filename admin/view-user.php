<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId  = $_SESSION['user_id'];
$targetId = (int)($_GET['id'] ?? 0);
$isMe     = ($targetId == $adminId);

if ($targetId <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch user
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

// Profile pic fallback
$profilePic = (!empty($user['profile_picture'])
    && $user['profile_picture'] !== 'default-avatar.png'
    && file_exists("../uploads/profiles/" . $user['profile_picture']))
    ? "../uploads/profiles/" . $user['profile_picture']
    : null;

$initial = strtoupper(substr($user['first_name'], 0, 1));

$pageTitle = "View User - mk-students";
require_once "../includes/header.php";
?>

<style>
    .view-page { max-width: 900px; margin: 40px auto; padding: 0 20px; }

    .view-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #5e7ce9;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 22px;
        transition: 0.2s;
    }
    .view-back:hover { color: #764ba2; transform: translateX(-3px); }

    .view-card {
        background: #fff;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(45, 58, 100, 0.12);
    }

    /* Header */
    .view-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 40px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 26px;
        flex-wrap: wrap;
    }
    .view-avatar {
        width: 110px; height: 110px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.35);
        overflow: hidden;
        flex-shrink: 0;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    .view-avatar img {
        width: 100%; height: 100%;
        object-fit: cover;
    }
    .view-info { flex: 1; min-width: 220px; }
    .view-info h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.3px;
    }
    .view-email {
        font-size: 14.5px;
        opacity: 0.92;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    .view-badges { display: flex; flex-wrap: wrap; gap: 10px; }
    .view-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(255,255,255,0.18);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 7px 14px;
        border-radius: 25px;
        font-size: 12.5px;
        font-weight: 600;
    }

    /* Body */
    .view-body {
        padding: 32px 40px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 34px;
    }
    .view-body h3 {
        font-size: 14px;
        font-weight: 700;
        color: #17213c;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .view-body h3 i { color: #5e7ce9; }
    .view-body p {
        color: #555;
        font-size: 14.5px;
        line-height: 1.7;
    }
    .view-muted { color: #999; font-style: italic; font-size: 13.5px; }

    /* Skills */
    .view-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .view-skill {
        background: rgba(94, 124, 233, 0.1);
        color: #5e7ce9;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid rgba(94, 124, 233, 0.22);
        transition: 0.2s;
    }
    .view-skill:hover {
        background: linear-gradient(135deg, #5e7ce9, #7547a9);
        color: #fff;
        transform: translateY(-2px);
    }

    .view-empty {
        text-align: center;
        padding: 30px 15px;
        color: #9aa4b8;
        font-size: 13.5px;
    }
    .view-empty i {
        display: block;
        font-size: 30px;
        color: #d0d6e3;
        margin-bottom: 8px;
    }

    /* Actions */
    .view-actions {
        padding: 22px 40px;
        border-top: 1px solid #f0f2f5;
        background: #fafbff;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: flex-end;
    }
    .view-btn {
        padding: 12px 22px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.22s;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
    .view-btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.28);
    }
    .view-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(102, 126, 234, 0.38);
    }
    .view-btn-outline {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .view-btn-outline:hover { border-color: #667eea; color: #667eea; }
    .view-btn-danger {
        background: #fff;
        border: 1.5px solid #ffd3d3;
        color: #d93030;
    }
    .view-btn-danger:hover { background: #fff1f1; border-color: #d93030; }

    @media (max-width: 700px) {
        .view-hero { padding: 30px 24px; text-align: center; justify-content: center; }
        .view-info { text-align: center; }
        .view-email { justify-content: center; }
        .view-badges { justify-content: center; }
        .view-body { grid-template-columns: 1fr; padding: 24px; }
        .view-actions { padding: 20px 24px; justify-content: center; }
        .view-btn { width: 100%; justify-content: center; }
    }
</style>

<main class="view-page">

    <a href="dashboard.php" class="view-back">
        <i class="fa-solid fa-arrow-left"></i> Back to User Management
    </a>

    <div class="view-card">

        <!-- HERO -->
        <div class="view-hero">
            <div class="view-avatar">
                <?php if ($profilePic): ?>
                    <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="">
                <?php else: ?>
                    <?php echo $initial; ?>
                <?php endif; ?>
            </div>
            <div class="view-info">
                <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <p class="view-email">
                    <i class="fa-solid fa-envelope"></i>
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <div class="view-badges">
                    <span class="view-badge">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <?php echo htmlspecialchars($user['study_level'] ?? 'Not set'); ?>
                    </span>
                    <span class="view-badge">
                        <i class="fa-solid fa-phone"></i>
                        <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>
                    </span>
                    <span class="view-badge">
                        <i class="fa-solid fa-circle-check"></i>
                        <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                    </span>
                    <span class="view-badge">
                        <i class="fa-solid fa-user-tag"></i>
                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- BODY -->
        <div class="view-body">
            <div>
                <h3><i class="fa-solid fa-user"></i> About</h3>
                <?php if (!empty($user['bio'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php else: ?>
                    <p class="view-muted">No bio provided.</p>
                <?php endif; ?>

                <h3 style="margin-top:24px;"><i class="fa-solid fa-calendar-days"></i> Registered</h3>
                <p><?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>

            <div>
                <h3><i class="fa-solid fa-lightbulb"></i> Skills</h3>
                <?php if (count($skills) > 0): ?>
                    <div class="view-skills">
                        <?php foreach ($skills as $s): ?>
                            <span class="view-skill">
                                <i class="fa-solid fa-check"></i>
                                <?php echo htmlspecialchars($s['skill_name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="view-empty">
                        <i class="fa-solid fa-folder-open"></i>
                        No skills recorded.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="view-actions">

            <?php if ($isMe): ?>
                <!-- Admin viewing themselves -->
                <a href="../user/profile.php" class="view-btn view-btn-outline">
                    <i class="fa-solid fa-user"></i> My Profile Page
                </a>
                <a href="../user/edit-profile.php" class="view-btn view-btn-primary">
                    <i class="fa-solid fa-pen"></i> Edit My Info
                </a>
            <?php else: ?>
                <!-- Admin viewing another user -->
                <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="view-btn view-btn-primary">
                    <i class="fa-solid fa-pen"></i> Edit User
                </a>
                <a href="toggle-user.php?id=<?php echo $user['id']; ?>" class="view-btn view-btn-outline"
                   onclick="return confirm('<?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> this user?');">
                    <i class="fa-solid fa-power-off"></i>
                    <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                </a>
                <a href="delete-user.php?id=<?php echo $user['id']; ?>" class="view-btn view-btn-danger"
                   onclick="return confirm('Delete this user permanently?');">
                    <i class="fa-solid fa-trash"></i> Delete
                </a>
            <?php endif; ?>

        </div>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>