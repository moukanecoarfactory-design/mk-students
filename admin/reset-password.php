<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$email        = trim($_GET['email'] ?? '');
$requestId    = (int)($_GET['request_id'] ?? 0);
$message      = '';
$messageType  = '';
$tempPassword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $requestId = (int)($_POST['request_id'] ?? 0);

    if (empty($email)) {
        $message = "Email is required.";
        $messageType = "error";
    } else {
        // Check user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $userRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$userRow) {
            $message = "No account found with that email.";
            $messageType = "error";
        } else {
            // Generate a random 8-character password
            $tempPassword = bin2hex(random_bytes(4)); // 8 hex chars
            $hashed = password_hash($tempPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ?, must_change_password = 1 WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userRow['id']);
            $stmt->execute();
            $stmt->close();

            // Mark request as resolved
            if ($requestId > 0) {
                $stmt = $conn->prepare("UPDATE password_resets SET status = 'resolved' WHERE id = ?");
                $stmt->bind_param("i", $requestId);
                $stmt->execute();
                $stmt->close();
            }

            $message = "Password reset successfully! Share this temporary password with the user:";
            $messageType = "success";
        }
    }
}

$pageTitle = "Reset Password - mk-students";
require_once "../includes/header.php";
?>
<style>
    .rp-wrap { max-width: 560px; margin: 30px auto 40px; padding: 0 20px; }
    .rp-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: #a8b8ff; text-decoration: none; font-weight: 600;
        font-size: 13.5px; margin-bottom: 16px; transition: 0.2s;
    }
    .rp-back:hover { color: #fff; transform: translateX(-3px); }

    .rp-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.4);
        overflow: hidden;
    }
    .rp-header {
        background: linear-gradient(135deg, #667eea, #764ba2);
        padding: 24px 30px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .rp-header .h-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: rgba(255,255,255,0.2);
        border: 1.5px solid rgba(255,255,255,0.35);
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
    }
    .rp-header h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .rp-header p  { font-size: 12.5px; opacity: 0.9; margin: 2px 0 0; }

    .rp-body { padding: 28px 30px 32px; }

    .rp-alert {
        padding: 14px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 500;
    }
    .rp-alert.success { background: #edfff4; color: #167b3a; border-left: 4px solid #28a745; }
    .rp-alert.error   { background: #fff1f1; color: #c0392b; border-left: 4px solid #c0392b; }

    .rp-password-box {
        background: linear-gradient(135deg, #f8faff, #eef2ff);
        border: 2px dashed #c7d2ff;
        border-radius: 14px;
        padding: 22px;
        margin-top: 16px;
        text-align: center;
    }
    .rp-password-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #888;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .rp-password-value {
        font-family: 'Courier New', monospace;
        font-size: 28px;
        font-weight: 800;
        color: #4b5ecb;
        letter-spacing: 3px;
        background: #fff;
        padding: 12px 18px;
        border-radius: 10px;
        display: inline-block;
        border: 2px solid #c7d2ff;
        margin-bottom: 14px;
        user-select: all;
    }
    .rp-copy {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        transition: 0.22s;
    }
    .rp-copy:hover { transform: translateY(-2px); }
    .rp-copy.copied { background: #10b981; }

    .rp-field { margin-bottom: 16px; }
    .rp-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }
    .rp-field input {
        width: 100%;
        padding: 13px 15px;
        border: 2px solid #e1e5eb;
        border-radius: 12px;
        font-size: 14px;
        font-family: inherit;
        background: #f8faff;
        transition: 0.22s;
        outline: none;
    }
    .rp-field input:focus {
        border-color: #667eea;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(102,126,234,0.12);
    }

    .rp-btn {
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        border: none;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
        box-shadow: 0 12px 28px rgba(102,126,234,0.35);
        transition: 0.25s;
    }
    .rp-btn:hover { transform: translateY(-3px); box-shadow: 0 18px 36px rgba(102,126,234,0.5); }

    .rp-note {
        font-size: 12.5px;
        color: #888;
        text-align: center;
        margin-top: 20px;
        line-height: 1.6;
    }
</style>

<main class="rp-wrap">

    <a href="reset-requests.php" class="rp-back">
        <i class="fa-solid fa-arrow-left"></i> Back to Requests
    </a>

    <div class="rp-card">

        <div class="rp-header">
            <div class="h-icon"><i class="fa-solid fa-key"></i></div>
            <div>
                <h1>Reset User Password</h1>
                <p>Generate a new temporary password</p>
            </div>
        </div>

        <div class="rp-body">

            <?php if ($message): ?>
                <div class="rp-alert <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($tempPassword): ?>
                <div class="rp-password-box">
                    <div class="rp-password-label">📌 Temporary Password</div>
                    <div class="rp-password-value" id="tempPwd"><?php echo htmlspecialchars($tempPassword); ?></div>
                    <br>
                    <button type="button" class="rp-copy" onclick="copyPwd(this)">📋 Copy Password</button>
                    <div class="rp-note" style="margin-top:14px;">
                        ⚠️ Send this to the user <strong>privately</strong> (WhatsApp, DM, etc.).<br>
                        They'll be forced to change it on next login.
                    </div>
                </div>

                <a href="reset-requests.php" style="display:block;text-align:center;margin-top:20px;padding:12px;border:1.5px solid #e1e5eb;border-radius:12px;color:#555;text-decoration:none;font-weight:600;font-size:13.5px;">
                    ← Back to Requests
                </a>
            <?php else: ?>
                <form method="POST" action="reset-password.php">
                    <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">

                    <div class="rp-field">
                        <label>User's Email</label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="user@example.com">
                    </div>

                    <button type="submit" class="rp-btn">
                        🔑 Generate New Password
                    </button>
                </form>

                <p class="rp-note">
                    A random 8-character password will be created and the user<br>
                    will be required to change it after login.
                </p>
            <?php endif; ?>

        </div>

    </div>

</main>

<script>
    function copyPwd(btn) {
        const txt = document.getElementById('tempPwd').textContent;
        navigator.clipboard.writeText(txt).then(() => {
            btn.textContent = '✅ Copied!';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.textContent = '📋 Copy Password';
                btn.classList.remove('copied');
            }, 2000);
        });
    }
</script>

<?php require_once "../includes/footer.php"; ?>