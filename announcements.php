<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireUser();

// Fetch all announcements with author name
$stmt = $conn->query("
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$announcements = $stmt->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Announcements - MK Students";
require_once "includes/header.php";
?>

<style>
    .pub-header h1 {
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: 0 2px 15px rgba(0, 0, 0, 0.6) !important;
    font-size: 30px !important;
    font-weight: 800 !important;
    margin: 0 0 6px !important;
}
.pub-header p {
    color: rgba(255,255,255,0.7) !important;
}
    html, body {
    background:
        radial-gradient(circle at 15% 15%, rgba(102, 126, 234, 0.35), transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(118, 75, 162, 0.4), transparent 45%),
        linear-gradient(135deg, #1e2340 0%, #2d1b4e 50%, #1e2340 100%) !important;
    background-attachment: fixed !important;
    min-height: 100vh !important;
}
    .pub-wrap { max-width: 800px; margin: 30px auto 50px; padding: 0 20px; }
    .pub-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .pub-back:hover { color: #fff; transform: translateX(-3px); }

   .pub-header {
    margin-bottom: 24px;
    display: flex; align-items: center; gap: 20px;
    flex-wrap: wrap;
    padding: 10px 0;
    min-height: 60px;
}
    .pub-header .h-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.5);
    }
   .pub-header h1 {
    font-size: 28px;
    font-weight: 800;
    color: #ffffff !important;
    -webkit-text-fill-color: #ffffff !important;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.5);
    margin: 0 0 4px;
    letter-spacing: -0.5px;
    line-height: 1.2;
}
    .pub-header p {
        color: rgba(255,255,255,0.6) !important;
        font-size: 14px;
        margin: 0;
    }

    .pub-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .pub-item {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 18px;
        padding: 24px 28px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        animation: fadeUp 0.5s ease both;
        border-left: 4px solid transparent;
        transition: 0.25s;
    }
    .pub-item:hover {
        border-left-color: #667eea;
        transform: translateX(4px);
        box-shadow: 0 25px 60px rgba(102, 126, 234, 0.25);
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .pub-item h3 {
        font-size: 19px;
        font-weight: 700;
        color: #17213c;
        margin: 0 0 8px;
        letter-spacing: -0.3px;
    }

    .pub-meta {
        display: flex;
        gap: 16px;
        font-size: 12.5px;
        color: #888;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .pub-meta span { display: inline-flex; align-items: center; gap: 6px; }
    .pub-meta i { color: #667eea; }

    .pub-body {
        color: #555;
        font-size: 14.5px;
        line-height: 1.75;
    }

    .pub-empty {
        text-align: center;
        padding: 60px 20px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 18px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }
    .pub-empty i {
        font-size: 54px;
        color: #d0d6e3;
        display: block;
        margin-bottom: 14px;
    }
    .pub-empty p {
        color: #888;
        font-size: 15px;
        margin: 0;
    }

    @media (max-width: 600px) {
        .pub-wrap { padding: 0 15px; }
        .pub-item { padding: 20px 22px; }
        .pub-header h1 { font-size: 22px; }
    }
</style>

<main class="pub-wrap">

    <a href="user/dashboard.php" class="pub-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="pub-header">
        <div class="h-icon"><i class="fa-solid fa-bullhorn"></i></div>
        <div>
            <h1>📢 Announcements</h1>
            <p>News and updates from the admin team</p>
        </div>
    </div>

    <div class="pub-list">
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $a): ?>
                <div class="pub-item">
                    <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                    <div class="pub-meta">
                        <span>
                            <i class="fa-solid fa-user"></i>
                            <?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?>
                        </span>
                        <span>
                            <i class="fa-regular fa-clock"></i>
                            <?php echo date('F d, Y · H:i', strtotime($a['created_at'])); ?>
                        </span>
                    </div>
                    <div class="pub-body"><?php echo nl2br(htmlspecialchars($a['body'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="pub-empty">
                <i class="fa-regular fa-bell-slash"></i>
                <p>No announcements yet. Check back later! ✨</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "includes/footer.php"; ?>