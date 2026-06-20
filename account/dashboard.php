<?php
/**
 * File: account/dashboard.php
 * Location: /tour_update/account/dashboard.php
 *
 * User dashboard - main landing page for logged-in users.
 * Shows profile information and transaction history placeholder.
 * Requires active user session.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Require user to be logged in
if (!is_logged_in()) {
    header('Location: ' . APP_URL . '/account/login?redirect=' . urlencode('/account/dashboard'));
    exit;
}

$user = get_logged_in_user();

// Check for activation success message
$activated = $_GET['activated'] ?? '';

$page_title = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .dashboard-container {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 3rem 0;
    }
    
    .welcome-banner {
        background: linear-gradient(135deg, #805AD5 0%, #3182CE 100%);
        color: white;
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(128, 90, 213, 0.2);
    }
    
    .success-alert {
        background: linear-gradient(135deg, #68D391 0%, #48bb78 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(104, 211, 145, 0.3);
    }
    
    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }
    
    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    
    .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
    }
    
    .profile-item {
        display: flex;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .profile-item:last-child {
        border-bottom: none;
    }
    
    .profile-label {
        font-weight: 600;
        color: #718096;
        min-width: 150px;
        text-transform: uppercase;
        font-size: 0.875rem;
        letter-spacing: 0.05em;
    }
    
    .profile-value {
        color: #2d3748;
        font-size: 1rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        text-align: center;
        border-top: 4px solid;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #718096;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e0;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        color: white;
        text-align: center;
    }
    
    .action-btn i {
        margin-right: 0.75rem;
        font-size: 1.25rem;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
</style>

<div class="dashboard-container">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <?php if ($activated === 'success'): ?>
        <!-- Activation Success Alert -->
        <div class="success-alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-3xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-xl mb-1">Account Activated!</h3>
                    <p>Your account has been successfully activated. Welcome to Tour de Roar!</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($activated === 'already'): ?>
        <!-- Already Active Alert -->
        <div class="success-alert">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-3xl mr-4"></i>
                <div>
                    <h3 class="font-bold text-xl mb-1">Welcome Back!</h3>
                    <p>Your account is already active.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
                    <p class="text-purple-100 text-lg">Manage your account and track your impact</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-user-circle text-7xl text-blue-200"></i>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="stat-card" style="border-color: #3182CE;">
                <div class="stat-value" style="color: #3182CE;" id="event-count">0</div>
                <div class="stat-label">Event Registrations</div>
            </div>
            <div class="stat-card" style="border-color: #68D391;">
                <div class="stat-value" style="color: #68D391;" id="donation-total">$0</div>
                <div class="stat-label">Total Donations</div>
            </div>
            <div class="stat-card" style="border-color: #FF6B1A;">
                <div class="stat-value" style="color: #FF6B1A;" id="order-count">0</div>
                <div class="stat-label">Store Orders</div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #FF6B1A, #E53E3E); color: white;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h2 class="card-title">Quick Actions</h2>
            </div>
            
            <div class="quick-actions">
                <a href="<?= APP_URL ?>/events" class="action-btn" style="background: linear-gradient(135deg, #3182CE, #805AD5);">
                    <i class="fas fa-calendar-check"></i>
                    Register for Event
                </a>
                <a href="<?= APP_URL ?>/donate" class="action-btn" style="background: linear-gradient(135deg, #68D391, #48bb78);">
                    <i class="fas fa-heart"></i>
                    Make a Donation
                </a>
                <a href="<?= APP_URL ?>/store" class="action-btn" style="background: linear-gradient(135deg, #FF6B1A, #E53E3E);">
                    <i class="fas fa-shopping-cart"></i>
                    Shop Merchandise
                </a>
                <a href="<?= APP_URL ?>/sponsorship" class="action-btn" style="background: linear-gradient(135deg, #805AD5, #E53E3E);">
                    <i class="fas fa-handshake"></i>
                    Become a Sponsor
                </a>
            </div>
        </div>
        
        <!-- Profile Information -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #3182CE, #805AD5); color: white;">
                    <i class="fas fa-user"></i>
                </div>
                <h2 class="card-title">Profile Information</h2>
            </div>
            
            <div>
                <div class="profile-item">
                    <div class="profile-label">Name</div>
                    <div class="profile-value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Email</div>
                    <div class="profile-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Account Status</div>
                    <div class="profile-value">
                        <span style="color: #68D391; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transaction History (Placeholder for Phase 7) -->
        <div class="dashboard-card">
            <div class="card-header">
                <div class="card-icon" style="background: linear-gradient(135deg, #68D391, #48bb78); color: white;">
                    <i class="fas fa-receipt"></i>
                </div>
                <h2 class="card-title">Transaction History</h2>
            </div>
            
            <div id="transaction-preview" class="empty-state">
                <i class="fas fa-receipt"></i>
                <p class="text-lg font-semibold mb-2">No transactions yet</p>
                <p class="mb-4">Your payment history will appear here</p>
                <a href="<?= APP_URL ?>/events" style="color: #3182CE; font-weight: 600;">
                    Register for an event to get started →
                </a>
            </div>
        </div>
        
    </div>
</div>

<script>
$(document).ready(function() {
    // Load user statistics
    $.ajax({
        url: APP_URL + '/api/user/transactions',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data && response.data.transactions) {
                const transactions = response.data.transactions;
                
                // Count events
                const events = transactions.filter(t => t.type === 'event' && t.payment_status === 'succeeded');
                $('#event-count').text(events.length);
                
                // Sum donations
                const donations = transactions.filter(t => t.type === 'donation' && t.payment_status === 'succeeded');
                const donationTotal = donations.reduce((sum, d) => sum + parseFloat(d.amount), 0);
                $('#donation-total').text('$' + donationTotal.toFixed(2));
                
                // Count orders
                const orders = transactions.filter(t => t.type === 'store' && t.payment_status === 'succeeded');
                $('#order-count').text(orders.length);
                
                // Show recent transactions
                if (transactions.length > 0) {
                    const recentTransactions = transactions.slice(0, 3);
                    let html = '<div style="padding: 1rem 0;">';
                    
                    recentTransactions.forEach(t => {
                        const date = new Date(t.created_at).toLocaleDateString('en-US', { 
                            month: 'short', 
                            day: 'numeric', 
                            year: 'numeric' 
                        });
                        const statusBadge = t.payment_status === 'succeeded' 
                            ? '<span style="color: #68D391;">✓ Completed</span>' 
                            : '<span style="color: #F6AD55;">⏳ ' + t.payment_status.charAt(0).toUpperCase() + t.payment_status.slice(1) + '</span>';
                        
                        html += `
                            <div style="padding: 1rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: #2d3748;">${t.title}</div>
                                    <div style="color: #718096; font-size: 0.875rem;">${date} • ${statusBadge}</div>
                                </div>
                                <div style="font-weight: 700; color: #2d3748;">$${parseFloat(t.amount).toFixed(2)}</div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    html += '<div style="text-align: center; padding: 1.5rem;"><a href="' + APP_URL + '/account/transactions" class="font-semibold" style="color: #3182CE;">View All Transactions →</a></div>';
                    
                    $('#transaction-preview').html(html);
                }
            }
        },
        error: function() {
            console.log('Failed to load transaction stats');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
