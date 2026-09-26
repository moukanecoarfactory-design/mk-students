<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Fetch user data first
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Validation
    if (empty($password)) {
        $message = "Please enter your password to confirm.";
        $messageType = "error";
    } elseif ($confirm !== 'DELETE') {
        $message = "Please type DELETE in the confirmation box.";
        $messageType = "error";
    } elseif (!password_verify($password, $user['password'])) {
        $message = "Password is incorrect.";
        $messageType = "error";
    } else {
        // ✅ All checks passed — delete the account

        // 1. Delete user's skills
        $stmt = $conn->prepare("DELETE FROM skills WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 2. Delete profile picture file (if not the default)
        if (!empty($user['profile_picture']) 
            && $user['profile_picture'] !== 'default-avatar.png'
            && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
            @unlink("../uploads/profiles/" . $user['profile_picture']);
        }

        // 3. Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();

        // 4. Destroy session and redirect
        session_unset();
        session_destroy();
        header("Location: ../index.php?deleted=1");
        exit();
    }
}

$pageTitle = "Delete Account - mk-students";
require_once "../includes/header.php";
?>

<main class="page-container" style="max-width: 600px; margin: 40px auto; padding: 0 20px;">

    <a href="profile.php" class="back-link" style="display:inline-flex;align-items:center;gap:8px;color:#5e7ce9;text-decoration:none;font-weight:600;font-size:14px;margin-bottom:20px;">
        ← Back to Profile
    </a>

    <div class="dash-card" style="background:#fff;border-radius:20px;padding:35px;box-shadow:0 10px 30px rgba(45,58,100,0.08);">

        <div style="text-align:center;margin-bottom:25px;">
            <div style="width:70px;height:70px;margin:0 auto 15px;border-radius:50%;background:#ffecec;display:flex;align-items:center;justify-content:center;font-size:32px;">
                🗑️
            </div>
            <h1 style="font-size:24px;color:#17213c;margin-bottom:8px;">Delete My Account</h1>
            <p style="color:#888;font-size:14px;">This action cannot be undone.</p>
        </div>

        <?php if ($message): ?>
            <div style="padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;
                <?php echo $messageType === 'error' 
                    ? 'background:#fff1f1;border:1px solid #ffd3d3;color:#d93030;' 
                    : 'background:#edfff4;border:1px solid #c8f0d7;color:#208348;'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div style="background:#fff8e6;border:1px solid #ffe0a3;padding:16px 18px;border-radius:12px;margin-bottom:24px;">
            <strong style="color:#b8860b;display:block;margin-bottom:6px;">⚠️ Warning</strong>
            <ul style="margin:0;padding-left:20px;color:#7a5c00;font-size:13.5px;line-height:1.7;">
                <li>Your account will be permanently deleted</li>
                <li>All your skills and data will be removed</li>
                <li>Your profile picture will be deleted</li>
                <li>You will be logged out immediately</li>
                <li><strong>This cannot be reversed</strong></li>
            </ul>
        </div>

        <form method="POST" action="delete-account.php">

            <div style="margin-bottom:18px;">
                <label style="display:block;margin-bottom:6px;color:#555;font-weight:600;font-size:14px;">
                    Enter your password
                </label>
                <input type="password" name="password" required
                       placeholder="Your current password"
                       style="width:100%;padding:12px 15px;border:2px solid #e1e5eb;border-radius:10px;font-size:14px;">
            </div>

            <div style="margin-bottom:18px;">
    <label style="display:block;margin-bottom:6px;color:#555;font-weight:600;font-size:14px;">
        Enter your password
    </label>
    <div class="pwd-wrap">
        <input type="password" name="password" required
               placeholder="Your current password"
               style="width:100%;padding:12px 15px;border:2px solid #e1e5eb;border-radius:10px;font-size:14px;">
        <button type="button" class="pwd-toggle" onclick="togglePassword(this)">👁️</button>
    </div>
</div>

            <div style="display:flex;gap:12px;">
                <a href="profile.php"
                   style="flex:1;padding:14px;border-radius:10px;border:1.5px solid #e1e5eb;color:#555;font-weight:600;text-align:center;text-decoration:none;font-size:14px;">
                    Cancel
                </a>
                <button type="submit"
                        style="flex:1;padding:14px;border-radius:10px;border:none;background:#d93030;color:#fff;font-weight:600;cursor:pointer;font-size:14px;">
                    🗑️ Delete Forever
                </button>
            </div>

        </form>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>