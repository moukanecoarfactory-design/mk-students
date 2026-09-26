<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

// Handle CRUD Operations
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $action = $_POST['action'] ?? "";

    if ($action === "add") {
        $skillName = trim($_POST['skill_name'] ?? "");
        if (!empty($skillName)) {
            $stmt = $conn->prepare("SELECT id FROM skills WHERE user_id = ? AND LOWER(skill_name) = LOWER(?)");
            $stmt->bind_param("is", $userId, $skillName);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $stmt->close();
                $stmt = $conn->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)");
                $stmt->bind_param("is", $userId, $skillName);
                $stmt->execute();
            }
            $stmt->close();
        }
    } elseif ($action === "delete") {
        $skillId = (int)$_POST['skill_id'];
        $stmt = $conn->prepare("DELETE FROM skills WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $skillId, $userId);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: skills.php");
    exit();
}

// Fetch skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY skill_name ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$skills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - MK Students</title>
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
            max-width: 720px;
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
        .skills-card {
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

        /* Header */
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 22px 30px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .card-header .h-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            backdrop-filter: blur(10px);
        }
        .card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .card-header p {
            font-size: 12.5px;
            opacity: 0.9;
            margin: 2px 0 0;
        }

        /* Body */
        .card-body { padding: 26px 30px 30px; }

        /* ---------- ADD FORM ---------- */
        .add-skill-form {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
        }
        .add-skill-form input {
            flex: 1;
            padding: 13px 16px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            background: #f8faff;
            color: #27324d;
            transition: 0.22s;
            outline: none;
        }
        .add-skill-form input:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }
        .add-skill-form button {
            padding: 13px 22px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.25s;
            box-shadow: 0 10px 22px rgba(102, 126, 234, 0.32);
            white-space: nowrap;
        }
        .add-skill-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(102, 126, 234, 0.5);
        }

        /* ---------- SKILLS LIST ---------- */
        .skills-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .skill-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 13px 16px;
            background: linear-gradient(135deg, #f8faff, #eef2ff);
            border: 1.5px solid #eef1f8;
            border-radius: 13px;
            transition: 0.22s;
        }
        .skill-row:hover {
            background: #fff;
            border-color: #c7d2ff;
            box-shadow: 0 8px 22px rgba(102, 126, 234, 0.12);
            transform: translateX(3px);
        }

        .skill-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        .skill-icon {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
        }
        .skill-text {
            color: #17213c;
            font-weight: 600;
            font-size: 14px;
            word-break: break-word;
        }

        .delete-form { margin: 0; }
        .btn-delete {
            width: 36px; height: 36px;
            border-radius: 10px;
            border: 1.5px solid #ffd3d3;
            background: #fff;
            color: #d93030;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: 0.22s;
            flex-shrink: 0;
        }
        .btn-delete:hover {
            background: #ffecec;
            border-color: #d93030;
            transform: scale(1.08);
        }

        /* ---------- EMPTY STATE ---------- */
        .empty-state {
            text-align: center;
            padding: 45px 20px;
        }
        .empty-state .empty-icon {
            font-size: 50px;
            color: #d0d6e3;
            display: block;
            margin-bottom: 14px;
        }
        .empty-state p {
            color: #888;
            font-size: 14.5px;
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
            .card-body { padding: 20px 20px 24px; }
            .card-header { padding: 18px 22px; }
            .add-skill-form { flex-direction: column; }
            .add-skill-form button { justify-content: center; }
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

    <div class="skills-card">

        <!-- HEADER -->
        <div class="card-header">
            <div class="h-icon"><i class="fa-solid fa-star"></i></div>
            <div>
                <h1>Manage My Skills</h1>
                <p>Add skills to showcase your expertise to others</p>
            </div>
        </div>

        <!-- BODY -->
        <div class="card-body">

            <!-- ADD FORM -->
            <form action="skills.php" method="POST" class="add-skill-form">
                <input type="hidden" name="action" value="add">
                <input type="text" name="skill_name"
                       placeholder="Enter a skill (e.g., PHP, JavaScript, Design…)"
                       required>
                <button type="submit">
                    <i class="fa-solid fa-plus"></i> Add Skill
                </button>
            </form>

            <!-- SKILLS LIST -->
            <div class="skills-list">
                <?php if (count($skills) > 0): ?>
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-row">
                            <div class="skill-info">
                                <span class="skill-icon"><i class="fa-solid fa-check"></i></span>
                                <span class="skill-text"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            </div>
                            <form action="skills.php" method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="skill_id" value="<?php echo $skill['id']; ?>">
                                <button type="submit" class="btn-delete"
                                        onclick="return confirm('Delete this skill?');"
                                        title="Delete skill">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open empty-icon"></i>
                        <p>You haven't added any skills yet.<br>Add your first one above! ✨</p>
                    </div>
                <?php endif; ?>
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