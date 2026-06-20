<?php
/**
 * File: api/user/invoice.php
 * Location: /tour_update/api/user/invoice.php
 *
 * Generate PDF invoice for a specific transaction.
 * 
 * Method: GET
 * Params: ?type=event|donation|sponsorship|store&id=123
 * Requires: User session
 * Returns: PDF file download
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
    json_error('Please log in to view invoices', 401);
}

$user = get_logged_in_user();
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? '';

if (empty($type) || empty($id)) {
    json_error('Missing type or id parameter', 400);
}

try {
    $db = get_db();
    $transaction = null;
    
    // Fetch transaction details based on type
    switch ($type) {
        case 'event':
            $stmt = $db->prepare("
                SELECT 
                    er.id,
                    'Event Registration' AS type_label,
                    e.title,
                    e.event_date AS event_date,
                    e.location AS event_location,
                    er.participant_name,
                    er.participant_email,
                    er.amount_paid AS amount,
                    er.payment_status,
                    er.stripe_payment_intent_id,
                    er.created_at,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM event_registrations er
                INNER JOIN events e ON er.event_id = e.id
                INNER JOIN users u ON er.user_id = u.id
                WHERE er.id = ? AND er.user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
            
        case 'donation':
            $stmt = $db->prepare("
                SELECT 
                    dp.id,
                    'Donation' AS type_label,
                    CASE 
                        WHEN dp.donation_type_id = -1 THEN 'Custom One-Time Donation'
                        WHEN dp.donation_type_id = -2 THEN 'Custom Monthly Donation'
                        ELSE dt.label
                    END AS title,
                    dp.amount_paid AS amount,
                    dp.payment_status,
                    dp.stripe_payment_intent_id,
                    dp.created_at,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM donation_payments dp
                LEFT JOIN donation_types dt ON dp.donation_type_id = dt.id
                INNER JOIN users u ON dp.user_id = u.id
                WHERE dp.id = ? AND dp.user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
            
        case 'sponsorship':
            $stmt = $db->prepare("
                SELECT 
                    sp.id,
                    'Sponsorship' AS type_label,
                    pkg.name AS title,
                    sp.company_name,
                    sp.contact_email,
                    sp.amount_paid AS amount,
                    sp.payment_status,
                    sp.stripe_payment_intent_id,
                    sp.created_at,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM sponsorship_payments sp
                INNER JOIN sponsorship_packages pkg ON sp.package_id = pkg.id
                INNER JOIN users u ON sp.user_id = u.id
                WHERE sp.id = ? AND sp.user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            break;
            
        case 'store':
            $stmt = $db->prepare("
                SELECT 
                    o.id,
                    'Store Order' AS type_label,
                    CONCAT('Order #', o.id) AS title,
                    o.shipping_name,
                    o.shipping_email,
                    o.shipping_address,
                    o.total_amount AS amount,
                    o.payment_status,
                    o.stripe_payment_intent_id,
                    o.created_at,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM orders o
                INNER JOIN users u ON o.user_id = u.id
                WHERE o.id = ? AND o.user_id = ?
            ");
            $stmt->execute([$id, $user['id']]);
            $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Also get order items
            if ($transaction) {
                $stmt = $db->prepare("
                    SELECT 
                        oi.quantity,
                        oi.unit_price,
                        oi.size,
                        oi.product_name
                    FROM order_items oi
                    WHERE oi.order_id = ?
                ");
                $stmt->execute([$id]);
                $transaction['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
            
        default:
            json_error('Invalid transaction type', 400);
    }
    
    if (!$transaction) {
        json_error('Transaction not found', 404);
    }
    
    // Generate HTML invoice
    $html = generateInvoiceHTML($transaction, $type);
    
    // Output as HTML for now (can be converted to PDF later with a library)
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
    
} catch (PDOException $e) {
    error_log('Invoice generation error: ' . $e->getMessage());
    json_error('Failed to generate invoice. Please try again later.', 500);
}

function generateInvoiceHTML($transaction, $type) {
    $invoiceNumber = strtoupper($type) . '-' . str_pad($transaction['id'], 6, '0', STR_PAD_LEFT);
    $invoiceDate = date('F d, Y', strtotime($transaction['created_at']));
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Invoice ' . $invoiceNumber . '</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                padding: 40px;
                background: #f5f5f5;
            }
            .invoice {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                padding: 60px;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 40px;
                padding-bottom: 30px;
                border-bottom: 3px solid #3182CE;
            }
            .logo {
                font-size: 28px;
                font-weight: 800;
                color: #3182CE;
            }
            .invoice-details {
                text-align: right;
            }
            .invoice-number {
                font-size: 24px;
                font-weight: 700;
                color: #2d3748;
                margin-bottom: 8px;
            }
            .invoice-date {
                color: #718096;
                font-size: 14px;
            }
            .section {
                margin-bottom: 30px;
            }
            .section-title {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: #718096;
                margin-bottom: 10px;
                font-weight: 600;
            }
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 40px;
            }
            .info-block {
                background: #f7fafc;
                padding: 20px;
                border-radius: 8px;
            }
            .info-line {
                margin-bottom: 8px;
                color: #2d3748;
                line-height: 1.6;
            }
            .info-label {
                font-weight: 600;
                color: #4a5568;
                display: inline-block;
                min-width: 120px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
            }
            .items-table thead {
                background: #3182CE;
                color: white;
            }
            .items-table th {
                padding: 15px;
                text-align: left;
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .items-table td {
                padding: 15px;
                border-bottom: 1px solid #e2e8f0;
            }
            .items-table tbody tr:last-child td {
                border-bottom: none;
            }
            .total-section {
                display: flex;
                justify-content: flex-end;
                margin-top: 30px;
            }
            .total-box {
                background: #f7fafc;
                padding: 20px 30px;
                border-radius: 8px;
                min-width: 300px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                font-size: 16px;
            }
            .total-row.grand-total {
                font-size: 24px;
                font-weight: 700;
                color: #3182CE;
                border-top: 2px solid #cbd5e0;
                margin-top: 10px;
                padding-top: 15px;
            }
            .status-badge {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .status-succeeded {
                background: #d1fae5;
                color: #065f46;
            }
            .status-pending {
                background: #fef3c7;
                color: #92400e;
            }
            .footer {
                margin-top: 50px;
                padding-top: 30px;
                border-top: 2px solid #e2e8f0;
                text-align: center;
                color: #718096;
                font-size: 14px;
            }
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #3182CE;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 14px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .print-button:hover {
                background: #2c5aa0;
            }
            @media print {
                body { background: white; padding: 0; }
                .invoice { box-shadow: none; padding: 40px; }
                .print-button { display: none; }
            }
        </style>
    </head>
    <body>
        <button class="print-button" onclick="window.print()">
            🖨️ Print Invoice
        </button>
        
        <div class="invoice">
            <div class="header">
                <div class="logo">
                    🚴 Tour de Roar
                </div>
                <div class="invoice-details">
                    <div class="invoice-number">INVOICE #' . $invoiceNumber . '</div>
                    <div class="invoice-date">' . $invoiceDate . '</div>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-block">
                    <div class="section-title">Bill To</div>
                    <div class="info-line">
                        <strong>' . htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']) . '</strong>
                    </div>
                    <div class="info-line">' . htmlspecialchars($transaction['email']) . '</div>
                    ' . (!empty($transaction['phone']) ? '<div class="info-line">' . htmlspecialchars($transaction['phone']) . '</div>' : '') . '
                </div>
                
                <div class="info-block">
                    <div class="section-title">Payment Details</div>
                    <div class="info-line">
                        <span class="info-label">Type:</span>
                        ' . htmlspecialchars($transaction['type_label']) . '
                    </div>
                    <div class="info-line">
                        <span class="info-label">Status:</span>
                        <span class="status-badge status-' . $transaction['payment_status'] . '">
                            ' . ucfirst($transaction['payment_status']) . '
                        </span>
                    </div>
                    ' . (!empty($transaction['stripe_payment_intent_id']) ? '
                    <div class="info-line">
                        <span class="info-label">Payment ID:</span>
                        ' . htmlspecialchars($transaction['stripe_payment_intent_id']) . '
                    </div>' : '') . '
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>';
    
    // Add transaction-specific details
    if ($type === 'store' && !empty($transaction['items'])) {
        foreach ($transaction['items'] as $item) {
            $itemDesc = htmlspecialchars($item['product_name']);
            if ($item['size']) {
                $itemDesc .= ' (Size: ' . htmlspecialchars($item['size']) . ')';
            }
            $itemDesc .= ' × ' . $item['quantity'];
            
            $html .= '
                    <tr>
                        <td>' . $itemDesc . '</td>
                        <td style="text-align: right;">$' . number_format($item['unit_price'] * $item['quantity'], 2) . '</td>
                    </tr>';
        }
    } else {
        $description = htmlspecialchars($transaction['title'] ?? $transaction['type_label']);
        if ($type === 'event' && !empty($transaction['event_date'])) {
            $description .= '<br><small style="color: #718096;">Event Date: ' . date('F d, Y', strtotime($transaction['event_date'])) . '</small>';
            if (!empty($transaction['event_location'])) {
                $description .= '<br><small style="color: #718096;">Location: ' . htmlspecialchars($transaction['event_location']) . '</small>';
            }
        }
        
        $html .= '
                    <tr>
                        <td>' . $description . '</td>
                        <td style="text-align: right;">$' . number_format($transaction['amount'], 2) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
            </table>
            
            <div class="total-section">
                <div class="total-box">
                    <div class="total-row grand-total">
                        <span>Total Paid</span>
                        <span>$' . number_format($transaction['amount'], 2) . '</span>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Thank you for your support!</strong></p>
                <p style="margin-top: 10px;">Tour de Roar • Supporting Cycling Excellence</p>
                ' . (!empty($transaction['stripe_payment_intent_id']) ? '
                <p style="margin-top: 15px; font-size: 12px;">
                    This invoice was generated automatically. For questions, please contact us at support@tourderoar.org
                </p>' : '') . '
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
