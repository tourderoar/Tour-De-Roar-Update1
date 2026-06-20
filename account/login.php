<?php
/**
 * File: account/login.php
 * Location: /tour_update/account/login.php
 *
 * User login page.
 * Handles login form submission via AJAX to /api/auth/login.
 * Shows appropriate messages for inactive accounts.
 * Supports redirect parameter to return user to previous page after login.
 */

$page_title = 'Sign In';
require_once __DIR__ . '/../includes/header.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ' . APP_URL . '/account/dashboard');
    exit;
}

// Get redirect parameter if present (default to dashboard with full URL)
$redirect = $_GET['redirect'] ?? (APP_URL . '/account/dashboard');

// If redirect is a relative path or page name, prepend APP_URL
if (strpos($redirect, 'http') !== 0) {
    // If it starts with /, use as-is, otherwise add /
    if ($redirect[0] === '/') {
        $redirect = APP_URL . $redirect;
    } else {
        $redirect = APP_URL . '/' . $redirect;
    }
}
?>

<style>
    /* Premium login form styling */
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
        max-width: 450px;
        width: 100%;
        overflow: hidden;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #3182CE 0%, #805AD5 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .auth-body {
        padding: 2rem;
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
        border-color: #3182CE;
        box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
    }
    
    .form-input.error {
        border-color: #E53E3E;
    }
    
    .error-alert {
        background: #fff5f5;
        border-left: 4px solid #E53E3E;
        color: #742a2a;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        display: none;
    }
    
    .error-alert.show {
        display: block;
    }
    
    .submit-btn {
        width: 100%;
        background: linear-gradient(135deg, #3182CE 0%, #805AD5 100%);
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
        box-shadow: 0 10px 20px rgba(49, 130, 206, 0.3);
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
        font-size: 0.875rem;
    }
    
    .auth-footer a {
        color: #3182CE;
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        color: #805AD5;
    }
    
    .forgot-password {
        text-align: right;
        margin-top: 0.5rem;
    }
    
    .forgot-password a {
        color: #718096;
        font-size: 0.875rem;
        text-decoration: none;
    }
    
    .forgot-password a:hover {
        color: #3182CE;
    }
    
    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        color: #718096;
        font-size: 0.875rem;
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .divider span {
        padding: 0 1rem;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-sign-in-alt text-5xl mb-3"></i>
            <h1 class="text-3xl font-bold">Welcome Back</h1>
            <p class="mt-2 text-blue-100">Sign in to your account</p>
        </div>
        
        <div class="auth-body">
            <!-- Error Alert -->
            <div id="error-alert" class="error-alert">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-xl mr-3 mt-0.5"></i>
                    <div>
                        <div id="error-message"></div>
                        <div id="resend-link" style="display: none; margin-top: 0.5rem;">
                            <a href="<?= APP_URL ?>/account/resend-activation" style="color: #3182CE; font-weight: 600;">
                                Click here to resend activation email →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Form -->
            <form id="login-form">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <div class="forgot-password">
                        <a href="<?= APP_URL ?>/account/forgot-password">Forgot password?</a>
                    </div>
                </div>
                
                <button type="submit" id="submit-btn" class="submit-btn">
                    Sign In
                </button>
            </form>
            
            <div class="divider">
                <span>Don't have an account?</span>
            </div>
            
            <div class="auth-footer">
                <a href="<?= APP_URL ?>/account/register" style="font-size: 1rem;">
                    Create Account →
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('#error-alert').removeClass('show');
        $('#error-message').text('');
        $('#resend-link').hide();
        $('.form-input').removeClass('error');
        
        // Get form data
        const formData = {
            email: $('#email').val().trim(),
            password: $('#password').val(),
            redirect: $('input[name="redirect"]').val()
        };
        
        // Basic validation
        if (!formData.email || !formData.password) {
            showError('Please enter both email and password');
            return;
        }
        
        // Disable submit button
        $('#submit-btn').prop('disabled', true).text('Signing In...');
        
        // Submit to API
        $.ajax({
            url: APP_URL + '/api/auth/login',
            method: 'POST',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Redirect to specified page or dashboard
                    const redirectUrl = response.redirect || formData.redirect || (APP_URL + '/account/dashboard');
                    window.location.href = redirectUrl;
                } else {
                    showError(response.error || 'Login failed');
                    $('#submit-btn').prop('disabled', false).text('Sign In');
                    
                    // Show resend activation link if account is inactive
                    if (response.error && response.error.toLowerCase().includes('activate')) {
                        $('#resend-link').show();
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = 'Login failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                    
                    // Show resend activation link if account is inactive
                    if (errorMsg.toLowerCase().includes('activate')) {
                        $('#resend-link').show();
                    }
                }
                showError(errorMsg);
                $('#submit-btn').prop('disabled', false).text('Sign In');
            }
        });
    });
    
    function showError(message) {
        $('#error-message').text(message);
        $('#error-alert').addClass('show');
        $('#email').addClass('error');
        $('#password').addClass('error');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
