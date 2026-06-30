<?php
/**
 * File: api/index.php
 * Location: /tour_update/api/index.php
 *
 * API Request Router
 * Routes all /api/* requests to the appropriate handler based on method and path.
 * 
 * All responses use json_success() or json_error() from includes/response.php.
 * Sets Content-Type: application/json on every response.
 */

// Load core dependencies
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/middleware.php';

// Start session (needed for CSRF validation and user auth)
session_init();

// Set JSON content type for all API responses
header('Content-Type: application/json');

// Enable CORS for local development (remove or restrict in production)
if (APP_ENV === 'local') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Parse the request
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove base path and query string
$base_path = '/tour_update/api';
if (APP_ENV === 'production') {
    $base_path = '/api'; // Adjust for production if needed
}

// Strip base path
$path = str_replace($base_path, '', parse_url($request_uri, PHP_URL_PATH));
$path = trim($path, '/');

// Split path into segments
$segments = $path ? explode('/', $path) : [];

// Route dispatcher
try {
    // Public GET endpoints (no auth required)
    if ($request_method === 'GET') {
        switch ($segments[0] ?? '') {
            case 'admin':
                // GET /api/admin/* - admin endpoints (auth required)
                $admin_resource = $segments[1] ?? '';
                switch ($admin_resource) {
                    case 'events':
                    case 'products':
                    case 'sponsorships':
                    case 'donations':
                    case 'gallery':
                        require __DIR__ . '/admin/' . $admin_resource . '.php';
                        break;
                    case 'orders':
                        // GET /api/admin/orders/{id} - get order details
                        if (isset($segments[2]) && is_numeric($segments[2])) {
                            require __DIR__ . '/admin/order-details.php';
                        } else {
                            json_error('Admin endpoint not found', 404);
                        }
                        break;
                    case 'admins':
                        // GET /api/admin/admins or GET /api/admin/admins/{id}
                        require __DIR__ . '/admin/admins.php';
                        break;
                    default:
                        json_error('Admin endpoint not found', 404);
                }
                break;
                
            case 'events':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // GET /api/events/{id}
                    require __DIR__ . '/events/detail.php';
                } else {
                    // GET /api/events
                    require __DIR__ . '/events/index.php';
                }
                break;
                
            case 'sponsorships':
                // GET /api/sponsorships
                require __DIR__ . '/sponsorships/index.php';
                break;
                
            case 'donations':
                // GET /api/donations
                require __DIR__ . '/donations/index.php';
                break;
                
            case 'products':
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    // GET /api/products/{id}
                    require __DIR__ . '/products/detail.php';
                } else {
                    // GET /api/products
                    require __DIR__ . '/products/index.php';
                }
                break;
                
            case 'gallery':
                // GET /api/gallery
                require __DIR__ . '/gallery/index.php';
                break;
                
            case 'user':
                // GET /api/user/* - user-specific endpoints
                $user_action = $segments[1] ?? '';
                switch ($user_action) {
                    case 'transactions':
                        // GET /api/user/transactions
                        require __DIR__ . '/user/transactions.php';
                        break;
                    case 'invoice':
                        // GET /api/user/invoice
                        require __DIR__ . '/user/invoice.php';
                        break;
                    default:
                        json_error('User endpoint not found', 404);
                }
                break;
                
            default:
                json_error('Endpoint not found', 404);
        }
    }
    
    // POST endpoints
    elseif ($request_method === 'POST') {
        switch ($segments[0] ?? '') {
            case 'upload':
                // POST /api/upload - file upload endpoint (admin auth required)
                require __DIR__ . '/upload.php';
                break;
                
            case 'admin':
                // POST /api/admin/* - admin creation endpoints (auth required)
                $admin_resource = $segments[1] ?? '';
                switch ($admin_resource) {
                    case 'events':
                    case 'products':
                    case 'sponsorships':
                    case 'donations':
                    case 'gallery':
                        require __DIR__ . '/admin/' . $admin_resource . '.php';
                        break;
                    case 'admins':
                        // POST /api/admin/admins - create new admin
                        require __DIR__ . '/admin/admins.php';
                        break;
                    default:
                        json_error('Admin endpoint not found', 404);
                }
                break;
                
            case 'contact':
                // POST /api/contact
                require __DIR__ . '/contact/index.php';
                break;
                
            case 'auth':
                // POST /api/auth/* - authentication endpoints
                $auth_action = $segments[1] ?? '';
                switch ($auth_action) {
                    case 'register':
                        require __DIR__ . '/auth/register.php';
                        break;
                    case 'login':
                        require __DIR__ . '/auth/login.php';
                        break;
                    case 'logout':
                        require __DIR__ . '/auth/logout.php';
                        break;
                    case 'resend-activation':
                        require __DIR__ . '/auth/resend-activation.php';
                        break;
                    case 'forgot-password':
                        require __DIR__ . '/auth/forgot-password.php';
                        break;
                    case 'reset-password':
                        require __DIR__ . '/auth/reset-password.php';
                        break;
                    default:
                        json_error('Auth endpoint not found', 404);
                }
                break;
                
            case 'payments':
                // POST /api/payments/* - payment endpoints
                $payment_type = $segments[1] ?? '';
                switch ($payment_type) {
                    case 'event':
                        require __DIR__ . '/payments/event.php';
                        break;
                    case 'donation':
                        require __DIR__ . '/payments/donation.php';
                        break;
                    case 'sponsorship':
                        require __DIR__ . '/payments/sponsorship.php';
                        break;
                    case 'store':
                        require __DIR__ . '/payments/store.php';
                        break;
                    default:
                        json_error('Payment endpoint not found', 404);
                }
                break;
                
            case 'webhook':
                // POST /api/webhook/* - webhook endpoints
                $webhook_provider = $segments[1] ?? '';
                switch ($webhook_provider) {
                    case 'stripe':
                        // Stripe webhook - bypass normal routing
                        require __DIR__ . '/webhook/stripe.php';
                        break;
                    default:
                        json_error('Webhook endpoint not found', 404);
                }
                break;
                
            default:
                json_error('Endpoint not found', 404);
        }
    }
    
    // PUT endpoints
    elseif ($request_method === 'PUT') {
        switch ($segments[0] ?? '') {
            case 'admin':
                // PUT /api/admin/* - admin update endpoints (auth required)
                $admin_resource = $segments[1] ?? '';
                switch ($admin_resource) {
                    case 'events':
                    case 'products':
                    case 'sponsorships':
                    case 'donations':
                    case 'gallery':
                        require __DIR__ . '/admin/' . $admin_resource . '.php';
                        break;
                    case 'profile':
                    case 'password':
                        // PUT /api/admin/profile or PUT /api/admin/password
                        require __DIR__ . '/admin/profile.php';
                        break;
                    case 'admins':
                        // PUT /api/admin/admins/{id} - update admin
                        require __DIR__ . '/admin/admins.php';
                        break;
                    default:
                        json_error('Admin endpoint not found', 404);
                }
                break;
                
            case 'user':
                // PUT /api/user/* - user update endpoints
                $user_action = $segments[1] ?? '';
                switch ($user_action) {
                    case 'profile':
                        // PUT /api/user/profile
                        require __DIR__ . '/user/profile.php';
                        break;
                    case 'change-password':
                        // PUT /api/user/change-password
                        require __DIR__ . '/user/change-password.php';
                        break;
                    default:
                        json_error('User endpoint not found', 404);
                }
                break;
                
            default:
                json_error('Endpoint not found', 404);
        }
    }
    
    // DELETE endpoints
    elseif ($request_method === 'DELETE') {
        switch ($segments[0] ?? '') {
            case 'admin':
                // DELETE /api/admin/* - admin delete endpoints (auth required)
                $admin_resource = $segments[1] ?? '';
                switch ($admin_resource) {
                    case 'events':
                    case 'products':
                    case 'sponsorships':
                    case 'donations':
                    case 'gallery':
                        require __DIR__ . '/admin/' . $admin_resource . '.php';
                        break;
                    default:
                        json_error('Admin endpoint not found', 404);
                }
                break;
                
            default:
                json_error('Endpoint not found', 404);
        }
    }
    
    // Unsupported method
    else {
        json_error('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    // Log error in production, show details in local
    if (APP_ENV === 'production') {
        error_log('API Error: ' . $e->getMessage());
        json_error('Internal server error', 500);
    } else {
        json_error($e->getMessage(), 500);
    }
}
