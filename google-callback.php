<?php
// google-callback.php — receives the code from Google and logs the user in
session_start();
require_once 'config/database.php';

$config = require 'config/google.php';

$client_id     = $config['client_id'];
$client_secret = $config['client_secret'];
$redirect_uri  = $config['redirect_uri'];

// Google sent us an error?
if (isset($_GET['error'])) {
    die('Google login error: ' . htmlspecialchars($_GET['error']));
}

// No code → send the user back to login
if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit();
}

// ============ 1. Exchange code for access token ============
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'code'          => $_GET['code'],
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code',
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    die('Error: Could not retrieve Google access token. ' . htmlspecialchars(json_encode($response)));
}

// ============ 2. Fetch user info ============
$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $response['access_token']]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($userInfo['email'])) {
    die('Error: Google did not return an email.');
}

$email      = $userInfo['email'];
$first_name = $userInfo['given_name']  ?? 'Google';
$last_name  = $userInfo['family_name'] ?? 'User';

// ============ 3. Find or create the user ============
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    // Existing user → log them in
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
} else {
    // New user → create the account
    $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $role     = 'user';
    $status   = 'active';
    $phone    = '';

    $stmt = $conn->prepare(
        "INSERT INTO users (first_name, last_name, email, password, phone, role, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $password, $phone, $role, $status);
    $stmt->execute();
    $user_id = $conn->insert_id;
    $stmt->close();

    $_SESSION['user_id']    = $user_id;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name']  = $last_name;
    $_SESSION['email']      = $email;
    $_SESSION['role']       = $role;
}

// ============ 4. Redirect by role ============
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: user/dashboard.php');
}
exit();