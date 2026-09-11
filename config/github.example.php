<?php
/**
 * GitHub OAuth Configuration Template
 * ------------------------------------
 * Copy this file to `github.php` and fill in your own values.
 *
 * Get your credentials from:
 * https://github.com/settings/developers
 *
 * Setup:
 * 1. Click "New OAuth App"
 * 2. Application name:  MK Students
 * 3. Homepage URL:      http://localhost/mk-students/
 * 4. Authorization callback URL:
 *    http://localhost/mk-students/github-callback.php
 * 5. Register application → copy Client ID
 * 6. Generate a new Client Secret → copy it
 */

return [
    'client_id'     => 'YOUR_GITHUB_CLIENT_ID',
    'client_secret' => 'YOUR_GITHUB_CLIENT_SECRET',
    'redirect_uri'  => 'http://localhost/mk-students/github-callback.php',
];