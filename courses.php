<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$userId = $_SESSION['user_id'];

// Handle enroll / unenroll
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($courseId > 0) {
        if ($action === 'enroll') {
            // Check if not already enrolled
            $stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
            $stmt->bind_param("ii", $userId, $courseId);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$exists) {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $userId, $courseId);
                $stmt->execute();
                $stmt->close();
                $message = "🎉 You've successfully enrolled!";
                $messageType = "success";
            }
        } elseif ($action === 'unenroll') {
            $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
            $stmt->bind_param("ii", $userId, $courseId);
            $stmt->execute();
            $stmt->close();
            $message = "You've been unenrolled from the course.";
            $messageType = "success";
        }
    }
}

// Fetch all courses with enrollment count + whether current user is enrolled
$stmt = $conn->prepare("
    SELECT c.*,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS student_count,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND user_id = ?) AS is_enrolled
    FROM courses c
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Role-aware dashboard link
$dashboardLink = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin')
    ? 'admin/dashboard.php'
    : 'user/dashboard.php';

$pageTitle = "Courses - MK Students";
require_once "includes/header.php";
?>

<style>
    .crs-wrap { max-width: 1000px; margin: 24px auto 50px; padding: 0 20px; }

    .crs-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .crs-back:hover { color: #fff; transform: translateX(-3px); }

    .crs-header {
        display: flex; align-items: center; gap: 18px;
        margin-bottom: 22px; flex-wrap: wrap;
    }
    .crs-header .h-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px;
        box-shadow: 0 12px 28px rgba(102,126,234,0.5);
    }
    .crs-header h1 {
        font-size: 28px !important; font-weight: 800 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        text-shadow: 0 2px 12px rgba(0, 0, 0, 0.5) !important;
        margin: 0 0 4px !important;
        letter-spacing: -0.5px !important;
    }
    .crs-header p {
        color: rgba(255,255,255,0.65) !important;
        font-size: 14px !important;
        margin: 0 !important;
    }

    .alert {
        padding: 14px 20px; border-radius: 12px;
        margin-bottom: 20px; font-size: 13.5px; font-weight: 600;
        backdrop-filter: blur(10px);
    }
    .alert.success { background: rgba(74,222,128,0.15); color: #86efac; border: 1.5px solid rgba(74,222,128,0.35); }
    .alert.error   { background: rgba(239,68,68,0.15); color: #ff9b9b; border: 1.5px solid rgba(239,68,68,0.35); }

    .crs-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 16px;
    }

    .crs-card {
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        padding: 22px 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        transition: 0.28s;
        border-top: 4px solid #667eea;
        display: flex;
        flex-direction: column;
    }
    .crs-card.enrolled {
        border-top-color: #10b981;
        box-shadow: 0 20px 50px rgba(16, 185, 129, 0.3);
    }
    .crs-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 25px 60px rgba(102,126,234,0.3);
    }

    .crs-badges {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .crs-level {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 800;
        letter-spacing: 1px; text-transform: uppercase;
    }
    .crs-level.Beginner     { background: #d4f5e0; color: #167b3a; }
    .crs-level.Intermediate { background: #fff4e6; color: #c46a12; }
    .crs-level.Advanced     { background: #ffe0e0; color: #b42318; }

    .crs-enrolled-tag {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 800;
        letter-spacing: 1px; text-transform: uppercase;
        background: #d4f5e0; color: #167b3a;
    }

    .crs-card h4 {
        font-size: 17px; font-weight: 700;
        color: #17213c; margin: 0 0 8px;
        letter-spacing: -0.2px;
    }
    .crs-card .desc {
        color: #666; font-size: 13.5px; line-height: 1.6;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .crs-meta {
        display: flex; gap: 14px; flex-wrap: wrap;
        font-size: 12px; color: #888;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f0f2f8;
    }
    .crs-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .crs-meta i { color: #667eea; }

    .crs-btn {
        width: 100%;
        padding: 11px 16px;
        border-radius: 11px;
        font-size: 13.5px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        border: none;
        transition: 0.25s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: auto;
    }
    .crs-btn.enroll {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 10px 22px rgba(102,126,234,0.4);
    }
    .crs-btn.enroll:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(102,126,234,0.55);
    }
    .crs-btn.unenroll {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .crs-btn.unenroll:hover {
        border-color: #d93030;
        color: #d93030;
        background: #fff8f8;
    }

    .crs-empty {
        text-align: center;
        padding: 60px 20px;
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        grid-column: 1 / -1;
    }
    .crs-empty i {
        font-size: 54px; color: #d0d6e3;
        display: block; margin-bottom: 14px;
    }
    .crs-empty p { color: #888; font-size: 15px; }

    @media (max-width: 600px) {
        .crs-header h1 { font-size: 22px !important; }
        .crs-list { grid-template-columns: 1fr; }
    }
</style>

<main class="crs-wrap">

    <a href="<?php echo $dashboardLink; ?>" class="crs-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="crs-header">
        <div class="h-icon"><i class="fa-solid fa-book"></i></div>
        <div>
            <h1>📚 Available Courses</h1>
            <p>Browse our courses and enroll to start learning</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="crs-list">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $c):
                $isEnrolled = $c['is_enrolled'] > 0;
            ?>
                <div class="crs-card <?php echo $isEnrolled ? 'enrolled' : ''; ?>">
                    <div class="crs-badges">
                        <span class="crs-level <?php echo $c['level']; ?>">
                            <?php echo htmlspecialchars($c['level']); ?>
                        </span>
                        <?php if ($isEnrolled): ?>
                            <span class="crs-enrolled-tag">
                                <i class="fa-solid fa-check"></i> Enrolled
                            </span>
                        <?php endif; ?>
                    </div>

                    <h4><?php echo htmlspecialchars($c['title']); ?></h4>
                    <p class="desc"><?php echo htmlspecialchars($c['description']); ?></p>

                    <div class="crs-meta">
                        <span><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($c['teacher']); ?></span>
                        <?php if (!empty($c['duration'])): ?>
                            <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-users"></i> <?php echo $c['student_count']; ?> enrolled</span>
                    </div>

                    <form method="POST" action="courses.php" style="margin:0;">
                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                        <?php if ($isEnrolled): ?>
                            <input type="hidden" name="action" value="unenroll">
                            <button type="submit" class="crs-btn unenroll"
                                    onclick="return confirm('Unenroll from this course?');">
                                <i class="fa-solid fa-xmark"></i> Unenroll
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="enroll">
                            <button type="submit" class="crs-btn enroll">
                                <i class="fa-solid fa-plus"></i> Enroll Now
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="crs-empty">
                <i class="fa-solid fa-book-open"></i>
                <p>No courses available yet. Check back soon! ✨</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "includes/footer.php"; ?>