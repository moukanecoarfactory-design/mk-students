<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $body  = trim($_POST['body'] ?? '');

        if (empty($title) || empty($body)) {
            $message = "Please fill in both title and body.";
            $messageType = "error";
        } else {
            $stmt = $conn->prepare("INSERT INTO announcements (title, body, author_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $title, $body, $adminId);
            $stmt->execute();
            $stmt->close();
            $message = "Announcement published successfully!";
            $messageType = "success";
        }
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int)($_POST['announcement_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $message = "Announcement deleted.";
        $messageType = "success";
    }

    // Redirect to avoid form resubmission
    if (empty($message) || $messageType === 'success') {
        // Don't redirect — keep the success message visible
    }
}

// Fetch all announcements with author name
$stmt = $conn->query("
    SELECT a.*, u.first_name, u.last_name 
    FROM announcements a 
    LEFT JOIN users u ON a.author_id = u.id 
    ORDER BY a.created_at DESC
");
$announcements = $stmt->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Announcements - mk-students";
require_once "../includes/header.php";
?>

<style>
    .ann-wrap { max-width: 900px; margin: 30px auto 50px; padding: 0 20px; }
    .ann-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .ann-back:hover { color: #fff; transform: translateX(-3px); }

    .ann-header {
        display: flex; justify-content: space-between;
        align-items: flex-end; flex-wrap: wrap; gap: 14px;
        margin-bottom: 22px;
    }
    .ann-header h1 {
    font-size: 28px; font-weight: 800; color: #ffffff !important;
    margin: 0 0 4px; letter-spacing: -0.5px;
}
.ann-header p {
    color: rgba(255,255,255,0.6) !important;
    font-size: 14px; margin: 0;
}
    .btn {
        padding: 11px 20px;
        border-radius: 11px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        border: none;
        transition: 0.25s;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px rgba(102, 126, 234, 0.55);
    }

    .alert {
        padding: 14px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 600;
    }
    .alert.success { background: rgba(74,222,128,0.15); color: #86efac; border: 1.5px solid rgba(74,222,128,0.35); }
    .alert.error   { background: rgba(239,68,68,0.15); color: #ff9b9b; border: 1.5px solid rgba(239,68,68,0.35); }

    /* Create form card */
    .create-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 28px 32px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        margin-bottom: 26px;
    }
    .create-card h3 {
        font-size: 17px;
        color: #17213c;
        margin: 0 0 18px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
    }
    .create-card h3 i {
        color: #667eea;
        font-size: 18px;
    }

    .form-field { margin-bottom: 16px; }
    .form-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .form-field input,
    .form-field textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e5eb;
        border-radius: 11px;
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
    .form-field textarea {
        min-height: 100px;
        line-height: 1.6;
    }

    /* List */
    .ann-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .ann-item {
        background: rgba(255,255,255,0.98);
        border-radius: 16px;
        padding: 22px 26px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        animation: fadeUp 0.5s ease both;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(15px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .ann-item-head {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 10px;
        flex-wrap: wrap;
        align-items: flex-start;
    }
    .ann-item-head h4 {
        font-size: 17px;
        font-weight: 700;
        color: #17213c;
        margin: 0 0 6px;
        letter-spacing: -0.2px;
    }
    .ann-meta {
        display: flex;
        gap: 14px;
        font-size: 12px;
        color: #888;
        flex-wrap: wrap;
    }
    .ann-meta i { color: #667eea; margin-right: 4px; }

    .ann-body {
        color: #555;
        font-size: 14px;
        line-height: 1.7;
        margin-bottom: 12px;
        white-space: pre-line;
    }

    .ann-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .ann-btn {
        width: 34px; height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: 0.22s;
        font-family: inherit;
    }
    .ann-btn.edit {
        background: #fff4e6;
        color: #c46a12;
    }
    .ann-btn.edit:hover { background: #ffe6c7; transform: scale(1.08); }
    .ann-btn.del {
        background: #ffecec;
        color: #c9302c;
    }
    .ann-btn.del:hover { background: #ffd6d6; transform: scale(1.08); }

    .empty {
        text-align: center;
        padding: 50px 20px;
        background: rgba(255,255,255,0.98);
        border-radius: 16px;
        color: #888;
    }
    .empty i {
        font-size: 50px;
        color: #d0d6e3;
        display: block;
        margin-bottom: 14px;
    }
    .empty p { font-size: 15px; }
</style>

<main class="ann-wrap">

    <a href="dashboard.php" class="ann-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="ann-header">
    <div>
        <h1 style="color:#ffffff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.4);">📢 Announcements</h1>
        <p style="color:rgba(255,255,255,0.65) !important;">Post updates, news, and info to all users</p>
    </div>
</div>

    <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- CREATE FORM -->
    <div class="create-card">
        <h3><i class="fa-solid fa-plus-circle"></i> New Announcement</h3>
        <form method="POST" action="announcements.php">
            <input type="hidden" name="action" value="create">

            <div class="form-field">
                <label>Title</label>
                <input type="text" name="title" required
                       placeholder="e.g. 📢 New course available!"
                       maxlength="200">
            </div>

            <div class="form-field">
                <label>Message</label>
                <textarea name="body" required
                          placeholder="Write the announcement content here..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Publish Announcement
            </button>
        </form>
    </div>

    <!-- LIST -->
    <div class="ann-list">
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $a): ?>
                <div class="ann-item">
                    <div class="ann-item-head">
                        <div>
                            <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                            <div class="ann-meta">
                                <span>
                                    <i class="fa-solid fa-user"></i>
                                    <?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?>
                                </span>
                                <span>
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo date('M d, Y · H:i', strtotime($a['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="ann-actions">
                            <a href="announcement-edit.php?id=<?php echo $a['id']; ?>" 
                               class="ann-btn edit" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="POST" action="announcements.php" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="announcement_id" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="ann-btn del" title="Delete"
                                        onclick="return confirm('Delete this announcement?');">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="ann-body"><?php echo nl2br(htmlspecialchars($a['body'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">
                <i class="fa-regular fa-bell-slash"></i>
                <p>No announcements yet. Create the first one above! ✨</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>