<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireUser();
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Delete associated profile picture[cite: 1]
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
        unlink("../uploads/profiles/" . $user['profile_picture']);
    }

    // 2. Delete user (Skills will be cascade-deleted by MySQL)[cite: 1]
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // 3. Terminate session
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>