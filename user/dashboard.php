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

if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY skill_name ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
// Latest 3 announcements
$annResult = $conn->query("
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 3
");
$latestAnnouncements = $annResult->fetch_all(MYSQLI_ASSOC);

$hasPic = (!empty($user['profile_picture'])
    && $user['profile_picture'] !== 'default-avatar.png'
    && file_exists("../uploads/profiles/" . $user['profile_picture']));

$profilePicture = $hasPic ? "../uploads/profiles/" . $user['profile_picture'] : null;
$initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));

$pageTitle = "Dashboard - MK Students";
require_once "../includes/header.php";
?>
<style>
    /* =====================================================
       COMPACT PREMIUM USER DASHBOARD
    ===================================================== */
    html, body {
        background:
            radial-gradient(circle at 15% 20%, rgba(102, 126, 234, 0.35), transparent 45%),
            radial-gradient(circle at 85% 75%, rgba(118, 75, 162, 0.35), transparent 45%),
            linear-gradient(135deg, #1e2340 0%, #2d1b4e 50%, #1e2340 100%) !important;
        min-height: 100vh;
        background-attachment: fixed !important;
    }

    .dash-shell {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px 22px 30px;
}

    /* ---------- GREETING ---------- */
    .greet {
        margin-bottom: 14px;
        animation: fadeDown 0.6s ease both;
    }
    .greet h1 {
        font-size: 26px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.6px;
        margin: 0 0 2px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        line-height: 1.2;
    }
    .greet h1 em {
        font-style: normal;
        background: linear-gradient(135deg, #a8b8ff, #d4a8ff);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .greet p {
        color: rgba(255, 255, 255, 0.55);
        font-size: 12.5px;
        margin: 0;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.08);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        color: #fff;
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }
    .role-badge::before {
        content: "";
        width: 5px; height: 5px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 8px #4ade80;
    }

    .wave {
        display: inline-block;
        animation: wave 2.4s ease-in-out infinite;
        transform-origin: 70% 70%;
        font-size: 22px;
    }
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        10%, 30% { transform: rotate(14deg); }
        20%, 40% { transform: rotate(-8deg); }
    }
    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ---------- SUCCESS ---------- */
    .dash-msg {
        background: rgba(74, 222, 128, 0.15);
        border: 1.5px solid rgba(74, 222, 128, 0.35);
        color: #86efac;
        padding: 10px 16px;
        border-radius: 12px;
        margin-bottom: 14px;
        font-size: 12.5px;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }

    /* ---------- CARDS ---------- */
    .card {
        background: rgba(255, 255, 255, 0.05);
        border: 1.5px solid rgba(255, 255, 255, 0.09);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-radius: 18px;
        padding: 18px 22px;
        margin-bottom: 14px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        animation: fadeUp 0.6s ease both;
    }
    .card:nth-of-type(1) { animation-delay: 0.05s; }
    .card:nth-of-type(2) { animation-delay: 0.12s; }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1.5px solid rgba(255, 255, 255, 0.08);
    }
    .card-head h2 {
        font-size: 15px;
        font-weight: 700;
        color: #fff;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        letter-spacing: -0.2px;
    }
    .h-icon {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        box-shadow: 0 5px 12px rgba(102, 126, 234, 0.45);
    }

    .actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .btn {
        padding: 7px 13px;
        border-radius: 9px;
        font-size: 11.5px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: 0.22s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-family: inherit;
        white-space: nowrap;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(102, 126, 234, 0.55);
    }
    .btn-glass {
        background: rgba(255, 255, 255, 0.08);
        border: 1.5px solid rgba(255, 255, 255, 0.15);
        color: #d1d5f0;
    }
    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        color: #fff;
        transform: translateY(-2px);
    }

    /* ---------- PROFILE GRID ---------- */
    .profile-grid {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 22px;
        align-items: center;
    }

    .avatar {
        position: relative;
        width: 120px;
        height: 120px;
        margin: 0 auto;
    }
    .avatar::before {
        content: "";
        position: absolute;
        inset: -10px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, #667eea, #a78bfa, #764ba2, #667eea);
        animation: spin 6s linear infinite;
        filter: blur(2px);
        z-index: -1;
    }
    .avatar::after {
        content: "";
        position: absolute;
        inset: -16px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(167, 139, 250, 0.4), transparent 65%);
        z-index: -2;
        filter: blur(10px);
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    .avatar-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 4px solid rgba(30, 35, 64, 1);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea, #764ba2);
        font-size: 44px;
        font-weight: 800;
        color: #fff;
        letter-spacing: -2px;
        box-shadow: inset 0 0 40px rgba(0, 0, 0, 0.2);
    }
    .avatar-inner img {
        width: 100%; height: 100%; object-fit: cover;
    }

    .avatar-name {
        text-align: center;
        margin-top: 10px;
        color: #fff;
        font-weight: 700;
        font-size: 12.5px;
        letter-spacing: -0.2px;
    }
    .avatar-role {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
        font-size: 9.5px;
        margin-top: 2px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 600;
    }

    /* ---------- INFO TILES ---------- */
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 9px;
    }
    .info-tile {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px 13px;
        background: rgba(255, 255, 255, 0.05);
        border: 1.5px solid rgba(255, 255, 255, 0.09);
        border-radius: 11px;
        transition: 0.25s;
        backdrop-filter: blur(10px);
    }
    .info-tile:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(167, 139, 250, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(102, 126, 234, 0.25);
    }

    .tile-icon {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 5px 14px rgba(0, 0, 0, 0.3);
    }
    .tile-icon.i-name  { background: linear-gradient(135deg, #667eea, #764ba2); }
    .tile-icon.i-email { background: linear-gradient(135deg, #f97316, #ef4444); }
    .tile-icon.i-phone { background: linear-gradient(135deg, #10b981, #059669); }
    .tile-icon.i-edu   { background: linear-gradient(135deg, #f59e0b, #dc2626); }

    .tile-text { display: flex; flex-direction: column; min-width: 0; }
    .tile-label {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: rgba(255, 255, 255, 0.45);
        font-weight: 700;
        margin-bottom: 2px;
    }
    .tile-value {
        color: #fff;
        font-weight: 600;
        font-size: 12.5px;
        word-break: break-word;
    }

    /* ---------- SKILLS ---------- */
    .skills-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }
    .skill-tag {
        background: rgba(102, 126, 234, 0.15);
        border: 1.5px solid rgba(167, 139, 250, 0.3);
        color: #c7d2ff;
        padding: 6px 13px;
        border-radius: 18px;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: 0.25s;
    }
    .skill-tag::before {
        content: "✓";
        font-weight: 900;
        color: #4ade80;
    }
    .skill-tag:hover {
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-color: transparent;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(102, 126, 234, 0.5);
    }
    .skill-tag:hover::before { color: #fff; }

    .empty {
        text-align: center;
        padding: 22px 16px;
    }
    .empty-icon {
        font-size: 32px;
        color: rgba(255, 255, 255, 0.15);
        display: block;
        margin-bottom: 8px;
    }
    .empty p {
        color: rgba(255, 255, 255, 0.5);
        font-size: 12.5px;
        margin-bottom: 12px;
    }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 750px) {
        .profile-grid { grid-template-columns: 1fr; gap: 18px; text-align: center; }
        .info-grid { grid-template-columns: 1fr; text-align: left; }
        .greet h1 { font-size: 20px; }
        .card { padding: 16px 16px; }
        .card-head { flex-direction: column; align-items: flex-start; }
        .actions { width: 100%; justify-content: stretch; }
        .btn { flex: 1; justify-content: center; }
    }
    /* ---------- ANNOUNCEMENTS PREVIEW ---------- */
.ann-preview-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ann-preview-item {
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(255, 255, 255, 0.09);
    border-left: 4px solid #667eea;
    border-radius: 12px;
    padding: 15px 18px;
    transition: 0.25s;
    cursor: pointer;
}
.ann-preview-item:hover {
    background: rgba(255, 255, 255, 0.09);
    border-left-color: #a78bfa;
    transform: translateX(4px);
    box-shadow: 0 10px 24px rgba(102, 126, 234, 0.2);
}
.ann-preview-item h4 {
    color: #fff;
    font-size: 14.5px;
    font-weight: 700;
    margin: 0 0 6px;
    letter-spacing: -0.2px;
}
.ann-preview-meta {
    display: flex;
    gap: 14px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 8px;
    flex-wrap: wrap;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ann-preview-meta i {
    color: #a8b8ff;
    margin-right: 4px;
}
.ann-preview-body {
    color: rgba(255, 255, 255, 0.7);
    font-size: 13px;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.ann-empty {
    text-align: center;
    padding: 26px 16px;
    color: rgba(255, 255, 255, 0.4);
    font-size: 13px;
}
.ann-empty i {
    font-size: 30px;
    color: rgba(255, 255, 255, 0.15);
    display: block;
    margin-bottom: 8px;
}

/* ---------- 2-COLUMN GRID FOR BOTTOM CARDS ---------- */
.bottom-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 14px;
}
.bottom-grid .card {
    margin-bottom: 0;
}
.bottom-grid .card.full {
    grid-column: 1 / -1;
}
@media (max-width: 800px) {
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="dash-shell">

    <?php if (isset($_GET['success'])): ?>
        <div class="dash-msg">✅ Action completed successfully.</div>
    <?php endif; ?>

    <!-- GREETING -->
    <div class="greet">
        <h1>
            Welcome, <em><?php echo htmlspecialchars($user['first_name']); ?></em>
            <span class="wave">👋</span>
            <span class="role-badge"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></span>
        </h1>
        <p>Here's an overview of your profile and skills.</p>
    </div>

    <!-- PROFILE CARD -->
    <section class="card">
        <div class="card-head">
            <h2><span class="h-icon"><i class="fa-solid fa-id-card"></i></span> My Profile</h2>
            <div class="actions">
                <a href="profile.php"          class="btn btn-glass"><i class="fa-solid fa-eye"></i> View Full</a>
                <a href="edit-profile.php"     class="btn btn-primary"><i class="fa-solid fa-pen"></i> Edit</a>
                <a href="change-password.php"  class="btn btn-glass"><i class="fa-solid fa-lock"></i> Password</a>
            </div>
        </div>

        <div class="profile-grid">
            <div>
                <div class="avatar">
                    <div class="avatar-inner">
                        <?php if ($profilePicture): ?>
                            <img src="<?php echo htmlspecialchars($profilePicture); ?>" alt="">
                        <?php else: ?>
                            <?php echo $initials; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="avatar-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <div class="avatar-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></div>
            </div>

            <div class="info-grid">
                <div class="info-tile">
                    <div class="tile-icon i-name"><i class="fa-solid fa-user"></i></div>
                    <div class="tile-text">
                        <span class="tile-label">Name</span>
                        <span class="tile-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    </div>
                </div>

                <div class="info-tile">
                    <div class="tile-icon i-email"><i class="fa-solid fa-envelope"></i></div>
                    <div class="tile-text">
                        <span class="tile-label">Email</span>
                        <span class="tile-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>

                <div class="info-tile">
                    <div class="tile-icon i-phone"><i class="fa-solid fa-phone"></i></div>
                    <div class="tile-text">
                        <span class="tile-label">Phone</span>
                        <span class="tile-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></span>
                    </div>
                </div>

                <div class="info-tile">
                    <div class="tile-icon i-edu"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="tile-text">
                        <span class="tile-label">Study Level</span>
                        <span class="tile-value"><?php echo htmlspecialchars($user['study_level'] ?: 'Not set'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

            <div class="bottom-grid">

        <!-- ANNOUNCEMENTS CARD -->
        <section class="card">
            <div class="card-head">
                <h2>
                    <span class="h-icon"><i class="fa-solid fa-bullhorn"></i></span>
                    Latest Announcements
                </h2>
                <a href="../announcements.php" class="btn btn-glass">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View All
                </a>
            </div>

            <?php if (count($latestAnnouncements) > 0): ?>
                <div class="ann-preview-list">
                    <?php foreach ($latestAnnouncements as $a): ?>
                        <a href="../announcements.php" style="text-decoration:none;">
                            <div class="ann-preview-item">
                                <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                                <div class="ann-preview-meta">
                                    <span>
                                        <i class="fa-solid fa-user"></i>
                                        <?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?>
                                    </span>
                                    <span>
                                        <i class="fa-regular fa-clock"></i>
                                        <?php echo date('M d, Y', strtotime($a['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="ann-preview-body">
                                    <?php echo htmlspecialchars($a['body']); ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="ann-empty">
                    <i class="fa-regular fa-bell-slash"></i>
                    No announcements yet.
                </div>
            <?php endif; ?>
        </section>

        <!-- MY COURSES CARD -->
        <section class="card">
            <div class="card-head">
                <h2><span class="h-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fa-solid fa-graduation-cap"></i></span> My Courses</h2>
                <a href="my-courses.php" class="btn btn-primary">
                    <i class="fa-solid fa-book"></i> View All
                </a>
            </div>

            <?php
            // Fetch enrolled count
            $enrollStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM enrollments WHERE user_id = ?");
            $enrollStmt->bind_param("i", $userId);
            $enrollStmt->execute();
            $enrollCount = $enrollStmt->get_result()->fetch_assoc()['cnt'];
            $enrollStmt->close();
            ?>

            <?php if ($enrollCount > 0): ?>
                <div style="text-align:center; padding:20px; color:#fff;">
                    <div style="font-size:36px; font-weight:800; color:#4ade80;"><?php echo $enrollCount; ?></div>
                    <div style="font-size:13px; color:rgba(255,255,255,0.7);">
                        <?php echo $enrollCount == 1 ? 'course enrolled' : 'courses enrolled'; ?>
                    </div>
                    <a href="my-courses.php" style="display:inline-block;margin-top:14px;padding:10px 20px;background:linear-gradient(135deg,#10b981,#059669);color:#fff;text-decoration:none;border-radius:10px;font-size:13px;font-weight:700;">
                        <i class="fa-solid fa-graduation-cap"></i> View My Courses
                    </a>
                </div>
            <?php else: ?>
                <div class="empty">
                    <i class="fa-solid fa-graduation-cap empty-icon"></i>
                    <p>You haven't enrolled in any course yet.</p>
                    <a href="../courses.php" class="btn btn-primary">
                        <i class="fa-solid fa-book"></i> Browse Courses
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- SKILLS CARD -->
        <section class="card full">
            <div class="card-head">
                <h2><span class="h-icon"><i class="fa-solid fa-lightbulb"></i></span> My Skills</h2>
                <a href="skills.php" class="btn btn-primary"><i class="fa-solid fa-gear"></i> Manage Skills</a>
            </div>

            <?php if (count($skills) > 0): ?>
                <div class="skills-wrap">
                    <?php foreach ($skills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <i class="fa-solid fa-folder-open empty-icon"></i>
                    <p>You haven't added any skills yet.</p>
                    <a href="skills.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Your First Skill</a>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<script src="../js/script.js"></script>
</body>
</html>