<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE
    if ($action === 'create') {
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $teacher     = trim($_POST['teacher'] ?? '');
        $duration    = trim($_POST['duration'] ?? '');
        $level       = $_POST['level'] ?? 'Beginner';

        if (empty($title) || empty($description) || empty($teacher)) {
            $message = "Please fill in all required fields.";
            $messageType = "error";
        } elseif (!in_array($level, ['Beginner','Intermediate','Advanced'])) {
            $level = 'Beginner';
        } else {
            $stmt = $conn->prepare("INSERT INTO courses (title, description, teacher, duration, level) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $description, $teacher, $duration, $level);
            $stmt->execute();
            $stmt->close();
            $message = "Course published successfully!";
            $messageType = "success";
        }
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int)($_POST['course_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Course deleted.";
        $messageType = "success";
    }
}

// Fetch courses with enrollment count
$stmt = $conn->query("
    SELECT c.*,
           (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS student_count
    FROM courses c
    ORDER BY c.created_at DESC
");
$courses = $stmt->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Manage Courses - mk-students";
require_once "../includes/header.php";
?>

<style>
    .crs-wrap { max-width: 900px; margin: 22px auto 40px; padding: 0 20px; }

    .crs-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .crs-back:hover { color: #fff; transform: translateX(-3px); }

    .crs-header { margin-bottom: 16px; }
    .crs-header h1 {
        font-size: 24px !important;
        font-weight: 800 !important;
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
    }
    .alert.success { background: rgba(74,222,128,0.15); color: #86efac; border: 1.5px solid rgba(74,222,128,0.35); }
    .alert.error   { background: rgba(239,68,68,0.15); color: #ff9b9b; border: 1.5px solid rgba(239,68,68,0.35); }

    /* Create form */
    .crs-create-card {
        background: rgba(255,255,255,0.98);
        border-radius: 18px;
        padding: 22px 26px 26px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        margin-bottom: 20px;
    }
    .crs-create-card h3 {
        font-size: 16px; color: #17213c; margin: 0 0 16px;
        display: flex; align-items: center; gap: 10px; font-weight: 700;
    }
    .crs-create-card h3 i { color: #667eea; }

    /* Fields — each field stacked, full width */
    .crs-field {
        margin-bottom: 14px;
        width: 100%;
    }
    .crs-field label {
        display: block; font-size: 11.5px; font-weight: 700;
        color: #555; text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .crs-field input,
    .crs-field textarea,
    .crs-field select {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        padding: 12px 15px;
        border: 2px solid #e1e5eb; border-radius: 11px;
        font-size: 14px; font-family: inherit;
        background: #f8faff; color: #27324d;
        outline: none; transition: 0.22s; resize: vertical;
        box-sizing: border-box;
    }
    .crs-field input:focus,
    .crs-field textarea:focus,
    .crs-field select:focus {
        border-color: #667eea; background: #fff;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
    }
    .crs-field textarea { min-height: 80px; line-height: 1.55; }

    /* Two-column row (Teacher | Duration) */
    .crs-row-2 {
        display: flex;
        gap: 14px;
        margin-bottom: 14px;
    }
    .crs-row-2 .crs-field {
        flex: 1;
        margin-bottom: 0;
    }

    .btn-primary-crs {
        width: 100%;
        padding: 14px 24px; border-radius: 11px;
        font-size: 14px; font-weight: 700;
        cursor: pointer; border: none; font-family: inherit;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 8px; transition: 0.25s;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 10px 25px rgba(102,126,234,0.4);
        margin-top: 8px;
    }
    .btn-primary-crs:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(102,126,234,0.55);
    }

    /* Course list — 3 columns */
    .crs-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }
    .crs-item {
        background: rgba(255,255,255,0.98);
        border-radius: 14px;
        padding: 18px 20px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        transition: 0.25s;
        border-top: 4px solid #667eea;
        display: flex;
        flex-direction: column;
    }
    .crs-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 22px 55px rgba(102,126,234,0.35);
    }
    .crs-item-level {
        display: inline-block;
        padding: 3px 10px; border-radius: 20px;
        font-size: 10px; font-weight: 800;
        letter-spacing: 1px; text-transform: uppercase;
        margin-bottom: 10px; align-self: flex-start;
    }
    .crs-item-level.Beginner     { background: #d4f5e0; color: #167b3a; }
    .crs-item-level.Intermediate { background: #fff4e6; color: #c46a12; }
    .crs-item-level.Advanced     { background: #ffe0e0; color: #b42318; }
    .crs-item h4 {
        font-size: 15.5px; font-weight: 700;
        color: #17213c; margin: 0 0 8px;
        letter-spacing: -0.2px;
    }
    .crs-item .desc {
        color: #666; font-size: 13px; line-height: 1.55;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .crs-meta {
        display: flex; gap: 12px; flex-wrap: wrap;
        font-size: 11.5px; color: #888;
        margin-bottom: 12px; padding-bottom: 12px;
        border-bottom: 1px solid #f0f2f8;
    }
    .crs-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .crs-meta i { color: #667eea; }

    .crs-actions { display: flex; gap: 6px; margin-top: auto; }
    .crs-actions a,
    .crs-actions button {
        flex: 1; padding: 8px 10px;
        border-radius: 8px; font-size: 11.5px;
        font-weight: 700; font-family: inherit;
        text-decoration: none; cursor: pointer;
        display: inline-flex; align-items: center;
        justify-content: center; gap: 5px;
        border: none; transition: 0.22s;
    }
    .crs-actions .btn-view { background: #eef2ff; color: #4b5ecb; }
    .crs-actions .btn-view:hover { background: #dde4ff; }
    .crs-actions .btn-edit { background: #fff4e6; color: #c46a12; }
    .crs-actions .btn-edit:hover { background: #ffe6c7; }
    .crs-actions .btn-del { background: #ffecec; color: #c9302c; }
    .crs-actions .btn-del:hover { background: #ffd6d6; }

    .crs-empty {
        text-align: center; padding: 60px 20px;
        background: rgba(255,255,255,0.98);
        border-radius: 16px; color: #888;
        grid-column: 1 / -1;
    }
    .crs-empty i { font-size: 54px; color: #d0d6e3; display: block; margin-bottom: 14px; }
    .crs-empty p { font-size: 15px; }

    @media (max-width: 900px) {
        .crs-list { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .crs-list { grid-template-columns: 1fr; }
        .crs-row-2 { flex-direction: column; }
    }
</style>

<main class="crs-wrap">

    <a href="dashboard.php" class="crs-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="crs-header">
        <div>
            <h1>📚 Manage Courses</h1>
            <p>Create, edit, and manage all courses on the platform</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- CREATE FORM -->
    <div class="crs-create-card">
        <h3><i class="fa-solid fa-plus-circle"></i> New Course</h3>
        <form method="POST" action="courses.php">
    <input type="hidden" name="action" value="create">

    <div class="crs-field">
        <label>Course Title *</label>
        <input type="text" name="title" required maxlength="200"
               placeholder="e.g. Introduction to Web Development">
    </div>

    <div class="crs-field">
        <label>Description *</label>
        <textarea name="description" required
                  placeholder="Describe what students will learn..."></textarea>
    </div>

    <div class="crs-row-2">
        <div class="crs-field">
            <label>Teacher *</label>
            <input type="text" name="teacher" required maxlength="100"
                   placeholder="e.g. Mohammed MK">
        </div>

        <div class="crs-field">
            <label>Duration</label>
            <input type="text" name="duration" maxlength="50"
                   placeholder="e.g. 8 weeks">
        </div>
    </div>

    <div class="crs-field">
        <label>Level</label>
        <select name="level">
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
        </select>
    </div>

    <button type="submit" class="btn-primary-crs">
        <i class="fa-solid fa-paper-plane"></i> Publish Course
    </button>
</form>
    </div>

    <!-- LIST -->
    <div class="crs-list">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $c): ?>
                <div class="crs-item">
                    <span class="crs-item-level <?php echo $c['level']; ?>">
                        <?php echo htmlspecialchars($c['level']); ?>
                    </span>

                    <h4><?php echo htmlspecialchars($c['title']); ?></h4>
                    <p class="desc"><?php echo htmlspecialchars($c['description']); ?></p>

                    <div class="crs-meta">
                        <span><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($c['teacher']); ?></span>
                        <?php if (!empty($c['duration'])): ?>
                            <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?></span>
                        <?php endif; ?>
                        <span><i class="fa-solid fa-users"></i> <?php echo $c['student_count']; ?> enrolled</span>
                    </div>

                    <div class="crs-actions">
                        <a href="course-students.php?id=<?php echo $c['id']; ?>" class="btn-view">
                            <i class="fa-solid fa-users"></i> Students
                        </a>
                        <a href="course-edit.php?id=<?php echo $c['id']; ?>" class="btn-edit">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="courses.php" style="flex:1;display:flex;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                            <button type="submit" class="btn-del" style="width:100%;"
                                    onclick="return confirm('Delete this course? All enrollments will be removed too.');">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="crs-empty">
                <i class="fa-solid fa-book-open"></i>
                <p>No courses yet. Create the first one above! ✨</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>