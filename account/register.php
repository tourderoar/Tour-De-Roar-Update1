<?php
/**
 * File: account/register.php
 * Location: /tour_update/account/register.php
 *
 * User registration page.
 * Collects first name, last name, email, password, and phone.
 * On successful registration, shows activation message (user must check email).
 * No auto-login - account must be activated first via email link.
 */

$page_title = 'Create Account';
require_once __DIR__ . '/../includes/header.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ' . APP_URL . '/account/dashboard');
    exit;
}
?>

<style>
    /* Premium registration form styling */
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
        background: linear-gradient(135deg, #805AD5 0%, #3182CE 100%);
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
        border-color: #805AD5;
        box-shadow: 0 0 0 3px rgba(128, 90, 213, 0.1);
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
        background: linear-gradient(135deg, #FF6B1A 0%, #E53E3E 100%);
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
        box-shadow: 0 10px 20px rgba(255, 107, 26, 0.3);
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
        color: #805AD5;
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        color: #3182CE;
    }
    
    .password-requirements {
        font-size: 0.75rem;
        color: #718096;
        margin-top: 0.5rem;
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-plus text-5xl mb-3"></i>
            <h1 class="text-3xl font-bold">Create Your Account</h1>
            <p class="mt-2 text-purple-100">Join the Tour de Roar community</p>
        </div>
        
        <div class="auth-body">
            <!-- Success Message (shown after successful registration) -->
            <div id="success-message" class="success-message">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-2xl mr-3"></i>
                    <div>
                        <h3 class="font-bold text-lg mb-2">Account Created Successfully!</h3>
                        <p>We've sent an activation link to your email. Please check your inbox and click the link to activate your account.</p>
                        <p class="mt-2 text-sm">The link will expire in 24 hours.</p>
                    </div>
                </div>
            </div>
            
            <!-- Registration Form -->
            <form id="register-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-input" required>
                        <div class="error-message" id="first_name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-input" required>
                        <div class="error-message" id="last_name-error"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                    <div class="error-message" id="email-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number (Optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-input" placeholder="(123) 456-7890">
                    <div class="error-message" id="phone-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <div class="password-requirements">
                        Must be at least 8 characters with uppercase, lowercase, and number
                    </div>
                    <div class="error-message" id="password-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
                    <div class="error-message" id="password_confirm-error"></div>
                </div>
                
                <button type="submit" id="submit-btn" class="submit-btn">
                    Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? 
                <a href="<?= APP_URL ?>/account/login">Sign In</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.error-message').removeClass('show');
        $('.form-input').removeClass('error');
        $('#success-message').removeClass('show');
        
        // Get form data
        const formData = {
            first_name: $('#first_name').val().trim(),
            last_name: $('#last_name').val().trim(),
            email: $('#email').val().trim(),
            phone: $('#phone').val().trim(),
            password: $('#password').val(),
            password_confirm: $('#password_confirm').val()
        };
        
        // Client-side validation
        let hasError = false;
        
        if (!formData.first_name) {
            showError('first_name', 'First name is required');
            hasError = true;
        }
        
        if (!formData.last_name) {
            showError('last_name', 'Last name is required');
            hasError = true;
        }
        
        if (!formData.email || !isValidEmail(formData.email)) {
            showError('email', 'Please enter a valid email address');
            hasError = true;
        }
        
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
        $('#submit-btn').prop('disabled', true).text('Creating Account...');
        
        // Submit to API
        $.ajax({
            url: APP_URL + '/api/auth/register',
            method: 'POST',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#success-message').addClass('show');
                    // Hide form
                    $('#register-form').hide();
                    // Scroll to top
                    window.scrollTo(0, 0);
                } else {
                    showError('email', response.error || 'Registration failed');
                    $('#submit-btn').prop('disabled', false).text('Create Account');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Registration failed. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMsg = xhr.responseJSON.error;
                }
                showError('email', errorMsg);
                $('#submit-btn').prop('disabled', false).text('Create Account');
            }
        });
    });
    
    function showError(field, message) {
        $('#' + field).addClass('error');
        $('#' + field + '-error').text(message).addClass('show');
    }
    
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function isStrongPassword(password) {
        // Must contain at least one uppercase, one lowercase, and one number
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        return hasUpper && hasLower && hasNumber;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
