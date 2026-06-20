<?php
/**
 * Admin Events Management
 * View, create, edit, and delete events
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Events';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all events
$stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Events Management</h3>
        <p style="color: #718096; margin: 0.5rem 0 0 0;">Create and manage cycling events</p>
    </div>
    <button onclick="openEventModal()" class="btn-primary">
        <i class="fas fa-plus mr-2"></i>Add New Event
    </button>
</div>

<!-- Events Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Date</th>
                <th>Location</th>
                <th>Price</th>
                <th>Registrations</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="events-tbody">
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-calendar text-4xl mb-4" style="display: block;"></i>
                        No events yet. Click "Add New Event" to create one.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <tr data-event-id="<?= $event['id'] ?>">
                        <td>#<?= $event['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($event['title']) ?></strong>
                        </td>
                        <td><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <td>$<?= number_format($event['price'], 2) ?></td>
                        <td>
                            <?php
                            $stmt = $db->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ? AND payment_status = 'completed'");
                            $stmt->execute([$event['id']]);
                            $reg_count = $stmt->fetchColumn();
                            echo $reg_count;
                            ?>
                        </td>
                        <td>
                            <?php if ($event['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick='editEvent(<?= htmlspecialchars(json_encode($event), ENT_QUOTES) ?>)' class="btn-secondary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteEvent(<?= $event['id'] ?>)" class="btn-secondary btn-icon" title="Delete" style="background: #E53E3E; margin-left: 0.5rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Event Modal -->
<div id="event-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Add New Event</h3>
            <button type="button" onclick="closeEventModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="event-form">
                <input type="hidden" id="event-id" name="id">
                
                <div class="form-group">
                    <label class="form-label">Event Title *</label>
                    <input type="text" id="event-title" name="title" class="form-input" required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Event Date *</label>
                        <input type="date" id="event-date" name="event_date" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" id="event-price" name="price" class="form-input" step="0.01" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Location *</label>
                    <input type="text" id="event-location" name="location" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description *</label>
                    <textarea id="event-description" name="description" class="form-textarea" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Event Image</label>
                    <input type="hidden" id="event-image" name="image_path">
                    <input type="file" id="event-image-upload" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="form-input" style="padding: 0.5rem;">
                    <p style="color: #718096; font-size: 0.75rem; margin-top: 0.25rem;">
                        Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP
                    </p>
                    <div id="event-image-preview-container" style="margin-top: 1rem; display: none;">
                        <img id="event-image-preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e2e8f0;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="event-status" name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        <i class="fas fa-save mr-2"></i>Save Event
                    </button>
                    <button type="button" onclick="closeEventModal()" class="btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editingEventId = null;

function openEventModal() {
    editingEventId = null;
    document.getElementById('modal-title').textContent = 'Add New Event';
    document.getElementById('event-form').reset();
    document.getElementById('event-id').value = '';
    document.getElementById('event-image-upload').value = '';
    document.getElementById('event-image-preview-container').style.display = 'none';
    showModal('event-modal');
}

function closeEventModal() {
    hideModal('event-modal');
    editingEventId = null;
    document.getElementById('event-image-upload').value = '';
    document.getElementById('event-image-preview-container').style.display = 'none';
}

function editEvent(event) {
    editingEventId = event.id;
    document.getElementById('modal-title').textContent = 'Edit Event';
    document.getElementById('event-id').value = event.id;
    document.getElementById('event-title').value = event.title;
    document.getElementById('event-date').value = event.event_date;
    document.getElementById('event-location').value = event.location;
    document.getElementById('event-price').value = event.price;
    document.getElementById('event-description').value = event.description;
    document.getElementById('event-image').value = event.image_path || '';
    document.getElementById('event-status').value = event.status;
    
    // Show existing image preview if available
    if (event.image_path) {
        const preview = document.getElementById('event-image-preview');
        const container = document.getElementById('event-image-preview-container');
        preview.src = '<?= APP_URL ?>/images/events/' + event.image_path;
        container.style.display = 'block';
    }
    
    showModal('event-modal');
}

async function deleteEvent(id) {
    if (!confirmDelete('Are you sure you want to delete this event? All registrations will remain but the event will be removed.')) {
        return;
    }
    
    try {
        const response = await fetch('<?= APP_URL ?>/api/admin/events', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id, csrf_token: CSRF_TOKEN })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Event deleted successfully', 'success');
            // Remove row from table
            document.querySelector(`tr[data-event-id="${id}"]`).remove();
        } else {
            showToast(data.error || 'Failed to delete event', 'error');
        }
    } catch (error) {
        showToast('Network error. Please try again.', 'error');
    }
}

// Handle image preview
document.getElementById('event-image-upload').addEventListener('change', function(e) {
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
            const preview = document.getElementById('event-image-preview');
            const container = document.getElementById('event-image-preview-container');
            preview.src = event.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// Handle form submission
document.getElementById('event-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    
    try {
        // Upload image first if selected
        const imageFile = document.getElementById('event-image-upload').files[0];
        if (imageFile) {
            const uploadFormData = new FormData();
            uploadFormData.append('image', imageFile);
            uploadFormData.append('type', 'events');
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
            document.getElementById('event-image').value = uploadData.data.filename;
        }
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.csrf_token = CSRF_TOKEN;
        
        const method = editingEventId ? 'PUT' : 'POST';
        
        const response = await fetch('<?= APP_URL ?>/api/admin/events', {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(editingEventId ? 'Event updated successfully' : 'Event created successfully', 'success');
            closeEventModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(result.error || 'Failed to save event');
        }
    } catch (error) {
        showToast(error.message || 'Network error. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-save mr-2"></i>Save Event';
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
