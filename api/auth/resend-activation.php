<?php
/**
 * File: api/auth/resend-activation.php
 * Location: /tour_update/api/auth/resend-activation.php
 *
 * Resend activation email endpoint.
 * Generates a new activation token and sends a new activation email.
 * Rate-limited to prevent abuse (max 3 requests per hour per email).
 * 
 * Method: POST
 * Requires: CSRF token
 * Body: {email}
 * Returns: {success: true, message} - always returns same message to prevent email enumeration
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
$standard_message = 'If an unactivated account exists for this email, a new activation link has been sent.';

try {
    $db = get_db();
    
    // Look up user by email
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user doesn't exist or is already active, return standard message (don't reveal status)
    if (!$user || $user['status'] === 'active') {
        json_success(['message' => $standard_message]);
    }
    
    // Check rate limit: max 3 requests per hour for this user
    $stmt = $db->prepare("
        SELECT COUNT(*) as request_count
        FROM user_activation_tokens
        WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
    $stmt->execute([$user['id']]);
    $rate_limit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($rate_limit['request_count'] >= 3) {
        json_error('Too many requests. Please wait an hour before requesting another activation email.', 429);
    }
    
    // Delete any existing unused tokens for this user
    $stmt = $db->prepare("DELETE FROM user_activation_tokens WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    
    // Generate new secure activation token (64 characters)
    $activation_token = bin2hex(random_bytes(32));
    
    // Save new activation token with 24-hour expiry
    $stmt = $db->prepare("
        INSERT INTO user_activation_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    $stmt->execute([$user['id'], $activation_token]);
    
    // Build activation link
    $activation_link = APP_URL . '/account/activate?token=' . $activation_token;
    
    // Send activation email
    $email_subject = 'Activate Your Tour de Roar Account';
    $email_body = "
        <h2>Account Activation</h2>
        <p>Hello {$user['first_name']},</p>
        <p>You requested a new activation link for your Tour de Roar account. Please click the link below to activate your account:</p>
        <p><a href=\"{$activation_link}\" style=\"display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #FF6B1A 0%, #E53E3E 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;\">Activate My Account</a></p>
        <p>Or copy and paste this link into your browser:</p>
        <p>{$activation_link}</p>
        <p><strong>This link will expire in 24 hours.</strong></p>
        <p>If you didn't request this, please ignore this email.</p>
        <hr>
        <p style=\"color: #718096; font-size: 0.875rem;\">Tour de Roar - Cycling for Children's Health</p>
    ";
    
    send_mail($user['email'], $user['first_name'] . ' ' . $user['last_name'], $email_subject, $email_body);
    
    // Return standard success message
    json_success(['message' => $standard_message]);
    
} catch (PDOException $e) {
    error_log('Resend activation error: ' . $e->getMessage());
    json_error('Failed to process request. Please try again later.', 500);
}
