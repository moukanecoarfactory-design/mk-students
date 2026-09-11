<?php
session_start();

$config = require 'config/github.php';

// CSRF protection token
$_SESSION['oauth_state'] = bin2hex(random_bytes(16));

$auth_url = 'https://github.com/login/oauth/authorize?' . http_build_query([
    'client_id'    => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'scope'        => 'read:user user:email',
    'state'        => $_SESSION['oauth_state'],
]);

header('Location: ' . $auth_url);
exit();