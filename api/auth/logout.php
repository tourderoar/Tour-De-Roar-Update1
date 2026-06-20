<?php
/**
 * File: api/auth/logout.php
 * Location: /tour_update/api/auth/logout.php
 *
 * User logout endpoint.
 * Destroys the user session and clears all session data.
 * 
 * Method: POST
 * Requires: Valid user session
 * Returns: {success: true, message}
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';

// Initialize session
session_init();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// Check if user is logged in
if (!is_logged_in()) {
    json_error('Not logged in', 401);
}

// Clear all session data
$_SESSION = [];

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Return success
json_success([
    'message' => 'Logged out successfully'
]);
