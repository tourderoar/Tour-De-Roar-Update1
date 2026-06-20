<?php
/**
 * Admin Sponsorships Management
 * View, create, edit, and delete sponsorship packages
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Sponsorships';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all sponsorship packages
$stmt = $db->query("SELECT * FROM sponsorship_packages ORDER BY price DESC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Sponsorship Packages</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Manage corporate sponsorship tiers</p>
    </div>
    <button onclick="openPackageModal()" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>Add New Package
    </button>
</div>

<!-- Packages Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Package Name</th>
                <th>Price</th>
                <th>Sponsors</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="packages-tbody">
            <?php if (empty($packages)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-handshake text-4xl mb-4" style="display: block;"></i>
                        No sponsorship packages yet. Click "Add New Package" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($packages as $pkg): ?>
                    <tr data-package-id="<?= $pkg['id'] ?>">
                        <td>#<?= $pkg['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($pkg['name']) ?></strong>
                        </td>
                        <td>$<?= number_format($pkg['price'], 2) ?></td>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(*) FROM sponsorship_payments WHERE package_id = ? AND payment_status = 'completed'");
                            $stmt->execute([$pkg['id']]);
                            $sponsors_count = $stmt->fetchColumn();
                            echo $sponsors_count;
                            ?>
                        </td>
                        <td>
                            <?php if ($pkg['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick='editPackage(<?= htmlspecialchars(json_encode($pkg), ENT_QUOTES) ?>)' class="btn-secondary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deletePackage(<?= $pkg['id'] ?>)" class="btn-secondary btn-icon" title="Delete" style="background: #E53E3E; margin-left: 0.5rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Package Modal -->
<div id="package-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add New Package</h3>
            <button type="button" onclick="closePackageModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="package-form">
                <input type="hidden" id="package-id" name="id">
                
                <div class="form-group">
                    <label class="form-label">Package Name *</label>
                    <input type="text" id="package-name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea id="package-description" name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Benefits (one per line) *</label>
                    <textarea id="package-benefits" name="benefits" class="form-textarea" rows="5" required placeholder="Logo on website&#10;Event booth space&#10;Social media mentions"></textarea>
                    <p style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">Enter each benefit on a new line</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Price ($) *</label>
                    <input type="number" id="package-price" name="price" class="form-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="package-status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save mr-2"></i>Save Package
                    </button>
                    <button type="button" onclick="closePackageModal()" class="btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editingPackageId = null;

function openPackageModal() {
    editingPackageId = null;
    document.getElementById('modal-title').textContent = 'Add New Package';
    document.getElementById('package-form').reset();
    document.getElementById('package-id').value = '';
    showModal('package-modal');
}

function closePackageModal() {
    hideModal('package-modal');
    editingPackageId = null;
}

function editPackage(pkg) {
    editingPackageId = pkg.id;
    document.getElementById('modal-title').textContent = 'Edit Package';
    document.getElementById('package-id').value = pkg.id;
    document.getElementById('package-name').value = pkg.name;
    document.getElementById('package-description').value = pkg.description;
    
    // Convert perks_json array to newline-separated text
    let perks = '';
    try {
        const perksArray = typeof pkg.perks_json === 'string' ? JSON.parse(pkg.perks_json) : pkg.perks_json;
        perks = Array.isArray(perksArray) ? perksArray.join('\n') : '';
    } catch (e) {
        perks = '';
    }
    document.getElementById('package-benefits').value = perks;
    
    document.getElementById('package-price').value = pkg.price;
    document.getElementById('package-status').value = pkg.status;
    showModal('package-modal');
}

async function deletePackage(id) {
    if (!confirmDelete('Are you sure you want to delete this package? Existing sponsorships will remain.')) {
        return;
    }
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/sponsorships', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, csrf_token: CSRF_TOKEN })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Package deleted successfully', 'success');
            document.querySelector(`tr[data-package-id="${id}"]`).remove();
        } else {
            showToast(data.error || 'Failed to delete package', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
}

// Handle form submission
document.getElementById('package-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Convert benefits from newline-separated text to array
    const benefitsText = data.benefits || '';
    const benefitsArray = benefitsText.split('\n').map(b => b.trim()).filter(b => b.length > 0);
    data.perks_json = JSON.stringify(benefitsArray);
    delete data.benefits; // Remove the old field name
    
    data.csrf_token = CSRF_TOKEN;
    
    const method = editingPackageId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/sponsorships', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(editingPackageId ? 'Package updated successfully' : 'Package created successfully', 'success');
            closePackageModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(result.error || 'Failed to save package', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>