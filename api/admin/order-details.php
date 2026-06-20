<?php
/**
 * Admin Order Details API
 * Get detailed information about a specific order
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Start session and require admin authentication
session_init();
require_admin();

$db = get_db();

// Get order ID from URL
$segments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$order_id = end($segments);

if (!is_numeric($order_id)) {
    json_error('Invalid order ID', 400);
}

// Get order with customer info
$stmt = $db->prepare("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    json_error('Order not found', 404);
}

// Get order items with product details
$stmt = $db->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Combine data
$order['customer_name'] = $order['first_name'] . ' ' . $order['last_name'];
$order['customer_email'] = $order['email'];
$order['items'] = $items;

json_success($order);
