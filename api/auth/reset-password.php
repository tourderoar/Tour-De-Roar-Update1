<?php
/**
 * File: api/auth/reset-password.php
 * Location: /tour_update/api/auth/reset-password.php
 *
 * Reset password endpoint.
 * Validates reset token, updates user password, marks token as used.
 * 
 * Method: POST
 * Requires: CSRF token
 * Body: {token, password, password_confirm}
 * Returns: {success: true, message} or {success: false, error}
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// Initialize session for CSRF validation
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
$token = trim($input['token'] ?? '');
$password = $input['password'] ?? '';
$password_confirm = $input['password_confirm'] ?? '';

// Validation
$errors = [];

if (empty($token)) {
    $errors[] = 'Reset token is required';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain uppercase, lowercase, and number';
}

if ($password !== $password_confirm) {
    $errors[] = 'Passwords do not match';
}

if (!empty($errors)) {
    json_error(implode(', ', $errors), 400);
}

try {
    $db = get_db();
    
    // Look up the token and check expiry using MySQL NOW() to avoid timezone issues
    $stmt = $db->prepare("
        SELECT prt.id, prt.user_id, prt.expires_at, prt.used_at,
               (prt.expires_at > NOW()) AS is_valid
        FROM password_reset_tokens prt
        WHERE prt.token = ?
    ");
    $stmt->execute([$token]);
    $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$token_data) {
        json_error('Invalid or expired reset token', 400);
    }
    
    if ($token_data['used_at'] !== null) {
        json_error('This reset link has already been used', 400);
    }
    
    if ($token_data['is_valid'] != 1) {
        json_error('This reset link has expired. Please request a new one.', 400);
    }
    
    // Hash new password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Update user password
    $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$password_hash, $token_data['user_id']]);
    
    // Mark token as used
    $stmt = $db->prepare("UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?");
    $stmt->execute([$token_data['id']]);
    
    // Return success
    json_success([
        'message' => 'Password reset successful. You can now sign in with your new password.'
    ]);
    
} catch (PDOException $e) {
    error_log('Reset password error: ' . $e->getMessage());
    json_error('Password reset failed. Please try again later.', 500);
}
