<?php
/**
 * Admin Donations Management
 * View, create, edit, and delete donation types
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Donations';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all donation types
$stmt = $db->query("SELECT * FROM donation_types ORDER BY is_recurring ASC, amount ASC");
$donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Donation Types</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Manage suggested donation amounts</p>
    </div>
    <button onclick="openDonationModal()" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>Add Donation Type
    </button>
</div>

<!-- Donations Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Label</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Donations Received</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="donations-tbody">
            <?php if (empty($donations)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-heart text-4xl mb-4" style="display: block;"></i>
                        No donation types yet. Click "Add Donation Type" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($donations as $donation): ?>
                    <tr data-donation-id="<?= $donation['id'] ?>">
                        <td>#<?= $donation['id'] ?></td>
                        <td>
                            <?php if ($donation['is_recurring']): ?>
                                <span class="badge" style="background: #fbbf24; color: #1a202c;">Monthly</span>
                            <?php else: ?>
                                <span class="badge" style="background: #34d399; color: #1a202c;">One-Time</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($donation['label']) ?></strong>
                        </td>
                        <td><?php echo $donation['is_recurring'] ? '$' . number_format($donation['amount'], 2) . '/mo' : '$' . number_format($donation['amount'], 2); ?></td>
                        <td><?= htmlspecialchars(substr($donation['description'] ?? '', 0, 50)) ?><?= !empty($donation['description']) ? '...' : '' ?></td>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(*) FROM donation_payments WHERE donation_type_id = ? AND payment_status = 'completed'");
                            $stmt->execute([$donation['id']]);
                            $count = $stmt->fetchColumn();
                            echo $count;
                            ?>
                        </td>
                        <td>
                            <?php if ($donation['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick='editDonation(<?= htmlspecialchars(json_encode($donation), ENT_QUOTES) ?>)' class="btn-secondary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteDonation(<?= $donation['id'] ?>)" class="btn-secondary btn-icon" title="Delete" style="background: #E53E3E; margin-left: 0.5rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Donation Modal -->
<div id="donation-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add Donation Type</h3>
            <button type="button" onclick="closeDonationModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="donation-form">
                <input type="hidden" id="donation-id" name="id">
                
                <div class="form-group">
                    <label class="form-label">Donation Type *</label>
                    <select id="donation-recurring" name="is_recurring" class="form-select" required>
                        <option value="0">One-Time Donation</option>
                        <option value="1">Monthly Giving (Recurring)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Label *</label>
                    <input type="text" id="donation-label" name="label" class="form-input" required placeholder="e.g., Safety Gear">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Amount ($) *</label>
                    <input type="number" id="donation-amount" name="amount" class="form-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea id="donation-description" name="description" class="form-textarea" rows="3" placeholder="What this donation accomplishes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="donation-status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save mr-2"></i>Save Donation Type
                    </button>
                    <button type="button" onclick="closeDonationModal()" class="btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editingDonationId = null;

function openDonationModal() {
    editingDonationId = null;
    document.getElementById('modal-title').textContent = 'Add Donation Type';
    document.getElementById('donation-form').reset();
    document.getElementById('donation-id').value = '';
    showModal('donation-modal');
}

function closeDonationModal() {
    hideModal('donation-modal');
    editingDonationId = null;
}

function editDonation(donation) {
    editingDonationId = donation.id;
    document.getElementById('modal-title').textContent = 'Edit Donation Type';
    document.getElementById('donation-id').value = donation.id;
    document.getElementById('donation-recurring').value = donation.is_recurring || 0;
    document.getElementById('donation-label').value = donation.label;
    document.getElementById('donation-amount').value = donation.amount;
    document.getElementById('donation-description').value = donation.description || '';
    document.getElementById('donation-status').value = donation.status;
    showModal('donation-modal');
}

async function deleteDonation(id) {
    if (!confirmDelete('Are you sure you want to delete this donation type? Existing donations will remain.')) {
        return;
    }
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/donations', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, csrf_token: CSRF_TOKEN })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Donation type deleted successfully', 'success');
            document.querySelector(`tr[data-donation-id="${id}"]`).remove();
        } else {
            showToast(data.error || 'Failed to delete donation type', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
}

// Handle form submission
document.getElementById('donation-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    data.csrf_token = CSRF_TOKEN;
    
    const method = editingDonationId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/donations', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(editingDonationId ? 'Donation type updated successfully' : 'Donation type created successfully', 'success');
            closeDonationModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.error || 'Failed to save donation type', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
