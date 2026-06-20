<?php
/**
 * Admin Products Management
 * View, create, edit, and delete products
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Products';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all products
$stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Products Management</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Manage store merchandise and products</p>
    </div>
    <button onclick="openProductModal()" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>Add New Product
    </button>
</div>

<!-- Products Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Orders</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="products-tbody">
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-store text-4xl mb-4" style="display: block;"></i>
                        No products yet. Click "Add New Product" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr data-product-id="<?= $product['id'] ?>">
                        <td>#<?= $product['id'] ?></td>
                        <td>
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="<?= APP_URL ?>/images/products/<?= htmlspecialchars($product['image_path']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="color: #a0aec0;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                        </td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td>
                            <?php if ($product['stock'] > 10): ?>
                                <span class="badge badge-success"><?= $product['stock'] ?> in stock</span>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="badge badge-warning"><?= $product['stock'] ?> left</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Out of stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT SUM(quantity) FROM order_items WHERE product_id = ?");
                            $stmt->execute([$product['id']]);
                            $orders_count = $stmt->fetchColumn() ?? 0;
                            echo $orders_count;
                            ?>
                        </td>
                        <td>
                            <?php if ($product['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick='editProduct(<?= htmlspecialchars(json_encode($product), ENT_QUOTES) ?>)' class="btn-secondary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteProduct(<?= $product['id'] ?>)" class="btn-secondary btn-icon" title="Delete" style="background: #E53E3E; margin-left: 0.5rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Product Modal -->
<div id="product-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add New Product</h3>
            <button type="button" onclick="closeProductModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="product-form">
                <input type="hidden" id="product-id" name="id">
                
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" id="product-name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea id="product-description" name="description" class="form-textarea" rows="3" required></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" id="product-price" name="price" class="form-input" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" id="product-stock" name="stock" class="form-input" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="hidden" id="product-image" name="image_path">
                    <input type="file" id="product-image-upload" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="form-input" style="padding: 0.5rem;">
                    <p style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
                        Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP
                    </p>
                    <div id="image-preview-container" style="margin-top: 1rem; display: none;">
                        <img id="image-preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="product-status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save mr-2"></i>Save Product
                    </button>
                    <button type="button" onclick="closeProductModal()" class="btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editingProductId = null;

function openProductModal() {
    editingProductId = null;
    document.getElementById('modal-title').textContent = 'Add New Product';
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';
    document.getElementById('product-image-upload').value = '';
    document.getElementById('image-preview-container').style.display = 'none';
    showModal('product-modal');
}

function closeProductModal() {
    hideModal('product-modal');
    editingProductId = null;
    document.getElementById('product-image-upload').value = '';
    document.getElementById('image-preview-container').style.display = 'none';
}

function editProduct(product) {
    editingProductId = product.id;
    document.getElementById('modal-title').textContent = 'Edit Product';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-description').value = product.description;
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-stock').value = product.stock;
    document.getElementById('product-image').value = product.image_path || '';
    document.getElementById('product-status').value = product.status;
    
    // Show existing image preview if available
    if (product.image_path) {
        const preview = document.getElementById('image-preview');
        const container = document.getElementById('image-preview-container');
        preview.src = '<?= APP_URL ?>/images/products/' + product.image_path;
        container.style.display = 'block';
    }
    
    showModal('product-modal');
}

async function deleteProduct(id) {
    if (!confirmDelete('Are you sure you want to delete this product? Orders containing this product will remain.')) {
        return;
    }
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/products', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, csrf_token: CSRF_TOKEN })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Product deleted successfully', 'success');
            document.querySelector(`tr[data-product-id="${id}"]`).remove();
        } else {
            showToast(data.error || 'Failed to delete product', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
}

// Handle image preview
document.getElementById('product-image-upload').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToast('File size exceeds 5MB limit', 'error');
            e.target.value = '';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.getElementById('image-preview');
            const container = document.getElementById('image-preview-container');
            preview.src = event.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Handle form submission
document.getElementById('product-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        // Upload image first if selected
        const imageFile = document.getElementById('product-image-upload').files[0];
        if (imageFile) {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('type', 'products');
            uploadFormData.append('csrf_token', CSRF_TOKEN);
            
            const uploadResponse = await fetch('<?= APP_URL ?>/api/upload', {
                method: 'POST',
                body: uploadFormData
            });
            
            const uploadData = await uploadResponse.json();
            if (!uploadData.success) {
                throw new Error(uploadData.error || 'Failed to upload image');
            }
            
            // Set the uploaded filename
            document.getElementById('product-image').value = uploadData.data.filename;
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.csrf_token = CSRF_TOKEN;
        
        const method = editingProductId ? 'PUT' : 'POST';
        
        const response = await fetch('<?= APP_URL ?>/api/admin/products', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(editingProductId ? 'Product updated successfully' : 'Product created successfully', 'success');
            closeProductModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.error || 'Failed to save product');
        }
    } catch (error) {
        showToast(error.message || 'Network error. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save Product';
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
