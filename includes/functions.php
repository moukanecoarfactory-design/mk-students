<?php
session_start();

// Database connection function
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'mk-students';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// User authentication function
function authenticateUser($email, $password) {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Check if user has admin role
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get user by ID
function getUserById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user skills
function getUserSkills($user_id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get all active users
function getActiveUsers() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM users WHERE status = 'active' ORDER BY last_name, first_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add skill to user
function addUserSkill($user_id, $skill_name) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)");
    return $stmt->execute([$user_id, $skill_name]);
}

// Update user status
function updateUserStatus($user_id, $status) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $user_id]);
}

// Logout function
function logout() {
    session_destroy();
    header('Location: login.php');
    exit();
}
?>