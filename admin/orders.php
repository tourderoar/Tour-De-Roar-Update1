<?php
/**
 * Admin Orders View
 * View all store orders
 */

define('ADMIN_PAGE', true);
$page_title = 'Orders';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all orders with customer info
$stmt = $db->query("
    SELECT o.*, u.first_name, u.last_name, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Store Orders</h3>
    <p style="color: #718096; margin: 0.5rem 0 0 0;">View all customer orders and shipments</p>
</div>

<!-- Orders Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-shopping-cart text-4xl mb-4" style="display: block;"></i>
                        No orders yet
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                        <td><?= htmlspecialchars($order['email']) ?></td>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                            $stmt->execute([$order['id']]);
                            $items_count = $stmt->fetchColumn();
                            echo $items_count . ' item' . ($items_count > 1 ? 's' : '');
                            ?>
                        </td>
                        <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                        <td>
                            <?php if ($order['payment_status'] === 'completed'): ?>
                                <span class="badge badge-success">Paid</span>
                            <?php elseif ($order['payment_status'] === 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Failed</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <button onclick="viewOrderDetails(<?= $order['id'] ?>)" class="btn-secondary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Order Details Modal -->
<div id="order-modal" class="modal-overlay">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Order Details</h3>
            <button type="button" onclick="hideModal('order-modal')" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="order-details-content">
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #805AD5;"></i>
            </div>
        </div>
    </div>
</div>

<script>
async function viewOrderDetails(orderId) {
    showModal('order-modal');
    
    try {
        const response = await fetch(`<?= APP_URL ?>/api/admin/orders/${orderId}`, {
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const order = result.data;
            let html = `
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: #2d3748; margin: 0 0 1rem 0;">Order #${order.id}</h4>
                    <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <p style="margin: 0.5rem 0;"><strong>Customer:</strong> ${order.customer_name}</p>
                        <p style="margin: 0.5rem 0;"><strong>Email:</strong> ${order.customer_email}</p>
                        <p style="margin: 0.5rem 0;"><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                        <p style="margin: 0.5rem 0;"><strong>Status:</strong> 
                            <span class="badge badge-${order.payment_status === 'completed' ? 'success' : 'warning'}">${order.payment_status}</span>
                        </p>
                    </div>
                    
                    <h5 style="color: #2d3748; margin: 1.5rem 0 0.75rem 0;">Shipping Information</h5>
                    <div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <p style="margin: 0.5rem 0;"><strong>Name:</strong> ${order.shipping_name}</p>
                        <p style="margin: 0.5rem 0;"><strong>Address:</strong><br>${order.shipping_address.replace(/\n/g, '<br>')}</p>
                    </div>
                    
                    <h5 style="color: #2d3748; margin: 1.5rem 0 0.75rem 0;">Order Items</h5>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f7fafc;">
                                <th style="padding: 0.75rem; text-align: left;">Product</th>
                                <th style="padding: 0.75rem; text-align: center;">Qty</th>
                                <th style="padding: 0.75rem; text-align: right;">Price</th>
                                <th style="padding: 0.75rem; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            order.items.forEach(item => {
                html += `
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 0.75rem;">${item.product_name}</td>
                        <td style="padding: 0.75rem; text-align: center;">${item.quantity}</td>
                        <td style="padding: 0.75rem; text-align: right;">$${parseFloat(item.price).toFixed(2)}</td>
                        <td style="padding: 0.75rem; text-align: right;">$${(item.quantity * item.price).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: 700; background: #f7fafc;">
                                <td colspan="3" style="padding: 0.75rem; text-align: right;">Total:</td>
                                <td style="padding: 0.75rem; text-align: right; color: #FF6B1A; font-size: 1.25rem;">$${parseFloat(order.total_amount).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            document.getElementById('order-details-content').innerHTML = html;
        } else {
            document.getElementById('order-details-content').innerHTML = `
                <p style="color: #E53E3E; text-align: center;">Failed to load order details</p>
            `;
        }
    } catch (error) {
        document.getElementById('order-details-content').innerHTML = `
            <p style="color: #E53E3E; text-align: center;">Network error. Please try again.</p>
        `;
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
