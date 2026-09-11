<?php
// github-callback.php — receives the code from GitHub and logs the user in
session_start();
require_once 'config/database.php';

$config = require 'config/github.php';

// 1. CSRF check
if (
    !isset($_GET['state']) ||
    !isset($_SESSION['oauth_state']) ||
    $_GET['state'] !== $_SESSION['oauth_state']
) {
    die('Invalid state parameter. Possible CSRF attack.');
}
unset($_SESSION['oauth_state']);

// 2. Error from GitHub?
if (isset($_GET['error'])) {
    die('GitHub login error: ' . htmlspecialchars($_GET['error_description'] ?? $_GET['error']));
}

if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit();
}

// 3. Exchange code for access token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'client_id'     => $config['client_id'],
    'client_secret' => $config['client_secret'],
    'code'          => $_GET['code'],
    'redirect_uri'  => $config['redirect_uri'],
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['access_token'])) {
    die('Error: Could not retrieve GitHub access token. ' . htmlspecialchars(json_encode($response)));
}

$access_token = $response['access_token'];

// 4. Fetch user profile
$ch = curl_init('https://api.github.com/user');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: token ' . $access_token,
    'User-Agent: MK-Students-App',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

$email = $userInfo['email'] ?? null;

// Email might be private → fetch separately
if (empty($email)) {
    $ch = curl_init('https://api.github.com/user/emails');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $access_token,
        'User-Agent: MK-Students-App',
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $emails = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (is_array($emails)) {
        foreach ($emails as $e) {
            if (!empty($e['primary']) && !empty($e['verified'])) {
                $email = $e['email'];
                break;
            }
        }
    }
}

if (empty($email)) {
    die('Error: Could not retrieve a verified email from GitHub. Please make your email public in GitHub settings.');
}

// GitHub returns full name in "name"; split into first/last
$fullName   = $userInfo['name'] ?? $userInfo['login'] ?? 'GitHub User';
$parts      = explode(' ', $fullName, 2);
$first_name = $parts[0] ?: 'GitHub';
$last_name  = $parts[1] ?? 'User';

// 5. Find or create the user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['role']       = $user['role'];
} else {
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

// 6. Redirect by role
if ($_SESSION['role'] === 'admin') {
    header('Location: admin/dashboard.php');
} else {
    header('Location: user/dashboard.php');
}
exit();