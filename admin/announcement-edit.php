<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId = $_SESSION['user_id'];
$id      = (int)($_GET['id'] ?? 0);
$message = '';
$messageType = '';

if ($id <= 0) {
    header("Location: announcements.php");
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body  = trim($_POST['body'] ?? '');

    if (empty($title) || empty($body)) {
        $message = "Please fill in both title and body.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, body = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $body, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: announcements.php?success=updated");
        exit();
    }
}

// Fetch the announcement
$stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$ann = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$ann) {
    header("Location: announcements.php");
    exit();
}

$pageTitle = "Edit Announcement - mk-students";
require_once "../includes/header.php";
?>

<style>
    .edit-wrap { max-width: 720px; margin: 30px auto 50px; padding: 0 20px; }
    .edit-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .edit-back:hover { color: #fff; transform: translateX(-3px); }

    .edit-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        animation: fadeUp 0.6s ease both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .edit-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 24px 30px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .edit-header .h-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        border: 1.5px solid rgba(255,255,255,0.35);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .edit-header h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .edit-header p  { font-size: 12.5px; opacity: 0.9; margin: 2px 0 0; }

    .edit-body { padding: 28px 30px 32px; }

    .alert {
        padding: 13px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 500;
    }
    .alert.error { background: #fff1f1; color: #c0392b; border-left: 4px solid #c0392b; }

    .form-field { margin-bottom: 18px; }
    .form-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }
    .form-field input,
    .form-field textarea {
        width: 100%;
        padding: 13px 16px;
        border: 2px solid #e1e5eb;
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        background: #f8faff;
        color: #27324d;
        outline: none;
        transition: 0.22s;
        resize: vertical;
    }
    .form-field input:focus,
    .form-field textarea:focus {
        border-color: #667eea;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
    }
    .form-field textarea { min-height: 140px; line-height: 1.6; }

    .edit-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 26px;
        padding-top: 22px;
        border-top: 1px solid #eef1f8;
    }

    .btn {
        padding: 13px 24px;
        border-radius: 12px;
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
    .btn-outline {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .btn-outline:hover {
        border-color: #667eea;
        color: #667eea;
        transform: translateY(-2px);
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 12px 28px rgba(102,126,234,0.4);
    }
    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(102,126,234,0.55);
    }

    @media (max-width: 600px) {
        .edit-header { padding: 20px 22px; }
        .edit-body { padding: 22px 22px 26px; }
        .edit-actions { flex-direction: column-reverse; }
        .btn { width: 100%; justify-content: center; }
    }
</style>

<main class="edit-wrap">

    <a href="announcements.php" class="edit-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Announcements
    </a>

    <div class="edit-card">

        <div class="edit-header">
            <div class="h-icon"><i class="fa-solid fa-pen"></i></div>
            <div>
                <h1>Edit Announcement</h1>
                <p>Update the title or content</p>
            </div>
        </div>

        <div class="edit-body">

            <?php if ($message): ?>
                <div class="alert <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="announcement-edit.php?id=<?php echo $id; ?>">

                <div class="form-field">
                    <label>Title</label>
                    <input type="text" name="title" required maxlength="200"
                           value="<?php echo htmlspecialchars($ann['title']); ?>">
                </div>

                <div class="form-field">
                    <label>Message</label>
                    <textarea name="body" required><?php echo htmlspecialchars($ann['body']); ?></textarea>
                </div>

                <div class="edit-actions">
                    <a href="announcements.php" class="btn btn-outline">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>

            </form>

        </div>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>