<?php
/**
 * Admin Gallery Management
 * View, upload, and delete gallery images
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Gallery';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all gallery images
$stmt = $db->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Gallery Management</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Manage event photos and images</p>
    </div>
    <button onclick="openImageModal()" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>Add New Image
    </button>
</div>

<!-- Gallery Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
    <?php if (empty($images)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: white; border-radius: 12px;">
            <i class="fas fa-images" style="font-size: 4rem; color: #a0aec0; margin-bottom: 1rem; display: block;"></i>
            <p style="color: #a0aec0; font-size: 1.125rem;">No images yet. Click "Add New Image" to upload one.</p>
        </div>
    <?php else: ?>
        <?php foreach ($images as $image): ?>
            <div class="gallery-item" data-image-id="<?= $image['id'] ?>">
                <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: transform 0.2s;">
                    <div style="position: relative; padding-top: 66.67%; background: #e2e8f0;">
                        <img src="<?= APP_URL ?>/images/events/<?= htmlspecialchars($image['filename']) ?>" 
                             alt="<?= htmlspecialchars($image['caption'] ?? 'Gallery image') ?>"
                             style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="padding: 1rem;">
                        <?php if ($image['caption']): ?>
                            <h4 style="margin: 0 0 0.5rem 0; font-weight: 600; color: #2d3748;">
                                <?= htmlspecialchars($image['caption']) ?>
                            </h4>
                        <?php endif; ?>
                        <div style="display: flex; gap: 0.5rem;">
                            <button onclick='editImage(<?= htmlspecialchars(json_encode($image), ENT_QUOTES) ?>)' class="btn-secondary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteImage(<?= $image['id'] ?>)" class="btn-secondary btn-sm" style="background: #E53E3E;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Image Modal -->
<div id="image-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add New Image</h3>
            <button type="button" onclick="closeImageModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="image-form">
                <input type="hidden" id="image-id" name="id">
                
                <div class="form-group">
                    <label class="form-label">Caption</label>
                    <input type="text" id="image-caption" name="caption" class="form-input" placeholder="Photo caption or title">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gallery Image *</label>
                    <input type="hidden" id="image-filename" name="filename">
                    <input type="file" id="gallery-image-upload" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="form-input" style="padding: 0.5rem;" required>
                    <p style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
                        Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP
                    </p>
                    <div id="gallery-image-preview-container" style="margin-top: 1rem; display: none;">
                        <img id="gallery-image-preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save mr-2"></i>Save Image
                    </button>
                    <button type="button" onclick="closeImageModal()" class="btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.gallery-item:hover > div {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
</style>

<script>
let editingImageId = null;

function openImageModal() {
    editingImageId = null;
    document.getElementById('modal-title').textContent = 'Add New Image';
    document.getElementById('image-form').reset();
    document.getElementById('image-id').value = '';
    document.getElementById('gallery-image-upload').value = '';
    document.getElementById('gallery-image-upload').required = true;
    document.getElementById('gallery-image-preview-container').style.display = 'none';
    showModal('image-modal');
}

function closeImageModal() {
    hideModal('image-modal');
    editingImageId = null;
    document.getElementById('gallery-image-upload').value = '';
    document.getElementById('gallery-image-preview-container').style.display = 'none';
}

function editImage(image) {
    editingImageId = image.id;
    document.getElementById('modal-title').textContent = 'Edit Image';
    document.getElementById('image-id').value = image.id;
    document.getElementById('image-caption').value = image.caption || '';
    document.getElementById('image-filename').value = image.filename;
    document.getElementById('gallery-image-upload').required = false; // Optional when editing
    
    // Show existing image preview if available
    if (image.filename) {
        const preview = document.getElementById('gallery-image-preview');
        const container = document.getElementById('gallery-image-preview-container');
        preview.src = '<?= APP_URL ?>/images/events/' + image.filename;
        container.style.display = 'block';
    }
    
    showModal('image-modal');
}

async function deleteImage(id) {
    if (!confirmDelete('Are you sure you want to delete this image?')) {
        return;
    }
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/gallery', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, csrf_token: CSRF_TOKEN })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Image deleted successfully', 'success');
            document.querySelector(`.gallery-item[data-image-id="${id}"]`).remove();
        } else {
            showToast(data.error || 'Failed to delete image', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
}

// Handle image preview
document.getElementById('gallery-image-upload').addEventListener('change', function(e) {
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
            const preview = document.getElementById('gallery-image-preview');
            const container = document.getElementById('gallery-image-preview-container');
            preview.src = event.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Handle form submission
document.getElementById('image-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        // Upload image first if selected
        const imageFile = document.getElementById('gallery-image-upload').files[0];
        if (imageFile) {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('type', 'events'); // Gallery images go to events folder
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
            document.getElementById('image-filename').value = uploadData.data.filename;
        } else if (!editingImageId) {
            throw new Error('Please select an image to upload');
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.csrf_token = CSRF_TOKEN;
        
        const method = editingImageId ? 'PUT' : 'POST';
        
        const response = await fetch('<?= APP_URL ?>/api/admin/gallery', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(editingImageId ? 'Image updated successfully' : 'Image added successfully', 'success');
            closeImageModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.error || 'Failed to save image');
        }
    } catch (error) {
        showToast(error.message || 'Network error. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save Image';
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
