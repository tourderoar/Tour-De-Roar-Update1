<?php
/**
 * File: api/user/transactions.php
 * Location: /tour_update/api/user/transactions.php
 *
 * User transactions history endpoint.
 * Returns all payments made by the logged-in user across all types
 * (event registrations, donations, sponsorships, store orders).
 * 
 * Method: GET
 * Requires: User session
 * Returns: {success: true, transactions: [...]} or {success: false, error}
 */

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/response.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';

// Initialize session
session_init();

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

// Require user to be logged in
if (!is_logged_in()) {
    json_error('Please log in to view your transactions', 401);
}

$user = get_logged_in_user();

try {
    $db = get_db();
    $transactions = [];
    
    // Fetch Event Registrations
    $stmt = $db->prepare("
        SELECT 
            er.id,
            'event' AS type,
            e.title AS title,
            er.amount_paid AS amount,
            er.payment_status,
            er.stripe_payment_intent_id,
            er.created_at
        FROM event_registrations er
        INNER JOIN events e ON er.event_id = e.id
        WHERE er.user_id = ?
        ORDER BY er.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($events as $event) {
        $transactions[] = $event;
    }
    
    // Fetch Donations
    $stmt = $db->prepare("
        SELECT 
            dp.id,
            'donation' AS type,
            CASE 
                WHEN dp.donation_type_id = -1 THEN 'Custom One-Time Donation'
                WHEN dp.donation_type_id = -2 THEN 'Custom Monthly Donation'
                ELSE dt.label
            END AS title,
            dp.amount_paid AS amount,
            dp.payment_status,
            dp.stripe_payment_intent_id,
            dp.created_at
        FROM donation_payments dp
        LEFT JOIN donation_types dt ON dp.donation_type_id = dt.id
        WHERE dp.user_id = ?
        ORDER BY dp.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($donations as $donation) {
        $transactions[] = $donation;
    }
    
    // Fetch Sponsorship Payments
    $stmt = $db->prepare("
        SELECT 
            sp.id,
            'sponsorship' AS type,
            pkg.name AS title,
            sp.amount_paid AS amount,
            sp.payment_status,
            sp.stripe_payment_intent_id,
            sp.created_at
        FROM sponsorship_payments sp
        INNER JOIN sponsorship_packages pkg ON sp.package_id = pkg.id
        WHERE sp.user_id = ?
        ORDER BY sp.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $sponsorships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sponsorships as $sponsorship) {
        $transactions[] = $sponsorship;
    }
    
    // Fetch Store Orders
    $stmt = $db->prepare("
        SELECT 
            o.id,
            'store' AS type,
            CONCAT('Order #', o.id) AS title,
            o.total_amount AS amount,
            o.payment_status,
            o.stripe_payment_intent_id,
            o.created_at
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($orders as $order) {
        $transactions[] = $order;
    }
    
    // Sort all transactions by date (newest first)
    usort($transactions, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Map payment_status 'completed' to 'succeeded' for frontend compatibility
    foreach ($transactions as &$transaction) {
        if ($transaction['payment_status'] === 'completed') {
            $transaction['payment_status'] = 'succeeded';
        }
    }
    
    // Return transactions
    json_success([
        'transactions' => $transactions,
        'count' => count($transactions)
    ]);
    
} catch (PDOException $e) {
    error_log('Transactions fetch error: ' . $e->getMessage());
    json_error('Failed to fetch transactions. Please try again later.', 500);
}
