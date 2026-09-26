<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId  = $_SESSION['user_id'];
$targetId = (int)($_GET['id'] ?? 0);

// Safety: can't delete yourself
if ($targetId <= 0 || $targetId == $adminId) {
    header("Location: dashboard.php?error=invalid_delete");
    exit();
}

// Fetch the target user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

// 1. Delete their skills
$stmt = $conn->prepare("DELETE FROM skills WHERE user_id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$stmt->close();

// 2. Delete their profile picture (if not default)
if (!empty($user['profile_picture'])
    && $user['profile_picture'] !== 'default-avatar.png'
    && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
    @unlink("../uploads/profiles/" . $user['profile_picture']);
}

// 3. Delete the user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php?success=user_deleted");
exit();