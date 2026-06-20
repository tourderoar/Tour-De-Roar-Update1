<?php
/**
 * File: account/transactions.php
 * Location: /tour_update/account/transactions.php
 *
 * User transactions history page.
 * Shows all event registrations, donations, sponsorship payments, and store orders.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

session_init();

// Require user to be logged in
if (!is_logged_in()) {
    header('Location: ' . APP_URL . '/account/login?redirect=' . urlencode('/account/transactions'));
    exit;
}

$user = get_logged_in_user();
$page_title = 'My Transactions';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .transactions-container {
        min-height: calc(100vh - 200px);
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 3rem 0;
    }
    
    .page-header {
        background: linear-gradient(135deg, #3182CE 0%, #805AD5 100%);
        color: white;
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(49, 130, 206, 0.2);
    }
    
    .transactions-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .filter-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    
    .filter-tab {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
        background: white;
        color: #4a5568;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .filter-tab:hover {
        border-color: #3182CE;
        color: #3182CE;
    }
    
    .filter-tab.active {
        background: linear-gradient(135deg, #3182CE, #805AD5);
        color: white;
        border-color: transparent;
    }
    
    .transaction-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        transition: background 0.2s;
    }
    
    .transaction-row:hover {
        background: #f7fafc;
    }
    
    .transaction-row:last-child {
        border-bottom: none;
    }
    
    .transaction-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .transaction-details {
        flex-grow: 1;
    }
    
    .transaction-title {
        font-weight: 700;
        color: #2d3748;
        font-size: 1.125rem;
        margin-bottom: 0.25rem;
    }
    
    .transaction-meta {
        color: #718096;
        font-size: 0.875rem;
    }
    
    .transaction-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .transaction-status {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        flex-shrink: 0;
    }
    
    .status-succeeded {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .status-failed {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #718096;
    }
    
    .empty-icon {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1.5rem;
    }
    
    .loading-spinner {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .spinner {
        font-size: 3rem;
        color: #3182CE;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #718096;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div class="transactions-container">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold mb-2">
                        <i class="fas fa-receipt mr-3"></i>My Transactions
                    </h1>
                    <p class="text-blue-100 text-lg">View your complete payment history</p>
                </div>
                <a href="<?= APP_URL ?>/account/dashboard" class="px-6 py-3 bg-white text-blue-600 rounded-lg font-bold hover:bg-blue-50 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Dashboard
                </a>
            </div>
        </div>
        
        <!-- Summary Stats -->
        <div id="summary-stats" class="summary-stats" style="display: none;">
            <div class="stat-card">
                <div class="stat-value" id="total-transactions">0</div>
                <div class="stat-label">Total Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="total-spent">$0</div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="total-succeeded">0</div>
                <div class="stat-label">Completed Payments</div>
            </div>
        </div>
        
        <!-- Transactions Card -->
        <div class="transactions-card">
            
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All Transactions</button>
                <button class="filter-tab" data-filter="event">Events</button>
                <button class="filter-tab" data-filter="donation">Donations</button>
                <button class="filter-tab" data-filter="sponsorship">Sponsorships</button>
                <button class="filter-tab" data-filter="store">Store Orders</button>
            </div>
            
            <!-- Loading State -->
            <div id="loading-state" class="loading-spinner">
                <div class="spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <p class="mt-4 text-gray-600">Loading your transactions...</p>
            </div>
            
            <!-- Transactions List -->
            <div id="transactions-list" style="display: none;"></div>
            
            <!-- Empty State -->
            <div id="empty-state" class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No transactions yet</h3>
                <p class="text-lg mb-4">Your payment history will appear here</p>
                <div class="flex gap-3 justify-center flex-wrap">
                    <a href="<?= APP_URL ?>/events" class="px-6 py-3 rounded-lg font-bold text-white" style="background: linear-gradient(135deg, #3182CE, #805AD5);">
                        <i class="fas fa-calendar-check mr-2"></i>Register for Event
                    </a>
                    <a href="<?= APP_URL ?>/donate" class="px-6 py-3 rounded-lg font-bold text-white" style="background: linear-gradient(135deg, #68D391, #48bb78);">
                        <i class="fas fa-heart mr-2"></i>Make a Donation
                    </a>
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<script>
let allTransactions = [];
let currentFilter = 'all';

// Type configurations
const typeConfig = {
    event: {
        icon: 'fa-calendar-check',
        color: 'background: linear-gradient(135deg, #3182CE, #805AD5);',
        label: 'Event Registration'
    },
    donation: {
        icon: 'fa-heart',
        color: 'background: linear-gradient(135deg, #68D391, #48bb78);',
        label: 'Donation'
    },
    sponsorship: {
        icon: 'fa-handshake',
        color: 'background: linear-gradient(135deg, #805AD5, #E53E3E);',
        label: 'Sponsorship'
    },
    store: {
        icon: 'fa-shopping-bag',
        color: 'background: linear-gradient(135deg, #FF6B1A, #E53E3E);',
        label: 'Store Purchase'
    }
};

$(document).ready(function() {
    // Check URL parameter for filter
    const urlParams = new URLSearchParams(window.location.search);
    const filterParam = urlParams.get('filter');
    if (filterParam && ['event', 'donation', 'sponsorship', 'store'].includes(filterParam)) {
        currentFilter = filterParam;
        $('.filter-tab').removeClass('active');
        $('.filter-tab[data-filter="' + filterParam + '"]').addClass('active');
    }
    
    loadTransactions();
    
    // Filter tabs
    $('.filter-tab').on('click', function() {
        $('.filter-tab').removeClass('active');
        $(this).addClass('active');
        currentFilter = $(this).data('filter');
        renderTransactions();
    });
});

function loadTransactions() {
    $.ajax({
        url: APP_URL + '/api/user/transactions',
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                allTransactions = response.data.transactions || [];
                updateStats();
                renderTransactions();
            } else {
                showError();
            }
        },
        error: function() {
            showError();
        }
    });
}

function updateStats() {
    const total = allTransactions.length;
    const succeeded = allTransactions.filter(t => t.payment_status === 'succeeded').length;
    const totalAmount = allTransactions
        .filter(t => t.payment_status === 'succeeded')
        .reduce((sum, t) => sum + parseFloat(t.amount), 0);
    
    $('#total-transactions').text(total);
    $('#total-spent').text('$' + totalAmount.toFixed(2));
    $('#total-succeeded').text(succeeded);
    
    if (total > 0) {
        $('#summary-stats').show();
    }
}

function renderTransactions() {
    $('#loading-state').hide();
    
    // Filter transactions
    const filtered = currentFilter === 'all' 
        ? allTransactions 
        : allTransactions.filter(t => t.type === currentFilter);
    
    if (filtered.length === 0) {
        $('#transactions-list').hide();
        $('#empty-state').show();
        return;
    }
    
    $('#empty-state').hide();
    $('#transactions-list').show().html('');
    
    filtered.forEach(transaction => {
        const config = typeConfig[transaction.type];
        const statusClass = 'status-' + transaction.payment_status;
        const date = new Date(transaction.created_at);
        const formattedDate = date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const html = `
            <div class="transaction-row">
                <div class="flex items-center flex-grow">
                    <div class="transaction-icon" style="${config.color} color: white;">
                        <i class="fas ${config.icon}"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="transaction-title">${escapeHtml(transaction.title)}</div>
                        <div class="transaction-meta">
                            ${config.label} • ${formattedDate}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="transaction-amount">$${parseFloat(transaction.amount).toFixed(2)}</div>
                    <div class="transaction-status ${statusClass}">
                        ${transaction.payment_status.charAt(0).toUpperCase() + transaction.payment_status.slice(1)}
                    </div>
                    <a href="${APP_URL}/api/user/invoice?type=${transaction.type}&id=${transaction.id}" 
                       target="_blank"
                       class="px-4 py-2 rounded-lg font-semibold text-white" 
                       style="background: linear-gradient(135deg, #3182CE, #805AD5); text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-invoice"></i>
                        Invoice
                    </a>
                </div>
            </div>
        `;
        
        $('#transactions-list').append(html);
    });
}

function showError() {
    $('#loading-state').hide();
    $('#transactions-list').hide();
    $('#empty-state').html(`
        <div class="empty-icon">
            <i class="fas fa-exclamation-circle" style="color: #E53E3E;"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-2">Unable to load transactions</h3>
        <p class="text-lg mb-4">Please try again later</p>
        <button onclick="location.reload()" class="px-6 py-3 rounded-lg font-bold text-white" style="background: linear-gradient(135deg, #3182CE, #805AD5);">
            <i class="fas fa-redo mr-2"></i>Retry
        </button>
    `).show();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>