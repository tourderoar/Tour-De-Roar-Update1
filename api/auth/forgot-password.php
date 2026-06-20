<?php
/**
 * File: api/auth/forgot-password.php
 * Location: /tour_update/api/auth/forgot-password.php
 *
 * Forgot password endpoint.
 * Generates a password reset token and sends a reset email.
 * Always returns the same message to prevent email enumeration.
 * 
 * Method: POST
 * Requires: CSRF token
 * Body: {email}
 * Returns: {success: true, message} - always same message regardless of email existence
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/mail.php';

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

// Extract email
$email = trim($input['email'] ?? '');

// Validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Valid email address is required', 400);
}

// Standard success message (same for all cases to prevent enumeration)
$standard_message = 'If an account exists for this email, you will receive password reset instructions shortly.';

try {
    $db = get_db();
    
    // Look up user by email
    $stmt = $db->prepare("SELECT id, first_name, last_name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user doesn't exist, return standard message (don't reveal existence)
    if (!$user) {
        json_success(['message' => $standard_message]);
    }
    
    // Delete any existing unused reset tokens for this user
    $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND used_at IS NULL");
    $stmt->execute([$user['id']]);
    
    // Generate secure reset token (64 characters)
    $reset_token = bin2hex(random_bytes(32));
    
    // Save reset token with 1-hour expiry
    $stmt = $db->prepare("
        INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
    ");
    $stmt->execute([$user['id'], $reset_token]);
    
    // Build reset link
    $reset_link = APP_URL . '/account/reset-password?token=' . $reset_token;
    
    // Send password reset email
    $email_subject = 'Reset Your Tour de Roar Password';
    $email_body = "
        <h2>Password Reset Request</h2>
        <p>Hello {$user['first_name']},</p>
        <p>We received a request to reset the password for your Tour de Roar account. Click the link below to create a new password:</p>
        <p><a href=\"{$reset_link}\" style=\"display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;\">Reset My Password</a></p>
        <p>Or copy and paste this link into your browser:</p>
        <p>{$reset_link}</p>
        <p><strong>This link will expire in 1 hour.</strong></p>
        <p>If you didn't request this password reset, please ignore this email and your password will remain unchanged.</p>
        <hr>
        <p style=\"color: #718096; font-size: 0.875rem;\">Tour de Roar - Cycling for Children's Health</p>
    ";
    
    send_mail($user['email'], $user['first_name'] . ' ' . $user['last_name'], $email_subject, $email_body);
    
    // Return standard success message
    json_success(['message' => $standard_message]);
    
} catch (PDOException $e) {
    error_log('Forgot password error: ' . $e->getMessage());
    json_error('Failed to process request. Please try again later.', 500);
}
