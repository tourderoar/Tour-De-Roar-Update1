<?php
/**
 * Admin Header with Dark Sidebar Navigation
 * Included in all admin pages
 */

if (!defined('ADMIN_PAGE')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Start session before checking authentication
session_init();

require_admin();

$admin_user = $_SESSION['admin'] ?? null;
if (!$admin_user) {
    header('Location: ' . APP_URL . '/admin/login');
    exit;
}
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= get_csrf_token() ?>">
    <title><?= htmlspecialchars($page_title ?? 'Admin Panel') ?> | Tour de Roar Admin</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            background: #f7fafc;
            margin: 0;
            padding: 0;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            background: linear-gradient(135deg, #FF6B1A, #E53E3E, #805AD5);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .sidebar-brand p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.75rem;
            margin: 0.25rem 0 0 0;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: #cbd5e0;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .sidebar-nav-item.active {
            background: rgba(255, 107, 26, 0.1);
            color: #FF6B1A;
            border-left-color: #FF6B1A;
        }
        
        .sidebar-nav-item i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1rem;
        }
        
        .sidebar-user {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-user-info {
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .sidebar-user-email {
            color: #a0aec0;
            font-size: 0.75rem;
        }
        
        /* Main Content Area */
        .admin-main {
            margin-left: 260px;
            min-height: 100vh;
        }
        
        .admin-topbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-topbar h2 {
            color: #2d3748;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .admin-content {
            padding: 2rem;
        }
        
        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-card-value {
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            margin: 0.5rem 0;
        }
        
        .stat-card-label {
            color: #718096;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        /* Data Tables */
        .data-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table thead {
            background: linear-gradient(135deg, #FF6B1A, #E53E3E);
        }
        
        .data-table th {
            padding: 1rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }
        
        .data-table tbody tr:hover {
            background: #f7fafc;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #FF6B1A, #E53E3E);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 26, 0.3);
        }
        
        .btn-secondary {
            background: #805AD5;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-secondary:hover {
            background: #6b46c1;
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .btn-icon {
            padding: 0.5rem 0.75rem;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #FF6B1A, #E53E3E);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: #FF6B1A;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Badge Styles */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background: #68D391;
            color: #1a4d2e;
        }
        
        .badge-warning {
            background: #F6E05E;
            color: #744210;
        }
        
        .badge-danger {
            background: #E53E3E;
            color: white;
        }
        
        .badge-info {
            background: #3182CE;
            color: white;
        }
    </style>
</head>
<body>

<!-- Admin Sidebar -->
<div class="admin-sidebar">
    <div class="sidebar-brand">
        <h1>Tour de Roar</h1>
        <p>Admin Portal</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/admin/index" class="sidebar-nav-item <?= $current_page === 'index' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        <a href="<?= APP_URL ?>/admin/events" class="sidebar-nav-item <?= $current_page === 'events' ? 'active' : '' ?>">
            <i class="fas fa-calendar"></i>
            Events
        </a>
        <a href="<?= APP_URL ?>/admin/products" class="sidebar-nav-item <?= $current_page === 'products' ? 'active' : '' ?>">
            <i class="fas fa-store"></i>
            Products
        </a>
        <a href="<?= APP_URL ?>/admin/sponsorships" class="sidebar-nav-item <?= $current_page === 'sponsorships' ? 'active' : '' ?>">
            <i class="fas fa-handshake"></i>
            Sponsorships
        </a>
        <a href="<?= APP_URL ?>/admin/donations" class="sidebar-nav-item <?= $current_page === 'donations' ? 'active' : '' ?>">
            <i class="fas fa-heart"></i>
            Donation Types
        </a>
        <a href="<?= APP_URL ?>/admin/gallery" class="sidebar-nav-item <?= $current_page === 'gallery' ? 'active' : '' ?>">
            <i class="fas fa-images"></i>
            Gallery
        </a>
        <a href="<?= APP_URL ?>/admin/orders" class="sidebar-nav-item <?= $current_page === 'orders' ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i>
            Orders
        </a>
        <a href="<?= APP_URL ?>/admin/payments" class="sidebar-nav-item <?= $current_page === 'payments' ? 'active' : '' ?>">
            <i class="fas fa-credit-card"></i>
            Payments
        </a>
        <a href="<?= APP_URL ?>/admin/users" class="sidebar-nav-item <?= $current_page === 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            Users
        </a>
        
        <?php if (($admin_user['admin_type'] ?? 'admin') === 'super_admin'): ?>
        <a href="<?= APP_URL ?>/admin/admins" class="sidebar-nav-item <?= $current_page === 'admins' ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i>
            Administrators
        </a>
        <?php endif; ?>
        
        <a href="<?= APP_URL ?>/admin/profile" class="sidebar-nav-item <?= $current_page === 'profile' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i>
            My Profile
        </a>
    </nav>
    
    <div class="sidebar-user">
        <div class="sidebar-user-info">
            <?= htmlspecialchars($admin_user['name']) ?>
        </div>
        <div class="sidebar-user-email">
            <?= htmlspecialchars($admin_user['email']) ?>
        </div>
        <a href="<?= APP_URL ?>/admin/logout" class="btn-secondary btn-sm" style="margin-top: 0.75rem; width: 100%; text-align: center;">
            <i class="fas fa-sign-out-alt mr-2"></i>Logout
        </a>
    </div>
</div>

<!-- Main Content Area -->
<div class="admin-main">
    <div class="admin-topbar">
        <h2><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h2>
        <a href="<?= APP_URL ?>" class="btn-secondary btn-sm">
            <i class="fas fa-globe mr-2"></i>View Site
        </a>
    </div>
    
    <div class="admin-content">
