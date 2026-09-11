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
            // Check for duplicates
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

$profilePicture = (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) 
    ? "../uploads/profiles/" . $user['profile_picture'] 
    : "../uploads/profiles/default-avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - mk-students</title>
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
                <h2><i class="fa-solid fa-star"></i> Manage My Skills</h2>
            </div>

            <!-- Add Skill Form -->
            <form action="skills.php" method="POST" class="add-skill-form">
                <input type="hidden" name="action" value="add">
                <div class="skill-input-group">
                    <input type="text" name="skill_name" placeholder="Enter a skill (e.g., PHP, JavaScript, Design...)" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Add Skill
                    </button>
                </div>
            </form>

            <!-- Skills List -->
            <div class="skills-management-list">
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
                                <button type="submit" class="btn-delete" onclick="return confirm('Delete this skill?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-folder-open empty-icon"></i>
                        <p>You haven't added any skills yet.</p>
                    </div>
                <?php endif; ?>
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