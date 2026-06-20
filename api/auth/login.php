<?php
/**
 * File: api/auth/login.php
 * Location: /tour_update/api/auth/login.php
 *
 * User login endpoint.
 * Validates credentials, checks account activation status, creates session.
 * 
 * Method: POST
 * Requires: CSRF token
 * Body: {email, password, redirect (optional)}
 * Returns: {success: true, user, redirect} or {success: false, error}
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// Initialize session
session_init();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// Validate CSRF token
if (!validate_csrf_token()) {
    json_error('Invalid CSRF token', 403);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_error('Invalid JSON input', 400);
}

// Extract fields
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$redirect = $input['redirect'] ?? APP_URL . '/account/dashboard';

// Validation
if (empty($email) || empty($password)) {
    json_error('Email and password are required', 400);
}

try {
    $db = get_db();
    
    // Look up user by email
    $stmt = $db->prepare("
        SELECT id, first_name, last_name, email, phone, password_hash, status
        FROM users
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists and password is correct
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Generic error message to prevent email enumeration
        json_error('Invalid email or password', 401);
    }
    
    // Check if account is activated
    if ($user['status'] === 'inactive') {
        json_error('Please activate your account. Check your email for the activation link.', 403);
    }
    
    // Account is active - create session
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone']
    ];
    
    // Regenerate session ID for security (prevents session fixation)
    session_regenerate_id(true);
    
    // Return success with user data
    json_success([
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name']
        ],
        'redirect' => $redirect
    ]);
    
} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    json_error('Login failed. Please try again later.', 500);
}
