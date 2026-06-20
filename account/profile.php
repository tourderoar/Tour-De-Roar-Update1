<?php
/**
 * File: account/profile.php
 * Location: /tour_update/account/profile.php
 *
 * User profile management page.
 * Allows users to update their personal information.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

session_init();

// Require user to be logged in
if (!is_logged_in()) {
    header('Location: ' . APP_URL . '/account/login?redirect=' . urlencode('/account/profile'));
    exit;
}

$user_session = get_logged_in_user();

// Fetch fresh user data from database
try {
    $db = get_db();
    $stmt = $db->prepare("SELECT id, first_name, last_name, email, phone, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_session['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // User doesn't exist anymore - logout
        header('Location: ' . APP_URL . '/account/logout');
        exit;
    }
} catch (PDOException $e) {
    error_log('Profile fetch error: ' . $e->getMessage());
    die('Unable to load profile. Please try again later.');
}

$page_title = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .profile-container {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 3rem 0;
    }
    
    .page-header {
        background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
        color: white;
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(128, 90, 213, 0.2);
    }
    
    .profile-card {
        background: white;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    
    .card-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .card-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.25rem;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.2s;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #805AD5;
        box-shadow: 0 0 0 3px rgba(128, 90, 213, 0.1);
    }
    
    .form-input:disabled {
        background: #f7fafc;
        cursor: not-allowed;
    }
    
    .success-alert, .error-alert {
        padding: 1rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: none;
    }
    
    .success-alert {
        background: #f0fdf4;
        border-left: 4px solid #68D391;
        color: #065f46;
    }
    
    .error-alert {
        background: #fef2f2;
        border-left: 4px solid #E53E3E;
        color: #991b1b;
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
        color: white;
        font-weight: 700;
        padding: 0.875rem 2rem;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(128, 90, 213, 0.3);
    }
    
    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .secondary-btn {
        background: white;
        color: #805AD5;
        font-weight: 600;
        padding: 0.875rem 2rem;
        border: 2px solid #805AD5;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .secondary-btn:hover {
        background: #805AD5;
        color: white;
        transform: translateY(-2px);
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f7fafc;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #4a5568;
        margin-bottom: 1rem;
    }
    
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-overlay.active {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #718096;
        cursor: pointer;
        padding: 0.5rem;
        line-height: 1;
        transition: color 0.2s;
    }
    
    .modal-close:hover {
        color: #E53E3E;
    }
</style>

<div class="profile-container">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="text-4xl font-bold mb-2">
                <i class="fas fa-user-cog mr-3"></i>My Profile
            </h1>
            <p class="text-purple-100 text-lg">Manage your personal information</p>
        </div>
        
        <!-- Profile Form -->
        <div class="profile-card">
            
            <!-- Success/Error Alerts -->
            <div id="success-alert" class="success-alert">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-xl mr-3"></i>
                    <div id="success-message"></div>
                </div>
            </div>
            
            <div id="error-alert" class="error-alert">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-xl mr-3"></i>
                    <div id="error-message"></div>
                </div>
            </div>
            
            <!-- Personal Information Section -->
            <div class="card-section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #805AD5, #E53E3E); color: white;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2 class="section-title">Personal Information</h2>
                </div>
                
                <form id="profile-form">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-input" 
                                   value="<?= htmlspecialchars($user['first_name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input" 
                                   value="<?= htmlspecialchars($user['last_name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" 
                               value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <div class="info-badge mt-2">
                            <i class="fas fa-info-circle" style="color: #3182CE;"></i>
                            Email cannot be changed for security reasons
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number (Optional)</label>
                        <input type="tel" name="phone" class="form-input" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                               placeholder="e.g. (070) 3438-8257">
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" class="submit-btn" id="save-btn">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                        <a href="<?= APP_URL ?>/account/dashboard" class="secondary-btn">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Security Section -->
            <div class="card-section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #E53E3E, #F6E05E); color: white;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h2 class="section-title">Security</h2>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600 mb-3">Protect your account by using a strong password</p>
                    <button type="button" id="change-password-btn" class="submit-btn" style="display: inline-block;">
                        <i class="fas fa-key mr-2"></i>Change Password
                    </button>
                </div>
            </div>
            
            <!-- Account Information Section -->
            <div class="card-section">
                <div class="section-header">
                    <div class="section-icon" style="background: linear-gradient(135deg, #3182CE, #68D391); color: white;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h2 class="section-title">Account Information</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="form-label">Account Status</div>
                        <div class="flex items-center gap-2 mt-2">
                            <span style="color: #68D391; font-weight: 600; font-size: 1.125rem;">
                                <i class="fas fa-check-circle mr-2"></i>Active
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="form-label">Member Since</div>
                        <div class="mt-2" style="color: #2d3748; font-size: 1rem;">
                            <?= date('F j, Y', strtotime($user['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<!-- Change Password Modal -->
<div id="password-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title"><i class="fas fa-key mr-2"></i>Change Password</h2>
            <button type="button" class="modal-close" onclick="closePasswordModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="password-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            
            <div class="success-alert" id="password-success">
                <i class="fas fa-check-circle mr-2"></i>
                <span id="password-success-message"></span>
            </div>
            
            <div class="error-alert" id="password-error">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span id="password-error-message"></span>
            </div>
            
            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" class="form-input" required>
                <small style="color: #718096; display: block; margin-top: 0.25rem;">Minimum 8 characters with uppercase, lowercase, and number</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" id="password-submit-btn" class="submit-btn" style="flex: 1;">
                    <i class="fas fa-save mr-2"></i>Update Password
                </button>
                <button type="button" onclick="closePasswordModal()" class="submit-btn" style="flex: 1; background: #718096;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Change Password Modal Functions
function openPasswordModal() {
    $('#password-modal').addClass('active');
    $('#password-form')[0].reset();
    $('#password-success').hide();
    $('#password-error').hide();
}

function closePasswordModal() {
    $('#password-modal').removeClass('active');
    $('#password-form')[0].reset();
    $('#password-success').hide();
    $('#password-error').hide();
}

// Close modal on overlay click
$('#password-modal').on('click', function(e) {
    if (e.target.id === 'password-modal') {
        closePasswordModal();
    }
});

$(document).ready(function() {
    // Open password modal
    $('#change-password-btn').on('click', function() {
        openPasswordModal();
    });
    
    // Handle password change form submission
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        
        $('#password-success').hide();
        $('#password-error').hide();
        
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        // Validate passwords match
        if (newPassword !== confirmPassword) {
            $('#password-error-message').text('New passwords do not match');
            $('#password-error').show();
            return;
        }
        
        // Validate password strength
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        if (!passwordRegex.test(newPassword)) {
            $('#password-error-message').text('Password must be at least 8 characters with uppercase, lowercase, and number');
            $('#password-error').show();
            return;
        }
        
        $('#password-submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');
        
        $.ajax({
            url: APP_URL + '/api/user/change-password',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                csrf_token: $('input[name="csrf_token"]').val(),
                current_password: $('#current_password').val(),
                new_password: newPassword
            }),
            success: function(response) {
                if (response.success) {
                    $('#password-success-message').text('Password updated successfully!');
                    $('#password-success').show();
                    $('#password-form')[0].reset();
                    
                    // Close modal after 2 seconds
                    setTimeout(function() {
                        closePasswordModal();
                    }, 2000);
                } else {
                    $('#password-error-message').text(response.error || 'Failed to update password');
                    $('#password-error').show();
                }
                $('#password-submit-btn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Update Password');
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'Failed to update password. Please try again.';
                $('#password-error-message').text(errorMsg);
                $('#password-error').show();
                $('#password-submit-btn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Update Password');
            }
        });
    });
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        // Hide previous alerts
        $('#success-alert, #error-alert').hide();
        
        // Disable submit button
        const $btn = $('#save-btn');
        const originalText = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
        
        // Gather form data
        const formData = {
            first_name: $('input[name="first_name"]').val().trim(),
            last_name: $('input[name="last_name"]').val().trim(),
            phone: $('input[name="phone"]').val().trim()
        };
        
        // Send AJAX request
        $.ajax({
            url: APP_URL + '/api/user/profile',
            method: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    $('#success-message').text(response.message || 'Profile updated successfully!');
                    $('#success-alert').fadeIn();
                    
                    // Update session data in header by reloading page after 1.5s
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    $('#error-message').text(response.error || 'Failed to update profile');
                    $('#error-alert').fadeIn();
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                
                let errorMsg = 'Failed to update profile. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                
                $('#error-message').text(errorMsg);
                $('#error-alert').fadeIn();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>