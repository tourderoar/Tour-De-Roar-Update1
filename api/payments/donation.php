<?php
/**
 * File: api/payments/donation.php
 * Endpoint: POST /api/payments/donation
 * 
 * Creates a Stripe PaymentIntent for a donation.
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
    json_error('You must be logged in to make a donation', 401);
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
if (empty($data['donation_type_id'])) {
    json_error('Donation type is required');
}

try {
    $db = get_db();
    
    $donation_type_id = intval($data['donation_type_id']);
    $is_custom = ($donation_type_id === -1 || $donation_type_id === -2);
    
    if ($is_custom) {
        // Custom donation amount
        if (empty($data['custom_amount'])) {
            json_error('Custom donation amount is required');
        }
        
        $amount = floatval($data['custom_amount']);
        
        // Validate custom amount range
        if ($amount < 5) {
            json_error('Minimum donation amount is $5.00');
        }
        
        if ($amount > 10000) {
            json_error('Maximum donation amount is $10,000.00');
        }
        
        $donation_label = $donation_type_id === -1 ? 'One-Time Custom Donation' : 'Monthly Custom Donation';
        
    } else {
        // Preset donation type
        $stmt = $db->prepare("
            SELECT id, label, amount, status 
            FROM donation_types 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$donation_type_id]);
        $donation_type = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$donation_type) {
            json_error('Donation type not found or not available', 404);
        }
        
        $amount = $donation_type['amount'];
        $donation_label = $donation_type['label'];
        
        // Validate amount
        if ($amount <= 0) {
            json_error('Invalid donation amount');
        }
    }
    
    // Determine if recurring
    $is_recurring = !empty($data['is_recurring']) ? 1 : 0;
    
    // Create Stripe PaymentIntent FIRST (before database record)
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => intval($amount * 100), // Stripe uses cents
        'currency' => 'usd',
        'description' => 'Donation: ' . $donation_label,
        'metadata' => [
            'type' => 'donation',
            'type_id' => $donation_type_id,
            'user_id' => $user['id'],
            'donation_label' => $donation_label,
            'is_recurring' => $is_recurring ? 'yes' : 'no',
            'is_custom' => $is_custom ? 'yes' : 'no'
        ],
        'receipt_email' => $user['email'],
    ]);
    
    // Only create database record if Stripe succeeded
    $stmt = $db->prepare("
        INSERT INTO donation_payments (
            donation_type_id, user_id, payment_status, amount_paid, 
            is_recurring, stripe_payment_intent_id, created_at
        ) VALUES (?, ?, 'pending', ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $donation_type_id,
        $user['id'],
        $amount,
        $is_recurring,
        $payment_intent->id
    ]);
    
    $payment_id = $db->lastInsertId();
    
    // Return client_secret to frontend
    json_success([
        'client_secret' => $payment_intent->client_secret,
        'payment_id' => $payment_id,
        'amount' => $amount,
        'donation_label' => $donation_label
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API error (donation payment): " . $e->getMessage());
    json_error('Payment processing error. Please try again.');
} catch (PDOException $e) {
    error_log("Database error (donation payment): " . $e->getMessage());
    json_error('Failed to process donation. Please try again.');
}
