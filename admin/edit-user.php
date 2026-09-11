<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId = (int)$_POST['user_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $studyLevel = $_POST['study_level'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, study_level=?, status=? WHERE id=? AND role='user'");
    $stmt->bind_param("sssssi", $firstName, $lastName, $phone, $studyLevel, $status, $targetId);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php?success=user_updated");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mk-StudentHub</title>
    <!-- The PHP time() forces Chrome to clear the CSS cache -->
    <link rel="stylesheet" href="../css/style.css?v=<?php echo time(); ?>">
</head>
<body class="dashboard-body">
<!-- ... rest of your navbar and main content ... -->
<main class="page-container">
    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    <section class="card">
        <h1>Edit User: <?php echo htmlspecialchars($user['first_name']); ?></h1>
        <form action="edit-user.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Study Level</label>
                <select name="study_level" required>
    <option value="" disabled selected>
        Select study level
    </option>

    <option value="Bac">Bac</option>
    <option value="Bac+2">Bac+2</option>
    <option value="Bac+3">Bac+3</option>
    <option value="Bac+5">Bac+5</option>
</select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-actions">
                <a href="dashboard.php" class="button button-secondary">Cancel</a>
                <button type="submit" class="button button-primary">Update User</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>