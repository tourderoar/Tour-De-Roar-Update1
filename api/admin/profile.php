<?php
/**
 * Admin Profile API Endpoints
 * PUT /api/admin/profile - Update profile
 * PUT /api/admin/password - Change password
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/middleware.php';

session_init();
require_admin();

$method = $_SERVER['REQUEST_METHOD'];
$admin = $_SESSION['admin'];

if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Determine which update to perform
    $request_uri = $_SERVER['REQUEST_URI'];
    
    if (strpos($request_uri, '/password') !== false) {
        // Change password
        update_password($admin, $input);
    } else {
        // Update profile
        update_profile($admin, $input);
    }
} else {
    json_error('Method not allowed', 405);
}

/**
 * Update admin profile (name, email)
 */
function update_profile($admin, $input) {
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        json_error('Name and email are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Invalid email format');
    }
    
    $db = get_db();
    
    // Check if email is already in use by another admin
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1");
    $stmt->execute([$email, $admin['id']]);
    if ($stmt->fetch()) {
        json_error('Email is already in use by another administrator');
    }
    
    // Update profile
    $stmt = $db->prepare("
        UPDATE admins 
        SET name = ?, email = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$name, $email, $admin['id']]);
    
    // Update session
    $_SESSION['admin']['name'] = $name;
    $_SESSION['admin']['email'] = $email;
    
    json_success(['message' => 'Profile updated successfully']);
}

/**
 * Change admin password
 */
function update_password($admin, $input) {
    $current_password = $input['current_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    
    if (empty($current_password) || empty($new_password)) {
        json_error('Current password and new password are required');
    }
    
    if (strlen($new_password) < 8) {
        json_error('New password must be at least 8 characters');
    }
    
    $db = get_db();
    
    // Verify current password
    $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = ? LIMIT 1");
    $stmt->execute([$admin['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row || !password_verify($current_password, $row['password_hash'])) {
        json_error('Current password is incorrect');
    }
    
    // Update password
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("
        UPDATE admins 
        SET password_hash = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$new_hash, $admin['id']]);
    
    json_success(['message' => 'Password changed successfully']);
}
