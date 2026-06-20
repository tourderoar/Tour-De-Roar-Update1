<?php
/**
 * Admin Logout
 * Destroys admin session and redirects to login
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Clear admin session
unset($_SESSION['admin']);

// Destroy entire session
session_destroy();

// Redirect to admin login
header('Location: ' . APP_URL . '/admin/login');
exit;
