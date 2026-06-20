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

// Rate limiting: Check if this IP has submitted in the last 5 minutes
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$stmt = $db->prepare("
    SELECT COUNT(*) as submission_count 
    FROM contact_submissions 
    WHERE ip_address = ? 
    AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
");
$stmt->execute([$ip_address]);
$recent_submissions = $stmt->fetch(PDO::FETCH_ASSOC);

if ($recent_submissions['submission_count'] > 0) {
    json_error('Please wait a few minutes before submitting another message', 429);
}

// Honeypot check - if this field is filled, it's likely a bot
if (!empty($input['website'])) {
    // Silently fail for bots
    json_success([
        'message' => 'Your message has been sent successfully. We\'ll get back to you soon!'
    ]);
    exit;
}

try {
    // Insert into database
    $stmt = $db->prepare("
        INSERT INTO contact_submissions (name, email, subject, message, ip_address, created_at)
        VALUES (:name, :email, :subject, :message, :ip_address, NOW())
    ");
    
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'subject' => $subject,
        'message' => $message,
        'ip_address' => $ip_address
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
    
    // Notify all admins about the new contact form submission
    $stmt = $db->prepare("SELECT email, name FROM admins WHERE status = 'active'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($admins as $admin) {
        if (!empty($admin['email'])) {
            $admin_subject = "New Contact Form Submission: {$subject}";
            $admin_body = "
                <h2 style='color: #805AD5;'>New Contact Form Submission</h2>
                <p>A new message has been received through the contact form.</p>
                
                <div style='background: #f7fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <p><strong>From:</strong> {$name}</p>
                    <p><strong>Email:</strong> <a href='mailto:{$email}'>{$email}</a></p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <p><strong>Submitted:</strong> " . date('F j, Y g:i A') . "</p>
                </div>
                
                <div style='background: #ffffff; padding: 20px; border-left: 4px solid #FF6B1A; margin: 20px 0;'>
                    <p><strong>Message:</strong></p>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                </div>
                
                <p style='color: #718096; font-size: 14px; margin-top: 30px;'>
                    <strong>Action Required:</strong> Please respond to this inquiry within 24 hours.
                </p>
            ";
            
            send_mail($admin['email'], $admin['name'], $admin_subject, $admin_body);
        }
    }
    
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
