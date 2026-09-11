<?php
session_start();
require_once 'config/database.php';

// =========================
// REGISTER
// =========================
if (isset($_POST['register'])) {

    $first_name       = trim($_POST['first_name'] ?? '');
    $last_name        = trim($_POST['last_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $role             = $_POST['role'] ?? 'user';

    // Only allow user / admin
    if (!in_array($role, ['user', 'admin'])) {
        $role = 'user';
    }

    $errors = [];

    if (empty($first_name))          $errors[] = "First name is required";
    if (empty($last_name))           $errors[] = "Last name is required";
    if (empty($email))               $errors[] = "Email is required";
    if (empty($password))            $errors[] = "Password is required";
    if (empty($password_confirm))    $errors[] = "Password confirmation is required";

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    if (!empty($password) && strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!empty($password) && !empty($password_confirm) && $password !== $password_confirm) {
        $errors[] = "Passwords do not match";
    }

    if (!empty($errors)) {
        $_SESSION['register_error'] = implode(", ", $errors);
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['register_error'] = 'This email is already registered';
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare(
        "INSERT INTO users (first_name, last_name, email, password, role) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);

    if ($stmt->execute()) {
        $_SESSION['register_success'] = 'Registration successful! Please login.';
        $_SESSION['active_form'] = 'login';
    } else {
        $_SESSION['register_error'] = 'Registration failed. Please try again.';
        $_SESSION['active_form'] = 'register';
    }

    header("Location: index.php");
    exit();
}

// Direct visit → go home
header("Location: index.php");
exit();