<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();

$userId = $_SESSION['user_id'];

// Handle unenroll
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($action === 'unenroll' && $courseId > 0) {
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $stmt->close();
        $message = "You've been unenrolled from the course.";
        $messageType = "success";
    }
}

// Fetch enrolled courses
$stmt = $conn->prepare("
    SELECT c.*, e.enrolled_at,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS student_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$myCourses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "My Courses - mk-students";
require_once "../includes/header.php";
?>

<style>
    .mc-wrap { max-width: 1000px; margin: 24px auto 50px; padding: 0 20px; }

    .mc-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .mc-back:hover { color: #fff; transform: translateX(-3px); }

    .mc-header {
        display: flex; align-items: center; gap: 18px;
        margin-bottom: 22px; flex-wrap: wrap;
    }
    .mc-header .h-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        box-shadow: 0 12px 28px rgba(16, 185, 129, 0.5);
    }
    .mc-header h1 {
        font-size: 28px !important; font-weight: 800 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.5) !important;
        margin: 0 0 4px !important;
        letter-spacing: -0.5px !important;
    }
    .mc-header p {
        color: rgba(255,255,255,0.65) !important;
        font-size: 14px !important; margin: 0 !important;
    }

    .alert {
        padding: 14px 20px; border-radius: 12px;
        margin-bottom: 20px; font-size: 13.5px; font-weight: 600;
        backdrop-filter: blur(10px);
    }
    .alert.success { background: rgba(74,222,128,0.15); color: #86efac; border: 1.5px solid rgba(74,222,128,0.35); }
    .alert.error   { background: rgba(239,68,68,0.15); color: #ff9b9b; border: 1.5px solid rgba(239,68,68,0.35); }

    .mc-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }

    .mc-card {
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        padding: 22px 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        transition: 0.28s;
        border-top: 4px solid #10b981;
        display: flex;
        flex-direction: column;
    }
    .mc-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 60px rgba(16, 185, 129, 0.35);
    }

    .mc-top-row {
        display: flex; justify-content: space-between;
        align-items: center; margin-bottom: 12px;
    }
    .mc-level {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 800;
        letter-spacing: 1px; text-transform: uppercase;
    }
    .mc-level.Beginner     { background: #d4f5e0; color: #167b3a; }
    .mc-level.Intermediate { background: #fff4e6; color: #c46a12; }
    .mc-level.Advanced     { background: #ffe0e0; color: #b42318; }

    .mc-enrolled-date {
        font-size: 11.5px;
        color: #888;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .mc-enrolled-date i { color: #10b981; }

    .mc-card h4 {
        font-size: 17px; font-weight: 700;
        color: #17213c; margin: 0 0 8px;
        letter-spacing: -0.2px;
    }
    .mc-card .desc {
        color: #666; font-size: 13.5px; line-height: 1.6;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .mc-meta {
        display: flex; gap: 14px; flex-wrap: wrap;
        font-size: 12px; color: #888;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f0f2f8;
    }
    .mc-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .mc-meta i { color: #10b981; }

    .mc-actions {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }
    .mc-btn {
        flex: 1;
        padding: 10px 16px;
        border-radius: 11px;
        font-size: 12.5px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        text-decoration: none;
        transition: 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: none;
    }
    .mc-btn-view {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        box-shadow: 0 8px 18px rgba(16,185,129,0.35);
    }
    .mc-btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(16,185,129,0.5);
    }
    .mc-btn-unenroll {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .mc-btn-unenroll:hover {
        border-color: #d93030;
        color: #d93030;
        background: #fff8f8;
    }

    .mc-empty {
        text-align: center;
        padding: 70px 30px;
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        grid-column: 1 / -1;
    }
    .mc-empty i {
        font-size: 60px;
        color: #d0d6e3;
        display: block;
        margin-bottom: 16px;
    }
    .mc-empty h3 {
        font-size: 20px; color: #17213c;
        margin: 0 0 8px; font-weight: 700;
    }
    .mc-empty p {
        color: #888; font-size: 14.5px;
        margin: 0 0 20px;
    }
    .mc-empty .mc-btn-browse {
        display: inline-flex;
        padding: 13px 26px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        text-decoration: none;
        border-radius: 11px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 12px 28px rgba(102,126,234,0.4);
        transition: 0.25s;
    }
    .mc-empty .mc-btn-browse:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(102,126,234,0.55);
    }

    @media (max-width: 600px) {
        .mc-header h1 { font-size: 22px !important; }
        .mc-list { grid-template-columns: 1fr; }
    }
</style>

<main class="mc-wrap">

    <a href="dashboard.php" class="mc-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="mc-header">
        <div class="h-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        <div>
            <h1>🎓 My Courses</h1>
            <p>All the courses you're currently enrolled in</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="mc-list">
        <?php if (count($myCourses) > 0): ?>
            <?php foreach ($myCourses as $c): ?>
                <div class="mc-card">
                    <div class="mc-top-row">
                        <span class="mc-level <?php echo $c['level']; ?>">
                            <?php echo htmlspecialchars($c['level']); ?>
                        </span>
                        <span class="mc-enrolled-date">
                            <i class="fa-solid fa-check-circle"></i>
                            Enrolled <?php echo date('M d', strtotime($c['enrolled_at'])); ?>
                        </span>
                    </div>

                    <h4><?php echo htmlspecialchars($c['title']); ?></h4>
                    <p class="desc"><?php echo htmlspecialchars($c['description']); ?></p>

                    <div class="mc-meta">
                        <span><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($c['teacher']); ?></span>
                        <?php if (!empty($c['duration'])): ?>
                            <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-users"></i> <?php echo $c['student_count']; ?> students</span>
                    </div>

                    <div class="mc-actions">
                        <a href="../courses.php" class="mc-btn mc-btn-view">
                            <i class="fa-solid fa-eye"></i> View Course
                        </a>
                        <form method="POST" action="my-courses.php" style="margin:0; flex:1; display:flex;">
                            <input type="hidden" name="action" value="unenroll">
                            <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                            <button type="submit" class="mc-btn mc-btn-unenroll"
                                    style="width:100%;"
                                    onclick="return confirm('Unenroll from this course?');">
                                <i class="fa-solid fa-xmark"></i> Unenroll
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mc-empty">
                <i class="fa-solid fa-graduation-cap"></i>
                <h3>No courses yet</h3>
                <p>You haven't enrolled in any course. Browse the catalog to get started!</p>
                <a href="../courses.php" class="mc-btn-browse">
                    <i class="fa-solid fa-book"></i> Browse Courses
                </a>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>