<?php
/**
 * Admin Management Page
 * Super Admins can add and manage other administrators
 */

define('ADMIN_PAGE', true);
$page_title = 'Manage Administrators';

require_once __DIR__ . '/includes/admin_header.php';

// Only super admins can access this page
if (($admin_user['admin_type'] ?? 'admin') !== 'super_admin') {
    header('Location: ' . APP_URL . '/admin/index');
    exit;
}
?>

<div>
    <!-- Success/Error Messages -->
    <div id="adminAlert" class="hidden mb-6 p-4 rounded-lg"></div>
    
    <!-- Add Admin Button -->
    <div class="mb-6">
        <button id="addAdminBtn" class="btn-primary">
            <i class="fas fa-user-plus mr-2"></i>Add Administrator
        </button>
    </div>
    
    <!-- Administrators Table -->
    <div class="data-table">
        <div class="p-6 bg-gradient-to-r from-orange-500 to-red-500">
            <h3 class="text-xl font-bold text-white">
                <i class="fas fa-user-shield mr-2"></i>
                Administrators
            </h3>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adminsTableBody">
                <tr>
                    <td colspan="6" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                        <p class="text-gray-500 mt-2">Loading administrators...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Admin Modal -->
<div id="adminModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-800">Add Administrator</h3>
                <button onclick="closeAdminModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <form id="adminForm" class="p-6 space-y-4">
            <input type="hidden" id="adminId" name="admin_id">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name *</label>
                <input type="text" 
                       id="adminName" 
                       name="name" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                <input type="email" 
                       id="adminEmail" 
                       name="email" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent read-only:bg-gray-100 read-only:cursor-not-allowed"
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Admin Type *</label>
                <select id="adminType" 
                        name="admin_type" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        required>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            
            <div id="passwordFields">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Password <span id="passwordRequired">*</span>
                    </label>
                    <input type="password" 
                           id="adminPassword" 
                           name="password" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                           minlength="8">
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                <select id="adminStatus" 
                        name="status" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="submit" class="btn-primary flex-1">
                    <i class="fas fa-save mr-2"></i>
                    <span id="submitBtnText">Add Administrator</span>
                </button>
                <button type="button" onclick="closeAdminModal()" class="btn-secondary flex-1">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let editingAdminId = null;

$(document).ready(function() {
    loadAdmins();
    
    // Add Admin Button
    $('#addAdminBtn').on('click', function() {
        openAddModal();
    });
    
    // Submit Admin Form
    $('#adminForm').on('submit', function(e) {
        e.preventDefault();
        
        const adminId = $('#adminId').val();
        const isEdit = adminId !== '';
        
        const data = {
            name: $('#adminName').val().trim(),
            email: $('#adminEmail').val().trim(),
            admin_type: $('#adminType').val(),
            status: $('#adminStatus').val()
        };
        
        const password = $('#adminPassword').val();
        if (password) {
            data.password = password;
        } else if (!isEdit) {
            showAlert('Password is required for new administrators', 'error');
            return;
        }
        
        const url = isEdit ? '<?= APP_URL ?>/api/admin/admins/' + adminId : '<?= APP_URL ?>/api/admin/admins';
        const method = isEdit ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                showAlert(isEdit ? 'Administrator updated successfully!' : 'Administrator added successfully!', 'success');
                closeAdminModal();
                loadAdmins();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to save administrator';
                showAlert(error, 'error');
            }
        });
    });
});

function loadAdmins() {
    $.ajax({
        url: '<?= APP_URL ?>/api/admin/admins',
        method: 'GET',
        success: function(response) {
            renderAdmins(response.data || []);
        },
        error: function() {
            $('#adminsTableBody').html('<tr><td colspan="6" class="text-center text-red-500 py-8">Failed to load administrators</td></tr>');
        }
    });
}

function renderAdmins(admins) {
    const tbody = $('#adminsTableBody');
    
    if (admins.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center text-gray-500 py-8">No administrators found</td></tr>');
        return;
    }
    
    const currentAdminId = <?= $admin_user['id'] ?>;
    
    tbody.html(admins.map(admin => {
        const isCurrentUser = admin.id === currentAdminId;
        const isSuperAdmin = admin.admin_type === 'super_admin';
        const isActive = admin.status === 'active';
        
        return `
            <tr>
                <td>
                    <div class="font-semibold">${escapeHtml(admin.name)}</div>
                    ${isCurrentUser ? '<span class="text-xs text-orange-500">(You)</span>' : ''}
                </td>
                <td>${escapeHtml(admin.email)}</td>
                <td>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${isSuperAdmin ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}">
                        ${isSuperAdmin ? 'Super Admin' : 'Admin'}
                    </span>
                </td>
                <td>
                    <span class="px-2 py-1 rounded-full text-xs font-semibold ${isActive ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                        ${isActive ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>${new Date(admin.created_at).toLocaleDateString()}</td>
                <td>
                    <button onclick="editAdmin(${admin.id})" class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${!isCurrentUser ? `
                        <button onclick="toggleAdminStatus(${admin.id}, '${admin.status}')" 
                                class="text-${isActive ? 'red' : 'green'}-600 hover:text-${isActive ? 'red' : 'green'}-800" 
                                title="${isActive ? 'Deactivate' : 'Activate'}">
                            <i class="fas fa-${isActive ? 'ban' : 'check'}"></i>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
    }).join(''));
}

function openAddModal() {
    editingAdminId = null;
    $('#modalTitle').text('Add Administrator');
    $('#submitBtnText').text('Add Administrator');
    $('#adminId').val('');
    $('#adminName').val('');
    $('#adminEmail').val('').prop('readonly', false);
    $('#adminType').val('admin');
    $('#adminPassword').val('').attr('required', true);
    $('#passwordRequired').show();
    $('#adminStatus').val('active');
    $('#adminModal').removeClass('hidden').css('display', 'flex');
}

function editAdmin(adminId) {
    $.ajax({
        url: '<?= APP_URL ?>/api/admin/admins/' + adminId,
        method: 'GET',
        success: function(response) {
            const admin = response.data;
            editingAdminId = adminId;
            
            $('#modalTitle').text('Edit Administrator');
            $('#submitBtnText').text('Update Administrator');
            $('#adminId').val(admin.id);
            $('#adminName').val(admin.name);
            $('#adminEmail').val(admin.email).prop('readonly', true);
            $('#adminType').val(admin.admin_type);
            $('#adminPassword').val('').attr('required', false);
            $('#passwordRequired').text('(leave blank to keep current)');
            $('#adminStatus').val(admin.status);
            $('#adminModal').removeClass('hidden').css('display', 'flex');
        },
        error: function() {
            showAlert('Failed to load administrator details', 'error');
        }
    });
}

function toggleAdminStatus(adminId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (!confirm(`Are you sure you want to ${action} this administrator?`)) {
        return;
    }
    
    $.ajax({
        url: '<?= APP_URL ?>/api/admin/admins/' + adminId,
        method: 'PUT',
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify({ status: newStatus }),
        success: function() {
            showAlert(`Administrator ${action}d successfully!`, 'success');
            loadAdmins();
        },
        error: function() {
            showAlert(`Failed to ${action} administrator`, 'error');
        }
    });
}

function closeAdminModal() {
    $('#adminModal').addClass('hidden').css('display', 'none');
    $('#adminForm')[0].reset();
    editingAdminId = null;
}

function showAlert(message, type) {
    const alert = $('#adminAlert');
    alert.removeClass('hidden bg-green-100 text-green-700 bg-red-100 text-red-700');
    
    if (type === 'success') {
        alert.addClass('bg-green-100 text-green-700');
    } else {
        alert.addClass('bg-red-100 text-red-700');
    }
    
    alert.html('<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message);
    alert.removeClass('hidden');
    
    if (type === 'success') {
        setTimeout(() => alert.addClass('hidden'), 5000);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
