<?php
/**
 * Admin Payments View
 * View all payments across all types
 */

define('ADMIN_PAGE', true);
$page_title = 'All Payments';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all payments from different sources
$payments = [];

// Event registrations - with event details
$stmt = $db->query("
    SELECT 
        er.id, 
        'event' as payment_type,
        'Event Registration' as type,
        e.title as description,
        e.event_date,
        e.location,
        er.participant_name,
        er.participant_email,
        er.participant_phone,
        er.emergency_contact,
        er.emergency_phone,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        er.amount_paid as amount,
        er.payment_status,
        er.created_at,
        er.created_at as paid_at,
        er.stripe_payment_intent_id
    FROM event_registrations er
    JOIN events e ON er.event_id = e.id
    LEFT JOIN users u ON er.user_id = u.id
");
$event_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$payments = array_merge($payments, $event_payments);

// Donations - with donation type details
$stmt = $db->query("
    SELECT 
        dp.id,
        'donation' as payment_type,
        CONCAT('Donation - ', IF(dp.is_recurring = 1, 'Monthly', 'One-time')) as type,
        COALESCE(dt.label, CONCAT('Custom $', dp.amount_paid)) as description,
        dt.description as donation_description,
        dp.is_recurring,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        dp.amount_paid as amount,
        dp.payment_status,
        dp.created_at,
        dp.created_at as paid_at,
        dp.stripe_payment_intent_id
    FROM donation_payments dp
    LEFT JOIN donation_types dt ON dp.donation_type_id = dt.id
    JOIN users u ON dp.user_id = u.id
");
$donation_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$payments = array_merge($payments, $donation_payments);

// Sponsorships - with package and company details
$stmt = $db->query("
    SELECT 
        sp.id,
        'sponsorship' as payment_type,
        'Sponsorship' as type,
        CONCAT(spkg.name, ' - ', COALESCE(sp.company_name, 'N/A')) as description,
        spkg.name as package_name,
        spkg.price as package_price,
        sp.company_name,
        sp.contact_email,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        sp.amount_paid as amount,
        sp.payment_status,
        sp.created_at,
        sp.created_at as paid_at,
        sp.stripe_payment_intent_id
    FROM sponsorship_payments sp
    LEFT JOIN sponsorship_packages spkg ON sp.package_id = spkg.id
    LEFT JOIN users u ON sp.user_id = u.id
");
$sponsor_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$payments = array_merge($payments, $sponsor_payments);

// Store orders - with order items
$stmt = $db->query("
    SELECT 
        o.id,
        'order' as payment_type,
        'Store Order' as type,
        CONCAT('Order #', o.id) as description,
        o.shipping_name,
        o.shipping_email,
        o.shipping_address,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        o.total_amount as amount,
        o.payment_status,
        o.created_at,
        o.created_at as paid_at,
        o.stripe_payment_intent_id
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
");
$order_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$payments = array_merge($payments, $order_payments);

// Sort by created_at desc
usort($payments, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>

<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">All Payments</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Combined view of all payment types</p>
    </div>
    
    <!-- Filter Dropdown -->
    <div style="display: flex; gap: 1rem; align-items: center;">
        <label for="payment-filter" style="font-weight: 600; color: #4a5568;">Filter by Type:</label>
        <select id="payment-filter" style="padding: 0.5rem 1rem; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 1rem; min-width: 200px;">
            <option value="all">All Payments</option>
            <option value="event">Event Registrations</option>
            <option value="donation">Donations</option>
            <option value="sponsorship">Sponsorships</option>
            <option value="order">Store Orders</option>
        </select>
    </div>
</div>

<!-- Payments Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th style="width: 30px;"></th>
                <th>ID</th>
                <th>Type</th>
                <th>Description</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-credit-card text-4xl mb-4" style="display: block;"></i>
                        No payments yet
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                    <tr class="payment-row" data-payment-type="<?= $payment['payment_type'] ?>" onclick="togglePaymentDetails(this)" style="cursor: pointer;">
                        <td>
                            <i class="fas fa-chevron-right expand-icon" style="transition: transform 0.3s; color: #68D391;"></i>
                        </td>
                        <td><strong>#<?= $payment['id'] ?></strong></td>
                        <td>
                            <?php if ($payment['payment_type'] === 'event'): ?>
                                <span class="badge badge-info"><?= $payment['type'] ?></span>
                            <?php elseif ($payment['payment_type'] === 'donation'): ?>
                                <span class="badge" style="background: #E53E3E; color: white;"><?= $payment['type'] ?></span>
                            <?php elseif ($payment['payment_type'] === 'sponsorship'): ?>
                                <span class="badge" style="background: #805AD5; color: white;"><?= $payment['type'] ?></span>
                            <?php else: ?>
                                <span class="badge" style="background: #FF6B1A; color: white;"><?= $payment['type'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($payment['description']) ?></td>
                        <td>
                            <?php if ($payment['payment_type'] === 'event'): ?>
                                <?= htmlspecialchars($payment['participant_name']) ?><br>
                                <small style="color: #718096;"><?= htmlspecialchars($payment['participant_email']) ?></small>
                            <?php else: ?>
                                <?= htmlspecialchars(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?><br>
                                <small style="color: #718096;"><?= htmlspecialchars($payment['email'] ?? 'N/A') ?></small>
                            <?php endif; ?>
                        </td>
                        <td><strong>$<?= number_format($payment['amount'], 2) ?></strong></td>
                        <td>
                            <?php if ($payment['payment_status'] === 'completed'): ?>
                                <span class="badge badge-success">Completed</span>
                            <?php elseif ($payment['payment_status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Failed</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($payment['created_at'])) ?></td>
                    </tr>
                    <!-- Expandable Details Row -->
                    <tr class="details-row" style="display: none;">
                        <td colspan="8" style="background: #f7fafc; padding: 1.5rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                <!-- Left Column: Customer Info -->
                                <div>
                                    <h4 style="font-weight: 700; color: #2d3748; margin-bottom: 1rem;">Customer Information</h4>
                                    <div style="background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <?php if ($payment['payment_type'] === 'event'): ?>
                                            <p style="margin: 0.5rem 0;"><strong>Participant:</strong> <?= htmlspecialchars($payment['participant_name']) ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Email:</strong> <?= htmlspecialchars($payment['participant_email']) ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Phone:</strong> <?= htmlspecialchars($payment['participant_phone'] ?? 'N/A') ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Emergency Contact:</strong> <?= htmlspecialchars($payment['emergency_contact'] ?? 'N/A') ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Emergency Phone:</strong> <?= htmlspecialchars($payment['emergency_phone'] ?? 'N/A') ?></p>
                                            <?php if (!empty($payment['first_name'])): ?>
                                                <hr style="margin: 1rem 0; border: 1px solid #e2e8f0;">
                                                <p style="margin: 0.5rem 0;"><strong>User Account:</strong> <?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></p>
                                                <p style="margin: 0.5rem 0;"><strong>User Email:</strong> <?= htmlspecialchars($payment['email']) ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p style="margin: 0.5rem 0;"><strong>Name:</strong> <?= htmlspecialchars(($payment['first_name'] ?? 'Guest') . ' ' . ($payment['last_name'] ?? '')) ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Email:</strong> <?= htmlspecialchars($payment['email'] ?? 'N/A') ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Phone:</strong> <?= htmlspecialchars($payment['phone'] ?? 'N/A') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Right Column: Payment-Specific Details -->
                                <div>
                                    <h4 style="font-weight: 700; color: #2d3748; margin-bottom: 1rem;">Payment Details</h4>
                                    <div style="background: white; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <?php if ($payment['payment_type'] === 'event'): ?>
                                            <p style="margin: 0.5rem 0;"><strong>Event:</strong> <?= htmlspecialchars($payment['description']) ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Event Date:</strong> <?= date('F d, Y', strtotime($payment['event_date'])) ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Location:</strong> <?= htmlspecialchars($payment['location']) ?></p>
                                        <?php elseif ($payment['payment_type'] === 'donation'): ?>
                                            <p style="margin: 0.5rem 0;"><strong>Type:</strong> <?= $payment['is_recurring'] ? 'Monthly Recurring' : 'One-Time' ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Donation Level:</strong> <?= htmlspecialchars($payment['description']) ?></p>
                                            <?php if (!empty($payment['donation_description'])): ?>
                                                <p style="margin: 0.5rem 0;"><strong>Impact:</strong> <?= htmlspecialchars($payment['donation_description']) ?></p>
                                            <?php endif; ?>
                                        <?php elseif ($payment['payment_type'] === 'sponsorship'): ?>
                                            <p style="margin: 0.5rem 0;"><strong>Package:</strong> <?= htmlspecialchars($payment['package_name'] ?? 'N/A') ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Company:</strong> <?= htmlspecialchars($payment['company_name'] ?? 'N/A') ?></p>
                                            <p style="margin: 0.5rem 0;"><strong>Contact Email:</strong> <?= htmlspecialchars($payment['contact_email'] ?? 'N/A') ?></p>
                                        <?php elseif ($payment['payment_type'] === 'order'): ?>
                                            <p style="margin: 0.5rem 0;"><strong>Order:</strong> #<?= $payment['id'] ?></p>
                                            <?php if (!empty($payment['shipping_name'])): ?>
                                                <p style="margin: 0.5rem 0;"><strong>Shipping Name:</strong> <?= htmlspecialchars($payment['shipping_name']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($payment['shipping_email'])): ?>
                                                <p style="margin: 0.5rem 0;"><strong>Shipping Email:</strong> <?= htmlspecialchars($payment['shipping_email']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($payment['shipping_address'])): ?>
                                                <p style="margin: 0.5rem 0;"><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($payment['shipping_address'])) ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <p style="margin: 0.5rem 0;"><strong>Payment ID:</strong> <?= htmlspecialchars(substr($payment['stripe_payment_intent_id'] ?? 'N/A', 0, 30)) ?></p>
                                        <p style="margin: 0.5rem 0;"><strong>Amount:</strong> $<?= number_format($payment['amount'], 2) ?></p>
                                        <p style="margin: 0.5rem 0;"><strong>Status:</strong> <?= ucfirst($payment['payment_status']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
// Filter payments by type
document.getElementById('payment-filter').addEventListener('change', function() {
    const filterValue = this.value;
    const rows = document.querySelectorAll('.payment-row');
    
    rows.forEach(row => {
        const paymentType = row.getAttribute('data-payment-type');
        const detailsRow = row.nextElementSibling;
        
        if (filterValue === 'all' || paymentType === filterValue) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
            // Also hide the details row if it was expanded
            if (detailsRow && detailsRow.classList.contains('details-row')) {
                detailsRow.style.display = 'none';
                // Reset the icon
                const icon = row.querySelector('.expand-icon');
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }
    });
});

// Toggle payment details
function togglePaymentDetails(row) {
    const detailsRow = row.nextElementSibling;
    const icon = row.querySelector('.expand-icon');
    
    if (detailsRow && detailsRow.classList.contains('details-row')) {
        if (detailsRow.style.display === 'none' || !detailsRow.style.display) {
            detailsRow.style.display = 'table-row';
            icon.style.transform = 'rotate(90deg)';
        } else {
            detailsRow.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
}

// Add hover effect to clickable rows
document.querySelectorAll('.payment-row').forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.style.background = '#f7fafc';
    });
    row.addEventListener('mouseleave', function() {
        this.style.background = '';
    });
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
