<?php
/**
 * File: api/payments/sponsorship.php
 * Endpoint: POST /api/payments/sponsorship
 * 
 * Creates a Stripe PaymentIntent for a sponsorship package.
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
    json_error('You must be logged in to purchase a sponsorship', 401);
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
if (empty($data['package_id'])) {
    json_error('Sponsorship package is required');
}

if (empty($data['company_name'])) {
    json_error('Company name is required');
}

if (empty($data['contact_email'])) {
    json_error('Contact email is required');
}

if (!filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address');
}

try {
    $db = get_db();
    
    // Get sponsorship package details
    $stmt = $db->prepare("
        SELECT id, name, price, status 
        FROM sponsorship_packages 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$data['package_id']]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        json_error('Sponsorship package not found or not available', 404);
    }
    
    $amount = $package['price'];
    
    // Validate amount
    if ($amount <= 0) {
        json_error('Invalid package price');
    }
    
    // Create Stripe PaymentIntent FIRST (before database record)
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => intval($amount * 100), // Stripe uses cents
        'currency' => 'usd',
        'description' => 'Sponsorship: ' . $package['name'] . ' Package',
        'metadata' => [
            'type' => 'sponsorship',
            'type_id' => $package['id'],
            'user_id' => $user['id'],
            'package_name' => $package['name'],
            'company_name' => $data['company_name'],
            'contact_email' => $data['contact_email']
        ],
        'receipt_email' => $data['contact_email'],
    ]);
    
    // Only create database record if Stripe succeeded
    $stmt = $db->prepare("
        INSERT INTO sponsorship_payments (
            package_id, user_id, payment_status, amount_paid,
            company_name, contact_email, stripe_payment_intent_id, created_at
        ) VALUES (?, ?, 'pending', ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $package['id'],
        $user['id'],
        $amount,
        $data['company_name'],
        $data['contact_email'],
        $payment_intent->id
    ]);
    
    $payment_id = $db->lastInsertId();
    
    // Return client_secret to frontend
    json_success([
        'client_secret' => $payment_intent->client_secret,
        'payment_id' => $payment_id,
        'amount' => $amount,
        'package_name' => $package['name']
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API error (sponsorship payment): " . $e->getMessage());
    json_error('Payment processing error. Please try again.');
} catch (PDOException $e) {
    error_log("Database error (sponsorship payment): " . $e->getMessage());
    json_error('Failed to process sponsorship. Please try again.');
}
