<?php
/**
 * File: api/user/change-password.php
 * Endpoint: PUT /api/user/change-password
 * 
 * Change password for logged-in user.
 * Requires current password verification.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth.php';

// Initialize session
session_init();

// Only allow PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_error('Method not allowed', 405);
}

// Get logged-in user
$user = get_logged_in_user();
if (!$user) {
    json_error('Unauthorized. Please log in.', 401);
}

// Get JSON input
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Validate CSRF token
if (!validate_csrf_token($data['csrf_token'] ?? '')) {
    json_error('Invalid CSRF token', 403);
}

// Validate required fields
if (empty($data['current_password'])) {
    json_error('Current password is required');
}

if (empty($data['new_password'])) {
    json_error('New password is required');
}

// Validate new password strength
$new_password = trim($data['new_password']);
if (strlen($new_password) < 8) {
    json_error('New password must be at least 8 characters long');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $new_password)) {
    json_error('New password must contain at least one uppercase letter, one lowercase letter, and one number');
}

try {
    $db = get_db();
    
    // Get user's current password hash
    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        json_error('User not found', 404);
    }
    
    // Verify current password
    if (!password_verify($data['current_password'], $user_data['password_hash'])) {
        json_error('Current password is incorrect');
    }
    
    // Check if new password is same as current password
    if (password_verify($new_password, $user_data['password_hash'])) {
        json_error('New password must be different from current password');
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update password
    $stmt = $db->prepare("
        UPDATE users 
        SET password_hash = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$new_password_hash, $user['id']]);
    
    json_success([
        'message' => 'Password updated successfully'
    ]);
    
} catch (PDOException $e) {
    error_log("Change password error: " . $e->getMessage());
    json_error('Failed to update password. Please try again.');
}
