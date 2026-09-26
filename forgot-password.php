<?php
session_start();
require_once 'config/database.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim($_POST['email'] ?? '');
    $note    = trim($_POST['message'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        // Check if a pending request already exists for this email
        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND status = 'pending' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $message = "You already have a pending request. The admin will respond soon.";
            $messageType = "warning";
        } else {
            // Save the request
            $stmt = $conn->prepare("INSERT INTO password_resets (email, message) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $note);

            if ($stmt->execute()) {
                $message = "✅ Request sent! The admin will reset your password and contact you soon.";
                $messageType = "success";
            } else {
                $message = "Something went wrong. Please try again.";
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MK Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 15%, rgba(102, 126, 234, 0.35), transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(118, 75, 162, 0.4), transparent 45%),
                linear-gradient(135deg, #1e2340 0%, #2d1b4e 50%, #1e2340 100%);
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #fff;
            max-width: 500px;
            width: 100%;
            border-radius: 24px;
            box-shadow: 0 40px 90px rgba(0,0,0,0.55);
            overflow: hidden;
            animation: fadeUp 0.65s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 30px 30px 26px;
            color: #fff;
            text-align: center;
        }
        .icon-circle {
            width: 72px; height: 72px;
            margin: 0 auto 14px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 1.5px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            backdrop-filter: blur(10px);
        }
        .card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .card-header p {
            font-size: 13px;
            opacity: 0.9;
        }
        .card-body { padding: 30px; }

        .alert {
            padding: 13px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert.success  { background: #edfff4; color: #167b3a; border-left: 4px solid #28a745; }
        .alert.error    { background: #fff1f1; color: #c0392b; border-left: 4px solid #c0392b; }
        .alert.warning  { background: #fff8e6; color: #b8860b; border-left: 4px solid #f0ad4e; }

        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            margin-bottom: 7px;
            color: #555;
            font-weight: 600;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .field input,
        .field textarea {
            width: 100%;
            padding: 13px 15px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            background: #f8faff;
            color: #27324d;
            transition: 0.25s;
            outline: none;
            resize: vertical;
        }
        .field input:focus,
        .field textarea:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
        }
        .field textarea { min-height: 80px; }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            box-shadow: 0 12px 28px rgba(102,126,234,0.35);
            transition: 0.25s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 36px rgba(102,126,234,0.5);
        }
        .btn-outline {
            display: block;
            text-align: center;
            padding: 12px;
            background: #fff;
            border: 1.5px solid #e1e5eb;
            color: #555;
            text-decoration: none;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            margin-top: 12px;
            transition: 0.22s;
        }
        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .info-text {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-top: 20px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div class="icon-circle">🔐</div>
            <h1>Forgot Your Password?</h1>
            <p>Send a request — the admin will reset it for you</p>
        </div>
        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot-password.php">
                <div class="field">
                    <label>Your Account Email</label>
                    <input type="email" name="email" required
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="field">
                    <label>Message to Admin (optional)</label>
                    <textarea name="message" placeholder="Add anything the admin should know…"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn">📨 Send Reset Request</button>
            </form>

            <a href="index.php" class="btn-outline">← Back to Login</a>

            <p class="info-text">
                The admin will receive your request and send you a temporary password.
            </p>
        </div>
    </div>
</body>
</html>