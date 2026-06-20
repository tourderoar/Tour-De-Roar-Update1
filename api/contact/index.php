<?php
/**
 * File: api/contact/index.php
 * Location: /tour_update/api/contact/index.php
 *
 * POST /api/contact
 * Handles contact form submissions.
 * Stores submission to database and sends confirmation email.
 * CSRF protection required.
 */

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// Validate CSRF token
require_csrf();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['name', 'email', 'subject', 'message'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        json_error("Field '$field' is required", 400);
    }
}

// Sanitize inputs
$name = trim($input['name']);
$email = trim($input['email']);
$subject = trim($input['subject']);
$message = trim($input['message']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address', 400);
}

// Validate message length
if (strlen($message) < 10) {
    json_error('Message must be at least 10 characters', 400);
}

if (strlen($message) > 5000) {
    json_error('Message is too long (max 5000 characters)', 400);
}

$db = get_db();

try {
    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO contact_submissions (name, email, subject, message, created_at)
        VALUES (:name, :email, :subject, :message, NOW())
    ");
    
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message
    ]);
    
    // Send auto-reply email (only on production, logged locally)
    require_once __DIR__ . '/../../includes/mail.php';
    
    $email_subject = 'Thank you for contacting Tour de Roar';
    $email_body = "
        <h2 style='color: #805AD5;'>Thank You for Reaching Out!</h2>
        <p>Dear {$name},</p>
        <p>We have received your message and will get back to you within 24 hours.</p>
        <p><strong>Your message:</strong></p>
        <p style='background: #f7fafc; padding: 15px; border-left: 4px solid #FF6B1A;'>" 
        . nl2br(htmlspecialchars($message)) . 
        "</p>
        <p>Best regards,<br>
        <strong style='background: linear-gradient(90deg, #FF6B1A, #E53E3E, #805AD5); -webkit-background-clip: text; -webkit-text-fill-color: transparent;'>
        Tour de Roar Team
        </strong></p>
        <p style='color: #718096; font-size: 12px;'>
        2860 South State Hwy 161, Ste 160 211, Grand Prairie, TX 75052<br>
        (972) 979-4608 | info@tourderoar.org
        </p>
    ";
    
    send_mail($email, $name, $email_subject, $email_body);
    
    json_success([
        'message' => 'Your message has been sent successfully. We\'ll get back to you soon!',
        'id' => $db->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Contact form API error: ' . $e->getMessage());
        json_error('Unable to send message. Please try again later.', 500);
    }
}
