<?php
/**
 * File: account/logout.php
 * Location: /tour_update/account/logout.php
 *
 * Logout handler.
 * Calls the logout API endpoint, then redirects to homepage.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Destroy session
$_SESSION = [];
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}
session_destroy();

// Redirect to homepage
header('Location: ' . APP_URL . '/');
exit;
