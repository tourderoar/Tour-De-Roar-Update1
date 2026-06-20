<?php
/**
 * File: api/payments/store.php
 * Endpoint: POST /api/payments/store
 * 
 * Creates a Stripe PaymentIntent for a store order.
 * Requires user to be logged in.
 * Accepts cart items and shipping information.
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
    json_error('You must be logged in to complete your purchase', 401);
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
if (empty($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
    json_error('Cart items are required');
}

if (empty($data['shipping_name'])) {
    json_error('Shipping name is required');
}

if (empty($data['shipping_email'])) {
    json_error('Shipping email is required');
}

if (!filter_var($data['shipping_email'], FILTER_VALIDATE_EMAIL)) {
    json_error('Invalid email address');
}

if (empty($data['shipping_address'])) {
    json_error('Shipping address is required');
}

try {
    $db = get_db();
    
    // Validate and calculate total
    $total_amount = 0;
    $validated_items = [];
    
    foreach ($data['items'] as $item) {
        if (empty($item['product_id']) || empty($item['quantity'])) {
            json_error('Invalid cart item');
        }
        
        // Get product details
        $stmt = $db->prepare("
            SELECT id, name, price, stock, status 
            FROM products 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            json_error('Product not found or not available: ' . ($item['product_id'] ?? 'unknown'));
        }
        
        $quantity = intval($item['quantity']);
        if ($quantity <= 0) {
            json_error('Invalid quantity for product: ' . $product['name']);
        }
        
        if ($product['stock'] < $quantity) {
            json_error('Insufficient stock for product: ' . $product['name']);
        }
        
        $line_total = $product['price'] * $quantity;
        $total_amount += $line_total;
        
        $validated_items[] = [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'size' => $item['size'] ?? null,
            'quantity' => $quantity,
            'unit_price' => $product['price'],
            'line_total' => $line_total
        ];
    }
    
    if ($total_amount <= 0) {
        json_error('Invalid order total');
    }
    
    // Prepare items description for Stripe
    $items_description = implode(', ', array_map(function($item) {
        return $item['quantity'] . 'x ' . $item['product_name'];
    }, $validated_items));
    
    // Create Stripe PaymentIntent FIRST (before database record)
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => intval($total_amount * 100), // Stripe uses cents
        'currency' => 'usd',
        'description' => 'Store Order: ' . $items_description,
        'metadata' => [
            'type' => 'store',
            'user_id' => $user['id'],
            'items_count' => count($validated_items),
            'shipping_name' => $data['shipping_name']
        ],
        'receipt_email' => $data['shipping_email'],
    ]);
    
    // Only create database record if Stripe succeeded
    $stmt = $db->prepare("
        INSERT INTO orders (
            user_id, payment_status, total_amount,
            shipping_name, shipping_email, shipping_address, 
            stripe_payment_intent_id, created_at
        ) VALUES (?, 'pending', ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $user['id'],
        $total_amount,
        $data['shipping_name'],
        $data['shipping_email'],
        $data['shipping_address'],
        $payment_intent->id
    ]);
    
    $order_id = $db->lastInsertId();
    
    // Create order line items
    $stmt = $db->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, size, quantity, unit_price
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($validated_items as $item) {
        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['product_name'],
            $item['size'],
            $item['quantity'],
            $item['unit_price']
        ]);
    }
    
    // Return client_secret to frontend
    json_success([
        'client_secret' => $payment_intent->client_secret,
        'order_id' => $order_id,
        'amount' => $total_amount,
        'items_count' => count($validated_items)
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API error (store payment): " . $e->getMessage());
    json_error('Payment processing error. Please try again.');
} catch (PDOException $e) {
    error_log("Database error (store payment): " . $e->getMessage());
    json_error('Failed to process order. Please try again.');
}
