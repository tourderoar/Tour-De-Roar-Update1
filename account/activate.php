<?php
/**
 * File: account/activate.php
 * Location: /tour_update/account/activate.php
 *
 * Account activation page.
 * Processes the activation token from the email link.
 * Validates the token, activates the account, logs the user in, and redirects to dashboard.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid activation link';
} else {
    try {
        $db = get_db();
        
        // Look up the token and check expiry using MySQL NOW() to avoid timezone issues
        $stmt = $db->prepare("
            SELECT uat.user_id, uat.expires_at, u.email, u.first_name, u.last_name, u.status,
                   (uat.expires_at > NOW()) AS is_valid
            FROM user_activation_tokens uat
            INNER JOIN users u ON uat.user_id = u.id
            WHERE uat.token = ?
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$token_data) {
            $error = 'This activation link is invalid or has already been used.';
        } elseif ($token_data['is_valid'] != 1) {
            $error = 'This activation link has expired. Please request a new one.';
        } elseif ($token_data['status'] === 'active') {
            // Account is already active - just log them in
            $_SESSION['user'] = [
                'id' => $token_data['user_id'],
                'email' => $token_data['email'],
                'first_name' => $token_data['first_name'],
                'last_name' => $token_data['last_name']
            ];
            
            // Delete the used token
            $stmt = $db->prepare("DELETE FROM user_activation_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            header('Location: ' . APP_URL . '/account/dashboard?activated=already');
            exit;
        } else {
            // Activate the account
            $stmt = $db->prepare("UPDATE users SET status = 'active', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$token_data['user_id']]);
            
            // Delete the used token
            $stmt = $db->prepare("DELETE FROM user_activation_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            // Log the user in automatically
            $_SESSION['user'] = [
                'id' => $token_data['user_id'],
                'email' => $token_data['email'],
                'first_name' => $token_data['first_name'],
                'last_name' => $token_data['last_name']
            ];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: ' . APP_URL . '/account/dashboard?activated=success');
            exit;
        }
        
    } catch (PDOException $e) {
        error_log('Activation error: ' . $e->getMessage());
        $error = 'Activation failed. Please try again or contact support.';
    }
}

// If we got here, there was an error - show the error page
$page_title = 'Account Activation';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .activation-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .activation-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 100%;
        padding: 3rem;
        text-align: center;
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
    
    .error-message {
        color: #718096;
        font-size: 1.125rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }
    
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
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
    
    .btn-secondary {
        background: #e2e8f0;
        color: #2d3748;
    }
    
    .btn-secondary:hover {
        background: #cbd5e0;
    }
</style>

<div class="activation-container">
    <div class="activation-card">
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <h1 class="error-title">Activation Failed</h1>
        <p class="error-message"><?= htmlspecialchars($error) ?></p>
        
        <div class="action-buttons">
            <a href="<?= APP_URL ?>/account/resend-activation" class="btn btn-primary">
                <i class="fas fa-envelope mr-2"></i>
                Request New Activation Link
            </a>
            <a href="<?= APP_URL ?>/account/login" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Back to Login
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
