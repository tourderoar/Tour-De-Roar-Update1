<?php
/**
 * Admin Dashboard
 * Shows overview statistics and recent activity
 */

define('ADMIN_PAGE', true);
$page_title = 'Dashboard';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get statistics
$stats = [];

// Total Events
$stmt = $db->query("SELECT COUNT(*) as total, 
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
                    FROM events");
$stats['events'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Total Products
$stmt = $db->query("SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
                    FROM products");
$stats['products'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Total Orders
$stmt = $db->query("SELECT COUNT(*) as total,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending
                    FROM orders");
$stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Total Revenue from all payment types
$revenue_query = "
    SELECT 
        COALESCE(SUM(amount_paid), 0) as total_revenue
    FROM (
        SELECT amount_paid FROM event_registrations WHERE payment_status = 'completed'
        UNION ALL
        SELECT amount_paid FROM donation_payments WHERE payment_status = 'completed'
        UNION ALL
        SELECT amount_paid FROM sponsorship_payments WHERE payment_status = 'completed'
        UNION ALL
        SELECT total_amount as amount_paid FROM orders WHERE payment_status = 'completed'
    ) as all_payments
";
$stmt = $db->query($revenue_query);
$stats['revenue'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Event Registrations
$stmt = $db->query("SELECT COUNT(*) as total,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed
                    FROM event_registrations");
$stats['registrations'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Donations
$stmt = $db->query("SELECT COUNT(*) as total,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN is_recurring = 1 THEN 1 ELSE 0 END) as recurring
                    FROM donation_payments");
$stats['donations'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Sponsorships
$stmt = $db->query("SELECT COUNT(*) as total,
                    SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed
                    FROM sponsorship_payments");
$stats['sponsorships'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Total Users (regular users only - admins are in separate table)
$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Admin count from admins table
$stmt = $db->query("SELECT COUNT(*) as total FROM admins WHERE status = 'active'");
$stats['admins'] = $stmt->fetch(PDO::FETCH_ASSOC);

// Recent Orders (last 10)
$stmt = $db->query("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Registrations (last 10)
$stmt = $db->query("
    SELECT er.*, e.title as event_title, u.first_name, u.last_name
    FROM event_registrations er
    JOIN events e ON er.event_id = e.id
    JOIN users u ON er.user_id = u.id
    ORDER BY er.created_at DESC
    LIMIT 10
");
$recent_registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Statistics Cards Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Revenue -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #68D391, #38a169);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-card-label">Total Revenue</div>
        <div class="stat-card-value">$<?= number_format($stats['revenue']['total_revenue'], 2) ?></div>
    </div>
    
    <!-- Total Events -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #3182CE, #2c5282);">
            <i class="fas fa-calendar"></i>
        </div>
        <div class="stat-card-label">Active Events</div>
        <div class="stat-card-value"><?= $stats['events']['active'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['events']['total'] ?> total
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #805AD5, #6b46c1);">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-card-label">Completed Orders</div>
        <div class="stat-card-value"><?= $stats['orders']['completed'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['orders']['pending'] ?> pending
        </div>
    </div>
    
    <!-- Total Users -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #FF6B1A, #E53E3E);">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-card-label">Total Users</div>
        <div class="stat-card-value"><?= $stats['users']['total'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['admins']['total'] ?> admins
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Event Registrations -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #F6E05E, #d69e2e);">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-card-label">Event Registrations</div>
        <div class="stat-card-value"><?= $stats['registrations']['completed'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['registrations']['total'] ?> total
        </div>
    </div>
    
    <!-- Donations -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #E53E3E, #c53030);">
            <i class="fas fa-heart"></i>
        </div>
        <div class="stat-card-label">Donations Received</div>
        <div class="stat-card-value"><?= $stats['donations']['completed'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['donations']['recurring'] ?> recurring
        </div>
    </div>
    
    <!-- Sponsorships -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #805AD5, #6b46c1);">
            <i class="fas fa-handshake"></i>
        </div>
        <div class="stat-card-label">Active Sponsors</div>
        <div class="stat-card-value"><?= $stats['sponsorships']['completed'] ?></div>
    </div>
    
    <!-- Products -->
    <div class="stat-card">
        <div class="stat-card-icon" style="background: linear-gradient(135deg, #3182CE, #2c5282);">
            <i class="fas fa-store"></i>
        </div>
        <div class="stat-card-label">Active Products</div>
        <div class="stat-card-value"><?= $stats['products']['active'] ?></div>
        <div style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
            <?= $stats['products']['total'] ?> total
        </div>
    </div>
</div>

<!-- Recent Activity Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders -->
    <div class="data-table">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; background: white;">
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #2d3748;">
                <i class="fas fa-shopping-cart mr-2" style="color: #FF6B1A;"></i>
                Recent Orders
            </h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #a0aec0; padding: 2rem;">
                            No orders yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <?php if ($order['payment_status'] === 'completed'): ?>
                                    <span class="badge badge-success">Completed</span>
                                <?php elseif ($order['payment_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div style="padding: 1rem; text-align: center; background: white;">
            <a href="<?= APP_URL ?>/admin/orders" style="color: #FF6B1A; font-weight: 600; text-decoration: none;">
                View All Orders <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
    
    <!-- Recent Registrations -->
    <div class="data-table">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; background: white;">
            <h3 style="margin: 0; font-size: 1.125rem; font-weight: 700; color: #2d3748;">
                <i class="fas fa-calendar mr-2" style="color: #3182CE;"></i>
                Recent Event Registrations
            </h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Participant</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_registrations)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #a0aec0; padding: 2rem;">
                            No registrations yet
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_registrations as $reg): ?>
                        <tr>
                            <td><?= htmlspecialchars(substr($reg['event_title'], 0, 30)) ?></td>
                            <td><?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?></td>
                            <td>$<?= number_format($reg['amount_paid'], 2) ?></td>
                            <td>
                                <?php if ($reg['payment_status'] === 'completed'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php elseif ($reg['payment_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div style="padding: 1rem; text-align: center; background: white;">
            <a href="<?= APP_URL ?>/admin/events" style="color: #3182CE; font-weight: 600; text-decoration: none;">
                View All Events <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
