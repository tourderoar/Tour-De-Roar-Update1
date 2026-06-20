<?php
/**
 * File: account/forgot-password.php
 * Location: /tour_update/account/forgot-password.php
 *
 * Forgot password page.
 * Allows users to request a password reset email.
 * Always shows the same message regardless of whether the email exists (anti-enumeration).
 */

$page_title = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ' . APP_URL . '/account/dashboard');
    exit;
}
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
        background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .auth-body {
        padding: 2rem;
    }
    
    .info-box {
        background: #ebf8ff;
        border-left: 4px solid #3182CE;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        color: #2c5282;
        font-size: 0.875rem;
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
        border-color: #805AD5;
        box-shadow: 0 0 0 3px rgba(128, 90, 213, 0.1);
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
    
    .error-message {
        background: #fff5f5;
        border-left: 4px solid #E53E3E;
        color: #742a2a;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: none;
    }
    
    .error-message.show {
        display: block;
    }
    
    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #805AD5 0%, #E53E3E 100%);
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
        box-shadow: 0 10px 20px rgba(128, 90, 213, 0.3);
    }
    
    .submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .auth-footer {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
        color: #718096;
    }
    
    .auth-footer a {
        color: #3182CE;
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        color: #805AD5;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-key text-5xl mb-3"></i>
            <h1 class="text-3xl font-bold">Forgot Password?</h1>
            <p class="mt-2 text-purple-100">No worries, we'll send you reset instructions</p>
        </div>
        
        <div class="auth-body">
            <div class="info-box">
                <i class="fas fa-info-circle mr-2"></i>
                Enter your email address and we'll send you a link to reset your password.
            </div>
            
            <!-- Success Message -->
            <div id="success-message" class="success-message">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-bold mb-2">Reset Link Sent!</h3>
                        <p id="success-text">If an account exists for this email, you'll receive password reset instructions shortly.</p>
                        <p class="mt-2 text-sm">The link will expire in 1 hour.</p>
                    </div>
                </div>
            </div>
            
            <!-- Error Message -->
            <div id="error-message" class="error-message">
                <i class="fas fa-exclamation-circle mr-3"></i>
                <span id="error-text"></span>
            </div>
            
            <!-- Forgot Password Form -->
            <form id="forgot-password-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required autofocus>
                </div>
                
                <button type="submit" id="submit-btn" class="submit-btn">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reset Link
                </button>
            </form>
            
            <div class="auth-footer">
                Remember your password? 
                <a href="<?= APP_URL ?>/account/login">Sign In</a>
                <br><br>
                Don't have an account? 
                <a href="<?= APP_URL ?>/account/register">Create Account</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#forgot-password-form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous messages
        $('#success-message').removeClass('show');
        $('#error-message').removeClass('show');
        
        const email = $('#email').val().trim();
        
        if (!email) {
            $('#error-text').text('Please enter your email address');
            $('#error-message').addClass('show');
            return;
        }
        
        // Disable submit button
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Sending...');
        
        // Submit to API
        $.ajax({
            url: APP_URL + '/api/auth/forgot-password',
            method: 'POST',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({ email: email }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#success-message').addClass('show');
                    $('#forgot-password-form').hide();
                } else {
                    $('#error-text').text(response.error || 'Failed to send reset link');
                    $('#error-message').addClass('show');
                    $('#submit-btn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Send Reset Link');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to send reset link. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                $('#error-text').text(errorMsg);
                $('#error-message').addClass('show');
                $('#submit-btn').prop('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i>Send Reset Link');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
