<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$targetId = (int)($_GET['id'] ?? 0);

if ($targetId > 0) {
    $stmt = $conn->prepare("SELECT status FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $targetId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($user) {
        $newStatus = ($user['status'] === 'active') ? 'inactive' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $targetId);
        $stmt->execute();
        $stmt->close();
    }
}
header("Location: dashboard.php");
exit();
?>