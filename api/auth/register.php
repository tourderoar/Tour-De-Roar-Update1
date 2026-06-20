<?php
/**
 * File: api/auth/register.php
 * Location: /tour_update/api/auth/register.php
 *
 * User registration endpoint.
 * Creates new user account with status='inactive', generates activation token,
 * and sends activation email.
 * 
 * Method: POST
 * Requires: CSRF token
 * Body: {first_name, last_name, email, phone (optional), password, password_confirm}
 * Returns: {success: true, message} or {success: false, error}
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

// Extract and validate fields
$first_name = trim($input['first_name'] ?? '');
$last_name = trim($input['last_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';
$password_confirm = $input['password_confirm'] ?? '';

// Validation
$errors = [];

if (empty($first_name)) {
    $errors[] = 'First name is required';
}

if (empty($last_name)) {
    $errors[] = 'Last name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
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
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        json_error('An account with this email already exists', 400);
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user with inactive status
    $stmt = $db->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password_hash, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'inactive', NOW(), NOW())
    ");
    
    $stmt->execute([$first_name, $last_name, $email, $phone, $password_hash]);
    $user_id = $db->lastInsertId();
    
    // Generate secure activation token (64 characters)
    $activation_token = bin2hex(random_bytes(32));
    
    // Save activation token with 24-hour expiry
    $stmt = $db->prepare("
        INSERT INTO user_activation_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    
    $stmt->execute([$user_id, $activation_token]);
    
    // Build activation link
    $activation_link = APP_URL . '/account/activate?token=' . $activation_token;
    
    // Send activation email
    $email_subject = 'Activate Your Tour de Roar Account';
    $email_body = "
        <h2>Welcome to Tour de Roar, {$first_name}!</h2>
        <p>Thank you for creating an account. Please click the link below to activate your account:</p>
        <p><a href=\"{$activation_link}\" style=\"display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #FF6B1A 0%, #E53E3E 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;\">Activate My Account</a></p>
        <p>Or copy and paste this link into your browser:</p>
        <p>{$activation_link}</p>
        <p><strong>This link will expire in 24 hours.</strong></p>
        <p>If you didn't create this account, please ignore this email.</p>
        <hr>
        <p style=\"color: #718096; font-size: 0.875rem;\">Tour de Roar - Cycling for Children's Health</p>
    ";
    
    send_mail($email, $first_name . ' ' . $last_name, $email_subject, $email_body);
    
    // Return success (do NOT log the user in yet - they must activate first)
    json_success([
        'message' => 'Registration successful! Please check your email to activate your account.',
        'email' => $email
    ]);
    
} catch (PDOException $e) {
    error_log('Registration error: ' . $e->getMessage());
    json_error('Registration failed. Please try again later.', 500);
}
