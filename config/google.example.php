<?php
/**
 * Google OAuth Configuration Template
 * -----------------------------------
 * Copy this file to `google.php` and fill in your own values.
 *
 * Get your credentials from:
 * https://console.cloud.google.com/apis/credentials
 *
 * Setup:
 * 1. Create a project (e.g. "MK Students")
 * 2. Configure OAuth consent screen (User Type: External, Testing)
 * 3. Create credentials → OAuth client ID → Web application
 * 4. Authorized JavaScript origins:  http://localhost
 * 5. Authorized redirect URIs:       http://localhost/mk-students/google-callback.php
 * 6. Copy Client ID + Secret below
 */

return [
    'client_id'     => 'YOUR_GOOGLE_CLIENT_ID',
    'client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
    'redirect_uri'  => 'http://localhost/mk-students/google-callback.php',
];