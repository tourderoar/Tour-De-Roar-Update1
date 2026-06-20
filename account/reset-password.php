<?php
/**
 * File: account/reset-password.php
 * Location: /tour_update/account/reset-password.php
 *
 * Password reset page.
 * Allows users to set a new password using the reset token from email.
 * Validates token, checks expiry, and updates password.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Get token from URL
$token = $_GET['token'] ?? '';

$token_valid = false;
$token_error = '';

if (empty($token)) {
    $token_error = 'Invalid reset link';
} else {
    try {
        $db = get_db();
        
        // DEBUG: Log the token being searched
        error_log("DEBUG: Searching for token: " . $token);
        
        // Look up the token and check expiry using MySQL NOW() to avoid timezone issues
        $stmt = $db->prepare("
            SELECT prt.user_id, prt.expires_at, prt.used_at,
                   NOW() AS now_time,
                   (prt.expires_at > NOW()) AS is_valid
            FROM password_reset_tokens prt
            WHERE prt.token = ?
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // DEBUG: Log the result
        error_log("DEBUG: Token data found: " . ($token_data ? 'YES' : 'NO'));
        if ($token_data) {
            error_log("DEBUG: Token details - expires_at: {$token_data['expires_at']}, used_at: {$token_data['used_at']}, is_valid: {$token_data['is_valid']}, now_time: {$token_data['now_time']}");
        }
        
        if (!$token_data) {
            $token_error = 'This reset link is invalid or has expired.';
        } elseif ($token_data['used_at'] !== null) {
            $token_error = 'This reset link has already been used.';
        } elseif ($token_data['is_valid'] != 1) {
            $token_error = 'This reset link has expired. Please request a new one.';
        } else {
            $token_valid = true;
        }
        
    } catch (PDOException $e) {
        error_log('Reset password validation error: ' . $e->getMessage());
        error_log('DEBUG: Full exception: ' . $e->getTraceAsString());
        // Show actual error in development
        if (APP_ENV === 'local') {
            $token_error = 'Database error: ' . $e->getMessage();
        } else {
            $token_error = 'Unable to validate reset link. Please try again.';
        }
    } catch (Exception $e) {
        error_log('Reset password general error: ' . $e->getMessage());
        error_log('DEBUG: Full exception: ' . $e->getTraceAsString());
        // Show actual error in development
        if (APP_ENV === 'local') {
            $token_error = 'Error: ' . $e->getMessage();
        } else {
            $token_error = 'Unable to validate reset link. Please try again.';
        }
    }
}

$page_title = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 0;
    }
    
    .auth-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 500px;
        width: 100%;
        overflow: hidden;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #E53E3E 0%, #805AD5 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .auth-body {
        padding: 2rem;
    }
    
    .error-icon {
        color: #E53E3E;
        font-size: 4rem;
        margin-bottom: 1.5rem;
    }
    
    .error-title {
        color: #2d3748;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .error-message-box {
        color: #718096;
        font-size: 1.125rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        display: block;
        font-weight: 600;
        color: #2d3748;
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
        transition: all 0.3s ease;
    }
    
    .form-input:focus {
        outline: none;
        border-color: #E53E3E;
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
    }
    
    .form-input.error {
        border-color: #E53E3E;
    }
    
    .error-message {
        color: #E53E3E;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: none;
    }
    
    .error-message.show {
        display: block;
    }
    
    .success-message {
        background: linear-gradient(135deg, #68D391 0%, #48bb78 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: none;
    }
    
    .success-message.show {
        display: block;
    }
    
    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #E53E3E 0%, #805AD5 100%);
        color: white;
        font-weight: 700;
        padding: 1rem;
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
        box-shadow: 0 10px 20px rgba(229, 62, 62, 0.3);
    }
    
    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .password-requirements {
        font-size: 0.75rem;
        color: #718096;
        margin-top: 0.5rem;
    }
    
    .btn {
        padding: 0.875rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #805AD5 0%, #3182CE 100%);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(128, 90, 213, 0.3);
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-lock text-5xl mb-3"></i>
            <h1 class="text-3xl font-bold">Reset Password</h1>
            <p class="mt-2 text-red-100">Enter your new password</p>
        </div>
        
        <div class="auth-body">
            <?php if (!$token_valid): ?>
                <!-- Token Error -->
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle error-icon"></i>
                    <h2 class="error-title">Invalid Reset Link</h2>
                    <p class="error-message-box"><?= htmlspecialchars($token_error) ?></p>
                    <a href="<?= APP_URL ?>/account/forgot-password" class="btn btn-primary">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Request New Reset Link
                    </a>
                </div>
            <?php else: ?>
                <!-- Success Message (shown after password reset) -->
                <div id="success-message" class="success-message">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-bold text-lg mb-2">Password Reset Successful!</h3>
                            <p>Your password has been updated. You can now sign in with your new password.</p>
                            <a href="<?= APP_URL ?>/account/login" class="mt-3 inline-block" style="color: white; font-weight: 700; text-decoration: underline;">
                                Go to Login →
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Reset Password Form -->
                <form id="reset-password-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="form-group">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" id="password" name="password" class="form-input" required autofocus>
                        <div class="password-requirements">
                            Must be at least 8 characters with uppercase, lowercase, and number
                        </div>
                        <div class="error-message" id="password-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
                        <div class="error-message" id="password_confirm-error"></div>
                    </div>
                    
                    <button type="submit" id="submit-btn" class="submit-btn">
                        <i class="fas fa-check mr-2"></i>
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#reset-password-form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.error-message').removeClass('show');
        $('.form-input').removeClass('error');
        $('#success-message').removeClass('show');
        
        // Get form data
        const formData = {
            token: $('input[name="token"]').val(),
            password: $('#password').val(),
            password_confirm: $('#password_confirm').val()
        };
        
        // Client-side validation
        let hasError = false;
        
        if (!formData.password || formData.password.length < 8) {
            showError('password', 'Password must be at least 8 characters');
            hasError = true;
        } else if (!isStrongPassword(formData.password)) {
            showError('password', 'Password must contain uppercase, lowercase, and number');
            hasError = true;
        }
        
        if (formData.password !== formData.password_confirm) {
            showError('password_confirm', 'Passwords do not match');
            hasError = true;
        }
        
        if (hasError) {
            return;
        }
        
        // Disable submit button
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Resetting...');
        
        // Submit to API
        $.ajax({
            url: APP_URL + '/api/auth/reset-password',
            method: 'POST',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#success-message').addClass('show');
                    $('#reset-password-form').hide();
                    window.scrollTo(0, 0);
                } else {
                    showError('password', response.error || 'Password reset failed');
                    $('#submit-btn').prop('disabled', false).html('<i class="fas fa-check mr-2"></i>Reset Password');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Password reset failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showError('password', errorMsg);
                $('#submit-btn').prop('disabled', false).html('<i class="fas fa-check mr-2"></i>Reset Password');
            }
        });
    });
    
    function showError(field, message) {
        $('#' + field).addClass('error');
        $('#' + field + '-error').text(message).addClass('show');
    }
    
    function isStrongPassword(password) {
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        return hasUpper && hasLower && hasNumber;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
