<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetId   = (int)$_POST['user_id'];
    $firstName  = trim($_POST['first_name']);
    $lastName   = trim($_POST['last_name']);
    $phone      = trim($_POST['phone']);
    $studyLevel = $_POST['study_level'];
    $status     = $_POST['status'];

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

$pageTitle = "Edit User - mk-students";
require_once "../includes/header.php";
?>

<style>
    .edit-page {
        max-width: 720px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .edit-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #5e7ce9;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 22px;
        transition: 0.2s;
    }
    .edit-back:hover {
        color: #764ba2;
        transform: translateX(-3px);
    }

    .edit-card {
        background: #fff;
        border-radius: 22px;
        box-shadow: 0 20px 50px rgba(45, 58, 100, 0.12);
        overflow: hidden;
    }

    /* Gradient header */
    .edit-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 32px 40px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .edit-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .edit-header-text h1 {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 4px;
        letter-spacing: -0.3px;
    }
    .edit-header-text p {
        font-size: 13.5px;
        opacity: 0.9;
        margin: 0;
    }

    /* Form body */
    .edit-body {
        padding: 32px 40px 36px;
    }

    .edit-field {
        margin-bottom: 20px;
    }
    .edit-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #555;
        margin-bottom: 7px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .edit-field input,
    .edit-field select {
        width: 100%;
        padding: 13px 16px;
        border: 2px solid #e1e5eb;
        border-radius: 12px;
        font-size: 14.5px;
        font-family: inherit;
        color: #27324d;
        background: #f8faff;
        transition: 0.22s;
        box-sizing: border-box;
    }
    .edit-field input:focus,
    .edit-field select:focus {
        outline: none;
        border-color: #667eea;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12);
    }

    /* Status pill style */
    .status-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 6px;
    }
    .status-option {
        position: relative;
        cursor: pointer;
    }
    .status-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .status-option .status-box {
        padding: 14px 16px;
        border: 2px solid #e1e5eb;
        border-radius: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        transition: 0.22s;
        background: #f8faff;
        color: #555;
    }
    .status-option input:checked + .status-box.active-box {
        background: #eefdf3;
        border-color: #28a745;
        color: #167b3a;
    }
    .status-option input:checked + .status-box.inactive-box {
        background: #fff1f1;
        border-color: #d93030;
        color: #b42318;
    }

    /* Actions */
    .edit-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 24px;
        border-top: 1px solid #f0f2f5;
    }
    .edit-btn {
        padding: 13px 26px;
        border-radius: 12px;
        font-size: 14.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
        transition: 0.22s;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .edit-btn-cancel {
        background: #fff;
        border: 1.5px solid #e1e5eb;
        color: #555;
    }
    .edit-btn-cancel:hover {
        border-color: #667eea;
        color: #667eea;
    }
    .edit-btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 10px 22px rgba(102, 126, 234, 0.28);
    }
    .edit-btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(102, 126, 234, 0.38);
    }

    @media (max-width: 600px) {
        .edit-header { padding: 26px 24px; }
        .edit-body   { padding: 26px 24px; }
        .status-row  { grid-template-columns: 1fr; }
        .edit-actions{ flex-direction: column; }
        .edit-btn    { width: 100%; justify-content: center; }
    }
</style>

<main class="edit-page">

    <a href="dashboard.php" class="edit-back">← Back to Dashboard</a>

    <div class="edit-card">

        <!-- HEADER -->
        <div class="edit-header">
            <div class="edit-avatar">
                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
            </div>
            <div class="edit-header-text">
                <h1>Edit User</h1>
                <p><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> · <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <!-- FORM -->
        <div class="edit-body">

            <form action="edit-user.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

                <div class="edit-field">
                    <label>First Name</label>
                    <input type="text" name="first_name"
                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>

                <div class="edit-field">
                    <label>Last Name</label>
                    <input type="text" name="last_name"
                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>

                <div class="edit-field">
                    <label>Phone</label>
                    <input type="text" name="phone"
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                           placeholder="Not set">
                </div>

                <div class="edit-field">
                    <label>Study Level</label>
                    <select name="study_level" required>
                        <option value="" disabled <?php echo empty($user['study_level']) ? 'selected' : ''; ?>>
                            Select study level
                        </option>
                        <?php
                        $levels = ['Bac', 'Bac+1', 'Bac+2', 'Bac+3', 'Bac+4', 'Bac+5', 'Doctorat'];
                        foreach ($levels as $lvl) {
                            $sel = ($user['study_level'] === $lvl) ? 'selected' : '';
                            echo "<option value=\"$lvl\" $sel>$lvl</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="edit-field">
                    <label>Status</label>
                    <div class="status-row">
                        <label class="status-option">
                            <input type="radio" name="status" value="active"
                                   <?php echo $user['status'] === 'active' ? 'checked' : ''; ?>>
                            <div class="status-box active-box">✅ Active</div>
                        </label>
                        <label class="status-option">
                            <input type="radio" name="status" value="inactive"
                                   <?php echo $user['status'] === 'inactive' ? 'checked' : ''; ?>>
                            <div class="status-box inactive-box">⛔ Inactive</div>
                        </label>
                    </div>
                </div>

                <div class="edit-actions">
                    <a href="dashboard.php" class="edit-btn edit-btn-cancel">Cancel</a>
                    <button type="submit" class="edit-btn edit-btn-save">💾 Save Changes</button>
                </div>
            </form>

        </div>
    </div>

</main>

<?php require_once "../includes/footer.php"; ?>