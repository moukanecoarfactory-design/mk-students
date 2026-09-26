<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$courseId = (int)($_GET['id'] ?? 0);

if ($courseId <= 0) {
    header("Location: courses.php");
    exit();
}

// Fetch the course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $courseId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: courses.php");
    exit();
}

// Handle "remove student" action
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'remove' && $userId > 0) {
        $stmt = $conn->prepare("DELETE FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        $stmt->close();
        $message = "Student removed from course.";
        $messageType = "success";
    }
}

// Fetch enrolled students
$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.study_level, e.enrolled_at
    FROM enrollments e
    JOIN users u ON e.user_id = u.id
    WHERE e.course_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->bind_param("i", $courseId);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Course Students - mk-students";
require_once "../includes/header.php";
?>

<style>
    .cs-wrap { max-width: 900px; margin: 24px auto 40px; padding: 0 20px; }
    .cs-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .cs-back:hover { color: #fff; transform: translateX(-3px); }

    .cs-header { margin-bottom: 18px; }
    .cs-header h1 {
        font-size: 24px !important; font-weight: 800 !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        text-shadow: 0 2px 12px rgba(0,0,0,0.5) !important;
        margin: 0 0 6px !important;
    }
    .cs-header p {
        color: rgba(255,255,255,0.65) !important;
        font-size: 14px !important; margin: 0 !important;
    }

    .cs-course-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        margin-top: 10px;
        box-shadow: 0 8px 20px rgba(102,126,234,0.4);
    }

    .cs-card {
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        padding: 22px 26px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.35);
        margin-top: 20px;
    }

    .cs-alert {
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 13.5px;
        font-weight: 600;
    }
    .cs-alert.success { background: #edfff4; color: #167b3a; border-left: 4px solid #28a745; }
    .cs-alert.error { background: #fff1f1; color: #c0392b; border-left: 4px solid #c0392b; }

    .cs-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }
    .cs-table thead th {
        text-align: left;
        padding: 12px 12px;
        background: #f7f9ff;
        color: #55607c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 2px solid #e6ebf5;
    }
    .cs-table thead th:first-child { border-top-left-radius: 10px; }
    .cs-table thead th:last-child  { border-top-right-radius: 10px; }

    .cs-table tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #eef1f8;
        color: #333;
        vertical-align: middle;
    }
    .cs-table tbody tr { transition: 0.2s; }
    .cs-table tbody tr:hover { background: #fafbff; }

    .cs-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .cs-name-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .cs-name-info {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .cs-name-info strong {
        color: #17213c;
        font-weight: 700;
        font-size: 14px;
    }
    .cs-name-info small {
        color: #888;
        font-size: 12px;
    }

    .cs-level-badge {
        display: inline-block;
        background: #eef2ff;
        color: #4b5ecb;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .cs-remove-btn {
        padding: 7px 14px;
        border-radius: 9px;
        background: #ffecec;
        color: #c9302c;
        border: none;
        cursor: pointer;
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        transition: 0.22s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .cs-remove-btn:hover { background: #ffd6d6; }

    .cs-empty {
        text-align: center;
        padding: 50px 20px;
        color: #888;
    }
    .cs-empty i {
        font-size: 50px;
        color: #d0d6e3;
        display: block;
        margin-bottom: 14px;
    }
    .cs-empty p { font-size: 15px; }

    @media (max-width: 700px) {
        .cs-table thead { display: none; }
        .cs-table tbody td { display: block; padding: 8px 0; border: none; }
        .cs-table tbody tr {
            display: block;
            padding: 14px;
            margin-bottom: 12px;
            background: #f8faff;
            border-radius: 12px;
            border: 1px solid #eef1f8;
        }
        .cs-table tbody td:last-child { padding-top: 12px; }
        .cs-remove-btn { width: 100%; justify-content: center; }
    }
</style>

<main class="cs-wrap">

    <a href="courses.php" class="cs-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Courses
    </a>

    <div class="cs-header">
        <h1>👥 Enrolled Students</h1>
        <p>See who is enrolled in this course</p>
        <div class="cs-course-badge">
            <i class="fa-solid fa-book"></i>
            <?php echo htmlspecialchars($course['title']); ?>
        </div>
    </div>

    <div class="cs-card">

        <?php if ($message): ?>
            <div class="cs-alert <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (count($students) > 0): ?>
            <div style="overflow-x:auto;">
                <table class="cs-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Level</th>
                            <th>Enrolled On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s):
                            $initials = strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1));
                        ?>
                            <tr>
                                <td>
                                    <div class="cs-name-cell">
                                        <div class="cs-avatar"><?php echo $initials; ?></div>
                                        <div class="cs-name-info">
                                            <strong><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></strong>
                                            <small><?php echo htmlspecialchars($s['phone'] ?: 'No phone'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td>
                                    <span class="cs-level-badge">
                                        <?php echo htmlspecialchars($s['study_level'] ?: '—'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($s['enrolled_at'])); ?></td>
                                <td>
                                    <form method="POST" action="course-students.php?id=<?php echo $courseId; ?>" style="margin:0;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="user_id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="cs-remove-btn"
                                                onclick="return confirm('Remove this student from the course?');">
                                            <i class="fa-solid fa-user-minus"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="cs-empty">
                <i class="fa-solid fa-users-slash"></i>
                <p>No students enrolled in this course yet.</p>
            </div>
        <?php endif; ?>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>