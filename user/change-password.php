<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    // Basic validation
    if (empty($current) || empty($new) || empty($confirm)) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (strlen($new) < 8) {
        $message = "New password must be at least 8 characters.";
        $messageType = "error";
    } elseif ($new !== $confirm) {
        $message = "New passwords do not match.";
        $messageType = "error";
    } else {
        // Fetch the user's current hashed password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($current, $row['password'])) {
            $message = "Current password is incorrect.";
            $messageType = "error";
        } else {
            // Hash the new password and update
            $hashed = password_hash($new, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $userId);
            $stmt->execute();
            $stmt->close();

            $message = "Password changed successfully!";
            $messageType = "success";
        }
    }
}

$pageTitle = "Change Password - mk-students";
require_once "../includes/header.php";
?>

<main class="page-container">

    <a href="dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="dash-card" style="max-width: 520px; margin: 0 auto;">

        <div class="card-top">
            <h2><i class="fa-solid fa-lock"></i> Change Password</h2>
        </div>

        <?php if ($message): ?>
            <div class="dashboard-alert <?php echo $messageType === 'success' ? 'success' : ''; ?>"
                 style="<?php echo $messageType === 'error' ? 'background:#fff1f1;border:1px solid #ffd3d3;color:#d93030;' : ''; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="change-password.php" style="padding: 10px 0;">

            <div class="form-group" style="margin-bottom: 18px;">
                <label style="display:block;margin-bottom:6px;color:#555;font-weight:600;">Current Password</label>
                <div class="pwd-wrap">
                    <input type="password" name="current_password" required
                           style="width:100%;padding:12px 15px;border:2px solid #e1e5eb;border-radius:10px;font-size:14px;">
                    <button type="button" class="pwd-toggle" onclick="togglePassword(this)">👁️</button>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
                <label style="display:block;margin-bottom:6px;color:#555;font-weight:600;">New Password</label>
                <div class="pwd-wrap">
                    <input type="password" name="new_password" required minlength="8"
                           style="width:100%;padding:12px 15px;border:2px solid #e1e5eb;border-radius:10px;font-size:14px;">
                    <button type="button" class="pwd-toggle" onclick="togglePassword(this)">👁️</button>
                </div>
                <small style="color:#888;font-size:12.5px;">Minimum 8 characters</small>
            </div>

            <div class="form-group" style="margin-bottom: 22px;">
                <label style="display:block;margin-bottom:6px;color:#555;font-weight:600;">Confirm New Password</label>
                <div class="pwd-wrap">
                    <input type="password" name="confirm_password" required minlength="8"
                           style="width:100%;padding:12px 15px;border:2px solid #e1e5eb;border-radius:10px;font-size:14px;">
                    <button type="button" class="pwd-toggle" onclick="togglePassword(this)">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
                <i class="fa-solid fa-key"></i> Update Password
            </button>

        </form>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>