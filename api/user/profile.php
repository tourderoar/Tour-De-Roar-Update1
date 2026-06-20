<?php
/**
 * File: api/user/profile.php
 * Location: /tour_update/api/user/profile.php
 *
 * User profile update endpoint.
 * Allows logged-in users to update their personal information.
 * 
 * Method: PUT
 * Requires: User session, CSRF token
 * Body: {first_name, last_name, phone (optional)}
 * Returns: {success: true, message, user} or {success: false, error}
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// Initialize session
session_init();

// Only accept PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    json_error('Method not allowed', 405);
}

// Require user to be logged in
if (!is_logged_in()) {
    json_error('Please log in to update your profile', 401);
}

// Validate CSRF token
if (!validate_csrf_token()) {
    json_error('Invalid CSRF token', 403);
}

$user = get_logged_in_user();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    json_error('Invalid JSON input', 400);
}

// Extract and validate fields
$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$phone = trim($input['phone'] ?? '');

// Validation
$errors = [];

if (empty($first_name)) {
    $errors[] = 'First name is required';
}

if (empty($last_name)) {
    $errors[] = 'Last name is required';
}

// Phone validation (optional field, but if provided must be valid)
if (!empty($phone)) {
    // Allow only digits, spaces, hyphens, parentheses, and plus sign
    if (!preg_match('/^[\d\s\-\(\)\+]+$/', $phone)) {
        $errors[] = 'Phone number can only contain digits, spaces, hyphens, parentheses, and plus sign';
    }
    // Must have at least 10 digits
    $digits_only = preg_replace('/\D/', '', $phone);
    if (strlen($digits_only) < 10) {
        $errors[] = 'Phone number must contain at least 10 digits';
    }
}

if (!empty($errors)) {
    json_error(implode(', ', $errors), 400);
}

try {
    $db = get_db();
    
    // Update user information
    $stmt = $db->prepare("
        UPDATE users 
        SET first_name = ?, 
            last_name = ?, 
            phone = ?, 
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $stmt->execute([$first_name, $last_name, $phone, $user['id']]);
    
    // Update session with new data
    $_SESSION['user']['first_name'] = $first_name;
    $_SESSION['user']['last_name'] = $last_name;
    
    // Return updated user data
    json_success([
        'message' => 'Profile updated successfully',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone
        ]
    ]);
    
} catch (PDOException $e) {
    error_log('Profile update error: ' . $e->getMessage());
    json_error('Failed to update profile. Please try again later.', 500);
}
