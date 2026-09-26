<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$id      = (int)($_GET['id'] ?? 0);
$message = '';
$messageType = '';

if ($id <= 0) {
    header("Location: courses.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ?, teacher = ?, duration = ?, level = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $description, $teacher, $duration, $level, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: courses.php?success=updated");
        exit();
    }
}

// Fetch the course
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: courses.php");
    exit();
}

$pageTitle = "Edit Course - mk-students";
require_once "../includes/header.php";
?>

<style>
    .ce-wrap { max-width: 760px; margin: 24px auto 40px; padding: 0 20px; }
    .ce-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .ce-back:hover { color: #fff; transform: translateX(-3px); }

    .ce-card {
        background: rgba(255,255,255,0.98);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        animation: fadeUp 0.5s ease both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .ce-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 22px 30px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .ce-header .h-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        border: 1.5px solid rgba(255,255,255,0.35);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .ce-header h1 {
        font-size: 20px; font-weight: 700; margin: 0;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }
    .ce-header p { font-size: 12.5px; opacity: 0.9; margin: 2px 0 0; }

    .ce-body { padding: 26px 30px 30px; }

    .ce-alert {
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 500;
    }
    .ce-alert.error { background: #fff1f1; color: #c0392b; border-left: 4px solid #c0392b; }

    .ce-field { margin-bottom: 16px; width: 100%; }
    .ce-field label {
        display: block; font-size: 12px; font-weight: 700;
        color: #555; text-transform: uppercase; letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .ce-field input,
    .ce-field textarea,
    .ce-field select {
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
    .ce-field input:focus,
    .ce-field textarea:focus,
    .ce-field select:focus {
        border-color: #667eea; background: #fff;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
    }
    .ce-field textarea { min-height: 100px; line-height: 1.6; }

    .ce-row {
        display: flex;
        gap: 14px;
        margin-bottom: 16px;
    }
    .ce-row .ce-field { flex: 1; margin-bottom: 0; }

    .ce-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #eef1f8;
    }

    .ce-btn {
        padding: 13px 24px;
        border-radius: 11px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-family: inherit;
        transition: 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .ce-btn-outline {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .ce-btn-outline:hover {
        border-color: #667eea; color: #667eea;
        transform: translateY(-2px);
    }
    .ce-btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 12px 28px rgba(102,126,234,0.4);
    }
    .ce-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(102,126,234,0.55);
    }

    @media (max-width: 600px) {
        .ce-header { padding: 20px 22px; }
        .ce-body { padding: 22px 22px 26px; }
        .ce-row { flex-direction: column; }
        .ce-actions { flex-direction: column-reverse; }
        .ce-btn { width: 100%; justify-content: center; }
    }
</style>

<main class="ce-wrap">

    <a href="courses.php" class="ce-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Courses
    </a>

    <div class="ce-card">

        <div class="ce-header">
            <div class="h-icon"><i class="fa-solid fa-pen"></i></div>
            <div>
                <h1>Edit Course</h1>
                <p>Update the course details</p>
            </div>
        </div>

        <div class="ce-body">

            <?php if ($message): ?>
                <div class="ce-alert <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="course-edit.php?id=<?php echo $id; ?>">

                <div class="ce-field">
                    <label>Course Title *</label>
                    <input type="text" name="title" required maxlength="200"
                           value="<?php echo htmlspecialchars($course['title']); ?>">
                </div>

                <div class="ce-field">
                    <label>Description *</label>
                    <textarea name="description" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>

                <div class="ce-row">
                    <div class="ce-field">
                        <label>Teacher *</label>
                        <input type="text" name="teacher" required maxlength="100"
                               value="<?php echo htmlspecialchars($course['teacher']); ?>">
                    </div>

                    <div class="ce-field">
                        <label>Duration</label>
                        <input type="text" name="duration" maxlength="50"
                               value="<?php echo htmlspecialchars($course['duration'] ?? ''); ?>">
                    </div>
                </div>

                <div class="ce-field">
                    <label>Level</label>
                    <select name="level">
                        <?php foreach (['Beginner','Intermediate','Advanced'] as $lvl): ?>
                            <option value="<?php echo $lvl; ?>" <?php echo $course['level'] === $lvl ? 'selected' : ''; ?>>
                                <?php echo $lvl; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ce-actions">
                    <a href="courses.php" class="ce-btn ce-btn-outline">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="ce-btn ce-btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>

            </form>

        </div>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>