<?php
// google-login.php — starts the Google OAuth flow
session_start();
require_once 'config/database.php';

$config = require 'config/google.php';

$client_id     = $config['client_id'];
$redirect_uri = 'http://localhost/mk-students/google-callback.php';
$scope         = 'email profile';

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'scope'         => $scope,
    'access_type'   => 'online',
    'prompt'        => 'select_account',
]);

header('Location: ' . $auth_url);
exit();