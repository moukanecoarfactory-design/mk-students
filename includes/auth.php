<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if a user session exists
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Restrict access to logged-in users only
function requireUser() {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
}

// Restrict access to administrators only
function requireAdmin() {
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}
?>