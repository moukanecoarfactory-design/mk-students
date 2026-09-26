<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId = $_SESSION['user_id'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $requestId = (int)($_POST['request_id'] ?? 0);

    if ($action === 'mark_resolved' && $requestId > 0) {
        $stmt = $conn->prepare("UPDATE password_resets SET status = 'resolved' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $stmt->close();
    }

    if ($action === 'delete' && $requestId > 0) {
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: reset-requests.php");
    exit();
}

// Fetch requests
$stmt = $conn->query("SELECT * FROM password_resets ORDER BY 
    CASE status WHEN 'pending' THEN 0 ELSE 1 END, 
    created_at DESC");
$requests = $stmt->fetch_all(MYSQLI_ASSOC);

$pendingCount = count(array_filter($requests, fn($r) => $r['status'] === 'pending'));

$pageTitle = "Reset Requests - mk-students";
require_once "../includes/header.php";
?>
<style>
    .rr-wrap { max-width: 900px; margin: 26px auto 40px; padding: 0 20px; }
    .rr-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .rr-back:hover { color: #fff; transform: translateX(-3px); }

    .rr-head { margin-bottom: 18px; }
    .rr-head h1 {
        font-size: 26px; font-weight: 800; color: #fff;
        margin: 0 0 4px; letter-spacing: -0.5px;
    }
    .rr-head p { color: rgba(255,255,255,0.55); font-size: 13.5px; margin: 0; }
    .rr-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f59e0b, #dc2626);
        color: #fff;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-left: 8px;
        vertical-align: middle;
        box-shadow: 0 4px 12px rgba(245,158,11,0.4);
    }

    .rr-card {
        background: rgba(255,255,255,0.98);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        overflow: hidden;
    }

    .rr-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 24px;
        border-bottom: 1px solid #eef1f8;
        transition: 0.2s;
    }
    .rr-row:last-child { border-bottom: none; }
    .rr-row:hover { background: #fafbff; }
    .rr-row.resolved { background: #f7fff9; opacity: 0.75; }

    .rr-info { flex: 1; min-width: 0; }
    .rr-email {
        font-weight: 700;
        color: #17213c;
        font-size: 14.5px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
        flex-wrap: wrap;
    }
    .rr-tag {
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }
    .rr-tag.pending  { background: #fff4e6; color: #c46a12; }
    .rr-tag.resolved { background: #eef8f1; color: #1f7a3c; }

    .rr-note {
        color: #555;
        font-size: 13px;
        margin-bottom: 4px;
        line-height: 1.5;
    }
    .rr-date {
        color: #999;
        font-size: 11.5px;
    }

    .rr-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .rr-btn {
        padding: 8px 14px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-family: inherit;
        transition: 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .rr-btn.reset    { background: #eef2ff; color: #4b5ecb; }
    .rr-btn.reset:hover { background: #dde4ff; }
    .rr-btn.resolve  { background: #eef8f1; color: #1f7a3c; }
    .rr-btn.resolve:hover { background: #d8f0e0; }
    .rr-btn.del      { background: #ffecec; color: #c9302c; }
    .rr-btn.del:hover { background: #ffd6d6; }

    .rr-empty {
        text-align: center;
        padding: 60px 20px;
        color: #888;
    }
    .rr-empty i { font-size: 48px; color: #d0d6e3; display: block; margin-bottom: 14px; }
    .rr-empty p { font-size: 15px; }

    @media (max-width: 700px) {
        .rr-row { flex-direction: column; align-items: stretch; }
        .rr-actions { justify-content: stretch; }
        .rr-btn { flex: 1; justify-content: center; }
    }
</style>

<main class="rr-wrap">

    <a href="dashboard.php" class="rr-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="rr-head">
        <h1>
            Password Reset Requests
            <?php if ($pendingCount > 0): ?>
                <span class="rr-badge"><?php echo $pendingCount; ?> pending</span>
            <?php endif; ?>
        </h1>
        <p>Users who forgot their password and need a temporary one</p>
    </div>

    <div class="rr-card">
        <?php if (count($requests) > 0): ?>
            <?php foreach ($requests as $r): ?>
                <div class="rr-row <?php echo $r['status'] === 'resolved' ? 'resolved' : ''; ?>">
                    <div class="rr-info">
                        <div class="rr-email">
                            <i class="fa-solid fa-envelope" style="color:#667eea;"></i>
                            <?php echo htmlspecialchars($r['email']); ?>
                            <span class="rr-tag <?php echo $r['status']; ?>">
                                <?php echo $r['status']; ?>
                            </span>
                        </div>

                        <?php if (!empty($r['message'])): ?>
                            <div class="rr-note">
                                💬 <?php echo nl2br(htmlspecialchars($r['message'])); ?>
                            </div>
                        <?php endif; ?>

                        <div class="rr-date">
                            <i class="fa-regular fa-clock"></i>
                            Requested on <?php echo date('M d, Y · H:i', strtotime($r['created_at'])); ?>
                        </div>
                    </div>

                    <div class="rr-actions">
                        <?php if ($r['status'] === 'pending'): ?>
                            <a href="reset-password.php?email=<?php echo urlencode($r['email']); ?>&request_id=<?php echo $r['id']; ?>"
                               class="rr-btn reset">
                                <i class="fa-solid fa-key"></i> Reset Password
                            </a>
                        <?php endif; ?>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                            <?php if ($r['status'] === 'pending'): ?>
                                <button type="submit" name="action" value="mark_resolved" class="rr-btn resolve">
                                    <i class="fa-solid fa-check"></i> Resolve
                                </button>
                            <?php endif; ?>
                            <button type="submit" name="action" value="delete" class="rr-btn del"
                                    onclick="return confirm('Delete this request?');">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="rr-empty">
                <i class="fa-solid fa-inbox"></i>
                <p>No password reset requests yet.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>