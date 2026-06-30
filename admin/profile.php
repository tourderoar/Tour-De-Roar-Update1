<?php
/**
 * Admin Profile Page
 * Allows admins to view and update their profile and password
 */

define('ADMIN_PAGE', true);
$page_title = 'My Profile';

require_once __DIR__ . '/includes/admin_header.php';

$admin = $_SESSION['admin'];
?>

<div class="max-w-4xl">
    <!-- Success/Error Messages -->
    <div id="profileAlert" class="hidden mb-6 p-4 rounded-lg"></div>
    
    <!-- Profile Information Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-user-circle text-orange-500 mr-2"></i>
                Profile Information
            </h3>
            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $admin['admin_type'] === 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                <?= $admin['admin_type'] === 'super_admin' ? 'Super Admin' : 'Admin' ?>
            </span>
        </div>
        
        <form id="profileForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="<?= htmlspecialchars($admin['name']) ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($admin['email']) ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       readonly>
            </div>
            
            <div class="pt-4">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Update Profile
                </button>
            </div>
        </form>
    </div>
    
    <!-- Change Password Card -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6">
            <i class="fas fa-lock text-orange-500 mr-2"></i>
            Change Password
        </h3>
        
        <form id="passwordForm" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password</label>
                <input type="password" 
                       id="current_password" 
                       name="current_password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       required>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                <input type="password" 
                       id="new_password" 
                       name="new_password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       required
                       minlength="8">
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                       required>
            </div>
            
            <div class="pt-4">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-key mr-2"></i>Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // Update Profile
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            name: $('#name').val().trim(),
            email: $('#email').val().trim()
        };
        
        $.ajax({
            url: '<?= APP_URL ?>/api/admin/profile',
            method: 'PUT',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                showAlert('Profile updated successfully!', 'success');
                // Update session data in sidebar
                $('.sidebar-user-info').text(data.name);
                $('.sidebar-user-email').text(data.email);
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to update profile';
                showAlert(error, 'error');
            }
        });
    });
    
    // Change Password
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (newPassword !== confirmPassword) {
            showAlert('New passwords do not match', 'error');
            return;
        }
        
        const data = {
            current_password: $('#current_password').val(),
            new_password: newPassword
        };
        
        $.ajax({
            url: '<?= APP_URL ?>/api/admin/password',
            method: 'PUT',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                showAlert('Password changed successfully!', 'success');
                $('#passwordForm')[0].reset();
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to change password';
                showAlert(error, 'error');
            }
        });
    });
    
    function showAlert(message, type) {
        const alert = $('#profileAlert');
        alert.removeClass('hidden bg-green-100 text-green-700 bg-red-100 text-red-700');
        
        if (type === 'success') {
            alert.addClass('bg-green-100 text-green-700');
        } else {
            alert.addClass('bg-red-100 text-red-700');
        }
        
        alert.html('<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message);
        alert.removeClass('hidden');
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => alert.addClass('hidden'), 5000);
        }
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
