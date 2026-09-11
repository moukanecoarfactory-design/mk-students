<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);

if ($targetId > 0) {
    // Check for custom profile picture
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $targetId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user && !empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
        unlink("../uploads/profiles/" . $user['profile_picture']);
    }

    // Delete skills
    $stmt = $conn->prepare("DELETE FROM skills WHERE user_id = ?");
    $stmt->bind_param("i", $targetId);
    $stmt->execute();
    $stmt->close();

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $targetId);
    $stmt->execute();
    $stmt->close();
}
header("Location: dashboard.php");
exit();
?>