<?php
/**
 * File: api/webhook/stripe.php
 * Endpoint: POST /api/webhook/stripe
 * 
 * Stripe Webhook Handler
 * Receives webhook events from Stripe servers to confirm payments.
 * Verifies webhook signature, updates payment status in database, and sends confirmation emails.
 * 
 * Events handled:
 * - payment_intent.succeeded → Update payment status to 'completed', send confirmation email
 * - payment_intent.payment_failed → Update payment status to 'failed'
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/stripe.php';
require_once __DIR__ . '/../../includes/mail.php';

// Webhook endpoint does NOT need session - it's called by Stripe servers
// No session_init() here

// Set JSON content type
header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get raw POST body (Stripe sends JSON)
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Verify webhook signature
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        STRIPE_WEBHOOK_SECRET
    );
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    error_log('Webhook error: Invalid payload - ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log('Webhook error: Invalid signature - ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Handle the event
$event_type = $event->type;
$payment_intent = $event->data->object; // Contains the PaymentIntent object

try {
    $db = get_db();
    
    switch ($event_type) {
        case 'payment_intent.succeeded':
            handlePaymentSuccess($db, $payment_intent);
            break;
            
        case 'payment_intent.payment_failed':
            handlePaymentFailure($db, $payment_intent);
            break;
            
        default:
            // Log unhandled event types
            error_log("Unhandled webhook event type: {$event_type}");
    }
    
    // Always return 200 OK to Stripe (even if we don't handle the event)
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Webhook processing error: ' . $e->getMessage());
    // Still return 200 to prevent Stripe from retrying
    http_response_code(200);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

/**
 * Handle successful payment
 */
function handlePaymentSuccess($db, $payment_intent) {
    $metadata = $payment_intent->metadata;
    $payment_type = $metadata->type ?? null;
    
    if (!$payment_type) {
        error_log("Webhook: Missing payment type in PaymentIntent {$payment_intent->id}");
        return;
    }
    
    switch ($payment_type) {
        case 'event':
            updateEventRegistration($db, $payment_intent, $metadata);
            break;
            
        case 'donation':
            updateDonation($db, $payment_intent, $metadata);
            break;
            
        case 'sponsorship':
            updateSponsorship($db, $payment_intent, $metadata);
            break;
            
        case 'store':
            updateStoreOrder($db, $payment_intent, $metadata);
            break;
            
        default:
            error_log("Webhook: Unknown payment type '{$payment_type}' in PaymentIntent {$payment_intent->id}");
    }
}

/**
 * Handle failed payment
 */
function handlePaymentFailure($db, $payment_intent) {
    $metadata = $payment_intent->metadata;
    $payment_type = $metadata->type ?? null;
    
    if (!$payment_type) {
        error_log("Webhook: Missing payment type in PaymentIntent {$payment_intent->id}");
        return;
    }
    
    // Update payment status to 'failed' by finding record via stripe_payment_intent_id
    switch ($payment_type) {
        case 'event':
            $stmt = $db->prepare("UPDATE event_registrations SET payment_status = 'failed' WHERE stripe_payment_intent_id = ?");
            $stmt->execute([$payment_intent->id]);
            break;
            
        case 'donation':
            $stmt = $db->prepare("UPDATE donation_payments SET payment_status = 'failed' WHERE stripe_payment_intent_id = ?");
            $stmt->execute([$payment_intent->id]);
            break;
            
        case 'sponsorship':
            $stmt = $db->prepare("UPDATE sponsorship_payments SET payment_status = 'failed' WHERE stripe_payment_intent_id = ?");
            $stmt->execute([$payment_intent->id]);
            break;
            
        case 'store':
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'failed' WHERE stripe_payment_intent_id = ?");
            $stmt->execute([$payment_intent->id]);
            break;
    }
}

/**
 * Update event registration and send confirmation email
 */
function updateEventRegistration($db, $payment_intent, $metadata) {
    $amount_paid = $payment_intent->amount / 100; // Convert from cents
    
    // Find registration by stripe_payment_intent_id
    $stmt = $db->prepare("
        SELECT id FROM event_registrations 
        WHERE stripe_payment_intent_id = ?
    ");
    $stmt->execute([$payment_intent->id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        error_log("Webhook: Event registration not found for PaymentIntent {$payment_intent->id}");
        return;
    }
    
    $registration_id = $record['id'];
    
    // Update registration status
    $stmt = $db->prepare("
        UPDATE event_registrations 
        SET payment_status = 'completed', 
            amount_paid = ?
        WHERE id = ?
    ");
    $stmt->execute([$amount_paid, $registration_id]);
    
    // Get registration details for email
    $stmt = $db->prepare("
        SELECT er.*, e.title as event_title, e.event_date, e.location, u.email, u.first_name, u.last_name
        FROM event_registrations er
        JOIN events e ON er.event_id = e.id
        LEFT JOIN users u ON er.user_id = u.id
        WHERE er.id = ?
    ");
    $stmt->execute([$registration_id]);
    $registration = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($registration) {
        // Send confirmation email to user
        sendEventConfirmationEmail($registration, $amount_paid);
        
        // Send notification to admins
        notifyAdminsEventRegistration($db, $registration, $amount_paid);
    }
}

/**
 * Update donation and send thank you email
 */
function updateDonation($db, $payment_intent, $metadata) {
    $amount_paid = $payment_intent->amount / 100;
    
    // Find donation by stripe_payment_intent_id
    $stmt = $db->prepare("
        SELECT id FROM donation_payments 
        WHERE stripe_payment_intent_id = ?
    ");
    $stmt->execute([$payment_intent->id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        error_log("Webhook: Donation payment not found for PaymentIntent {$payment_intent->id}");
        return;
    }
    
    $donation_id = $record['id'];
    
    // Update donation status
    $stmt = $db->prepare("
        UPDATE donation_payments 
        SET payment_status = 'completed',
            amount_paid = ?
        WHERE id = ?
    ");
    $stmt->execute([$amount_paid, $donation_id]);
    
    // Get donation details for email
    $stmt = $db->prepare("
        SELECT dp.*, dt.label as donation_label, dt.is_recurring, u.email, u.first_name, u.last_name
        FROM donation_payments dp
        LEFT JOIN donation_types dt ON dp.donation_type_id = dt.id
        LEFT JOIN users u ON dp.user_id = u.id
        WHERE dp.id = ?
    ");
    $stmt->execute([$donation_id]);
    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($donation) {
        // Send thank you email to user
        sendDonationThankYouEmail($donation, $amount_paid);
        
        // Send notification to admins
        notifyAdminsDonation($db, $donation, $amount_paid);
    }
}

/**
 * Update sponsorship and send welcome email
 */
function updateSponsorship($db, $payment_intent, $metadata) {
    $amount_paid = $payment_intent->amount / 100;
    
    // Find sponsorship by stripe_payment_intent_id
    $stmt = $db->prepare("
        SELECT id FROM sponsorship_payments 
        WHERE stripe_payment_intent_id = ?
    ");
    $stmt->execute([$payment_intent->id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        error_log("Webhook: Sponsorship payment not found for PaymentIntent {$payment_intent->id}");
        return;
    }
    
    $sponsorship_id = $record['id'];
    
    // Update sponsorship status
    $stmt = $db->prepare("
        UPDATE sponsorship_payments 
        SET payment_status = 'completed',
            amount_paid = ?
        WHERE id = ?
    ");
    $stmt->execute([$amount_paid, $sponsorship_id]);
    
    // Get sponsorship details for email
    $stmt = $db->prepare("
        SELECT sp.*, pkg.name as package_name, u.email, u.first_name, u.last_name
        FROM sponsorship_payments sp
        JOIN sponsorship_packages pkg ON sp.package_id = pkg.id
        LEFT JOIN users u ON sp.user_id = u.id
        WHERE sp.id = ?
    ");
    $stmt->execute([$sponsorship_id]);
    $sponsorship = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sponsorship) {
        // Send welcome email to user
        sendSponsorshipWelcomeEmail($sponsorship, $amount_paid);
        
        // Send notification to admins
        notifyAdminsSponsorship($db, $sponsorship, $amount_paid);
    }
}

/**
 * Update store order and send confirmation email
 */
function updateStoreOrder($db, $payment_intent, $metadata) {
    $amount_paid = $payment_intent->amount / 100;
    
    // Find order by stripe_payment_intent_id
    $stmt = $db->prepare("
        SELECT id FROM orders 
        WHERE stripe_payment_intent_id = ?
    ");
    $stmt->execute([$payment_intent->id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        error_log("Webhook: Order not found for PaymentIntent {$payment_intent->id}");
        return;
    }
    
    $order_id = $record['id'];
    
    // Update order status
    $stmt = $db->prepare("
        UPDATE orders 
        SET payment_status = 'completed'
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);
    
    // Get order details with items for email
    $stmt = $db->prepare("
        SELECT o.*, u.email, u.first_name, u.last_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        // Get order items
        $stmt = $db->prepare("
            SELECT * FROM order_items WHERE order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Send confirmation email to user
        sendOrderConfirmationEmail($order, $amount_paid);
        
        // Send notification to admins
        notifyAdminsStoreOrder($db, $order, $amount_paid);
    }
}

/**
 * Send event registration confirmation email
 */
function sendEventConfirmationEmail($registration, $amount) {
    $to = $registration['email'] ?: $registration['participant_email'];
    if (!$to) {
        error_log("Cannot send event confirmation: missing email for registration ID {$registration['id']}");
        return;
    }
    
    $name = $registration['first_name'] ?? $registration['participant_name'];
    
    $subject = "Event Registration Confirmed - {$registration['event_title']}";
    
    $body = "
        <h2>Registration Confirmed!</h2>
        <p>Hi {$name},</p>
        <p>Thank you for registering for <strong>{$registration['event_title']}</strong>!</p>
        
        <h3>Event Details:</h3>
        <ul>
            <li><strong>Event:</strong> {$registration['event_title']}</li>
            <li><strong>Date:</strong> {$registration['event_date']}</li>
            <li><strong>Location:</strong> {$registration['location']}</li>
            <li><strong>Amount Paid:</strong> \${$amount}</li>
        </ul>
        
        <h3>Participant Information:</h3>
        <ul>
            <li><strong>Name:</strong> {$registration['participant_name']}</li>
            <li><strong>Email:</strong> {$registration['participant_email']}</li>
            <li><strong>Phone:</strong> {$registration['participant_phone']}</li>
        </ul>
        
        <p>We'll send you more details about the event closer to the date.</p>
        <p>See you there!</p>
        
        <p>Best regards,<br>Tour de Roar Team</p>
    ";
    
    send_email($to, $subject, $body);
}

/**
 * Send donation thank you email
 */
function sendDonationThankYouEmail($donation, $amount) {
    $to = $donation['email'] ?? null;
    if (!$to) {
        error_log("Cannot send donation email: missing email for donation ID {$donation['id']}");
        return;
    }
    
    $name = $donation['first_name'] ?? 'Friend';
    $is_recurring = $donation['is_recurring'] ?? false;
    
    $subject = "Thank You for Your Donation!";
    
    $recurring_text = $is_recurring ? " (Monthly Recurring)" : "";
    
    $body = "
        <h2>Thank You for Your Generous Donation!</h2>
        <p>Hi {$name},</p>
        <p>We are incredibly grateful for your donation of <strong>\${$amount}{$recurring_text}</strong>.</p>
        
        <p>Your contribution helps us:</p>
        <ul>
            <li>Provide cycling programs for children in our community</li>
            <li>Promote physical and mental health through cycling</li>
            <li>Make a lasting impact on children's lives</li>
        </ul>
        
        <p>Your generosity makes our mission possible. Thank you for being part of our journey!</p>
        
        <p>With gratitude,<br>Tour de Roar Team</p>
    ";
    
    send_email($to, $subject, $body);
}

/**
 * Send sponsorship welcome email
 */
function sendSponsorshipWelcomeEmail($sponsorship, $amount) {
    $to = $sponsorship['contact_email'] ?? null;
    if (!$to) {
        error_log("Cannot send sponsorship email: missing contact_email for sponsorship ID {$sponsorship['id']}");
        return;
    }
    
    $subject = "Welcome as a {$sponsorship['package_name']}!";
    
    $body = "
        <h2>Welcome to Tour de Roar!</h2>
        <p>Thank you for becoming a <strong>{$sponsorship['package_name']}</strong>!</p>
        
        <h3>Sponsorship Details:</h3>
        <ul>
            <li><strong>Company:</strong> {$sponsorship['company_name']}</li>
            <li><strong>Package:</strong> {$sponsorship['package_name']}</li>
            <li><strong>Amount:</strong> \${$amount}</li>
        </ul>
        
        <p>Our team will contact you within 48 hours to discuss:</p>
        <ul>
            <li>Logo placement and marketing materials</li>
            <li>Event participation opportunities</li>
            <li>Community engagement activities</li>
            <li>Your sponsorship benefits</li>
        </ul>
        
        <p>We're excited to partner with you in making a difference!</p>
        
        <p>Best regards,<br>Tour de Roar Team</p>
    ";
    
    send_email($to, $subject, $body);
}

/**
 * Send store order confirmation email
 */
function sendOrderConfirmationEmail($order, $amount) {
    $to = $order['shipping_email'] ?? null;
    if (!$to) {
        error_log("Cannot send order confirmation: missing shipping_email for order ID {$order['id']}");
        return;
    }
    
    $name = $order['first_name'] ?? $order['shipping_name'];
    
    $subject = "Order Confirmation #" . $order['id'];
    
    // Build items list
    $items_html = "";
    if (!empty($order['items'])) {
        foreach ($order['items'] as $item) {
            $size_text = !empty($item['size']) ? " (Size: {$item['size']})" : "";
            $line_total = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
            $product_name = $item['product_name'] ?? 'Unknown Product';
            $quantity = $item['quantity'] ?? 0;
            $items_html .= "<li>{$quantity}x {$product_name}{$size_text} - \${$line_total}</li>";
        }
    }
    
    $body = "
        <h2>Order Confirmed!</h2>
        <p>Hi {$name},</p>
        <p>Thank you for your order! We're preparing your items for shipment.</p>
        
        <h3>Order #" . $order['id'] . "</h3>
        <ul>
            {$items_html}
        </ul>
        <p><strong>Total:</strong> \${$amount}</p>
        
        <h3>Shipping Address:</h3>
        <p>{$order['shipping_address']}</p>
        
        <p>We'll send you tracking information once your order ships.</p>
        
        <p>Thank you for supporting Tour de Roar!</p>
        
        <p>Best regards,<br>Tour de Roar Team</p>
    ";
    
    send_email($to, $subject, $body);
}

/**
 * Get all active admin email addresses
 */
function getAdminEmails($db) {
    $stmt = $db->prepare("SELECT email FROM admins WHERE status = 'active'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Notify admins of new event registration
 */
function notifyAdminsEventRegistration($db, $registration, $amount) {
    $admin_emails = getAdminEmails($db);
    if (empty($admin_emails)) return;
    
    $subject = "New Event Registration - {$registration['event_title']}";
    
    $body = "
        <h2>New Event Registration</h2>
        <p>A new event registration has been completed.</p>
        
        <h3>Event Details:</h3>
        <ul>
            <li><strong>Event:</strong> {$registration['event_title']}</li>
            <li><strong>Date:</strong> {$registration['event_date']}</li>
            <li><strong>Location:</strong> {$registration['location']}</li>
            <li><strong>Amount Paid:</strong> \${$amount}</li>
        </ul>
        
        <h3>Participant Information:</h3>
        <ul>
            <li><strong>Name:</strong> {$registration['participant_name']}</li>
            <li><strong>Email:</strong> {$registration['participant_email']}</li>
            <li><strong>Phone:</strong> {$registration['participant_phone']}</li>
            <li><strong>Registration ID:</strong> #{$registration['id']}</li>
        </ul>
        
        <p>View this registration in the admin portal.</p>
    ";
    
    foreach ($admin_emails as $admin_email) {
        send_email($admin_email, $subject, $body);
    }
}

/**
 * Notify admins of new donation
 */
function notifyAdminsDonation($db, $donation, $amount) {
    $admin_emails = getAdminEmails($db);
    if (empty($admin_emails)) return;
    
    $donor_name = $donation['first_name'] ? "{$donation['first_name']} {$donation['last_name']}" : 'Anonymous';
    $donor_email = $donation['email'] ?? 'No email on file';
    $donation_label = $donation['donation_label'] ?? 'Custom Donation';
    $is_recurring = $donation['is_recurring'] ?? false;
    $recurring_text = $is_recurring ? " (Monthly Recurring)" : "";
    
    $subject = "New Donation Received - \${$amount}{$recurring_text}";
    
    $body = "
        <h2>New Donation Received!</h2>
        <p>A new donation has been completed.</p>
        
        <h3>Donation Details:</h3>
        <ul>
            <li><strong>Amount:</strong> \${$amount}{$recurring_text}</li>
            <li><strong>Type:</strong> {$donation_label}</li>
            <li><strong>Donor:</strong> {$donor_name}</li>
            <li><strong>Email:</strong> {$donor_email}</li>
            <li><strong>Donation ID:</strong> #{$donation['id']}</li>
        </ul>
        
        <p>View this donation in the admin portal.</p>
    ";
    
    foreach ($admin_emails as $admin_email) {
        send_email($admin_email, $subject, $body);
    }
}

/**
 * Notify admins of new sponsorship
 */
function notifyAdminsSponsorship($db, $sponsorship, $amount) {
    $admin_emails = getAdminEmails($db);
    if (empty($admin_emails)) return;
    
    $subject = "New Sponsorship - {$sponsorship['package_name']}";
    
    $body = "
        <h2>New Sponsorship!</h2>
        <p>A new sponsorship payment has been completed.</p>
        
        <h3>Sponsorship Details:</h3>
        <ul>
            <li><strong>Company:</strong> {$sponsorship['company_name']}</li>
            <li><strong>Package:</strong> {$sponsorship['package_name']}</li>
            <li><strong>Amount:</strong> \${$amount}</li>
            <li><strong>Contact Email:</strong> {$sponsorship['contact_email']}</li>
            <li><strong>Sponsorship ID:</strong> #{$sponsorship['id']}</li>
        </ul>
        
        <p><strong>Action Required:</strong> Contact the sponsor within 48 hours to discuss partnership details.</p>
        
        <p>View this sponsorship in the admin portal.</p>
    ";
    
    foreach ($admin_emails as $admin_email) {
        send_email($admin_email, $subject, $body);
    }
}

/**
 * Notify admins of new store order
 */
function notifyAdminsStoreOrder($db, $order, $amount) {
    $admin_emails = getAdminEmails($db);
    if (empty($admin_emails)) return;
    
    $customer_name = $order['first_name'] ? "{$order['first_name']} {$order['last_name']}" : ($order['shipping_name'] ?? 'Unknown Customer');
    $customer_email = $order['shipping_email'] ?? 'No email provided';
    
    // Build items list
    $items_html = "";
    if (!empty($order['items'])) {
        foreach ($order['items'] as $item) {
            $size_text = !empty($item['size']) ? " (Size: {$item['size']})" : "";
            $line_total = ($item['unit_price'] ?? 0) * ($item['quantity'] ?? 0);
            $product_name = $item['product_name'] ?? 'Unknown Product';
            $quantity = $item['quantity'] ?? 0;
            $items_html .= "<li>{$quantity}x {$product_name}{$size_text} - \${$line_total}</li>";
        }
    }
    
    $subject = "New Store Order #" . $order['id'];
    
    $body = "
        <h2>New Store Order!</h2>
        <p>A new store order has been completed and requires fulfillment.</p>
        
        <h3>Order #" . $order['id'] . "</h3>
        <ul>
            {$items_html}
        </ul>
        <p><strong>Total:</strong> \${$amount}</p>
        
        <h3>Customer Information:</h3>
        <ul>
            <li><strong>Name:</strong> {$customer_name}</li>
            <li><strong>Email:</strong> {$customer_email}</li>
        </ul>
        
        <h3>Shipping Address:</h3>
        <p>{$order['shipping_address']}</p>
        
        <p><strong>Action Required:</strong> Process this order and arrange shipment.</p>
        
        <p>View this order in the admin portal.</p>
    ";
    
    foreach ($admin_emails as $admin_email) {
        send_email($admin_email, $subject, $body);
    }
}

