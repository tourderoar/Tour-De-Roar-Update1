<?php
/**
 * File: api/payments/event.php
 * Endpoint: POST /api/payments/event
 * 
 * Creates a Stripe PaymentIntent for event registration.
 * Requires user to be logged in.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/stripe.php';

// Initialize session
session_init();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed', 405);
}

// Get logged-in user
$user = get_logged_in_user();
if (!$user) {
    json_error('You must be logged in to register for an event', 401);
}

// Get JSON input
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Validate CSRF token
$received_token = $data['csrf_token'] ?? '';
$expected_token = $_SESSION['csrf_token'] ?? '';
if (empty($expected_token) || !hash_equals($expected_token, $received_token)) {
    json_error('Invalid CSRF token', 403);
}

// Validate required fields
if (empty($data['event_id'])) {
    json_error('Event ID is required');
}

if (empty($data['participant_name'])) {
    json_error('Participant name is required');
}

if (empty($data['participant_email'])) {
    json_error('Participant email is required');
}

if (!filter_var($data['participant_email'], FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address');
}

try {
    $db = get_db();
    
    // Get event details
    $stmt = $db->prepare("
        SELECT id, title, price, status 
        FROM events 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$data['event_id']]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$event) {
        json_error('Event not found or not available', 404);
    }
    
    $amount = $event['price'];
    
    // Validate amount
    if ($amount <= 0) {
        json_error('Invalid event price');
    }
    
    // Create Stripe PaymentIntent FIRST (before database record)
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => intval($amount * 100), // Stripe uses cents
        'currency' => 'usd',
        'description' => 'Event Registration: ' . $event['title'],
        'metadata' => [
            'type' => 'event',
            'event_id' => $event['id'],
            'event_title' => $event['title'],
            'user_id' => $user['id'],
            'participant_name' => $data['participant_name'],
            'participant_email' => $data['participant_email']
        ],
        'receipt_email' => $data['participant_email'],
    ]);
    
    // Only create database record if Stripe succeeded
    $stmt = $db->prepare("
        INSERT INTO event_registrations (
            event_id, user_id, participant_name, participant_email, 
            participant_phone, emergency_contact, emergency_phone,
            payment_status, amount_paid, stripe_payment_intent_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
    ");
    
    $stmt->execute([
        $event['id'],
        $user['id'],
        $data['participant_name'],
        $data['participant_email'],
        $data['participant_phone'] ?? null,
        $data['emergency_contact'] ?? null,
        $data['emergency_phone'] ?? null,
        $amount,
        $payment_intent->id
    ]);
    
    $registration_id = $db->lastInsertId();
    
    // Return client_secret to frontend
    json_success([
        'client_secret' => $payment_intent->client_secret,
        'registration_id' => $registration_id,
        'amount' => $amount,
        'event_title' => $event['title']
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API error (event payment): " . $e->getMessage());
    json_error('Payment processing error. Please try again.');
} catch (PDOException $e) {
    error_log("Database error (event payment): " . $e->getMessage());
    json_error('Failed to process registration. Please try again.');
}
