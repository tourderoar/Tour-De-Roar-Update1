<?php
/**
 * Admin Management API Endpoints (Super Admin Only)
 * GET /api/admin/admins - List all administrators
 * GET /api/admin/admins/{id} - Get single administrator
 * POST /api/admin/admins - Create new administrator
 * PUT /api/admin/admins/{id} - Update administrator
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/middleware.php';

session_init();
require_admin();

$admin = $_SESSION['admin'];

// Only super admins can manage other administrators
if (($admin['admin_type'] ?? 'admin') !== 'super_admin') {
    json_error('Access denied. Super admin privileges required.', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Extract admin ID from URL if present
$admin_id = null;
if (preg_match('/\/api\/admin\/admins\/(\d+)/', $request_uri, $matches)) {
    $admin_id = (int)$matches[1];
}

switch ($method) {
    case 'GET':
        if ($admin_id) {
            get_admin($admin_id);
        } else {
            list_admins();
        }
        break;
        
    case 'POST':
        create_admin();
        break;
        
    case 'PUT':
        if (!$admin_id) {
            json_error('Admin ID is required for update');
        }
        update_admin($admin_id);
        break;
        
    default:
        json_error('Method not allowed', 405);
}

/**
 * List all administrators
 */
function list_admins() {
    $db = get_db();
    
    $stmt = $db->query("
        SELECT id, name, email, admin_type, status, created_at, updated_at
        FROM admins
        ORDER BY created_at DESC
    ");
    
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    json_success($admins);
}

/**
 * Get single administrator details
 */
function get_admin($admin_id) {
    $db = get_db();
    
    $stmt = $db->prepare("
        SELECT id, name, email, admin_type, status, created_at, updated_at
        FROM admins
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        json_error('Administrator not found', 404);
    }
    
    json_success($admin);
}

/**
 * Create new administrator
 */
function create_admin() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $admin_type = $input['admin_type'] ?? 'admin';
    $status = $input['status'] ?? 'active';
    
    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        json_error('Name, email, and password are required');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Invalid email format');
    }
    
    if (strlen($password) < 8) {
        json_error('Password must be at least 8 characters');
    }
    
    if (!in_array($admin_type, ['admin', 'super_admin'])) {
        json_error('Invalid admin type');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        json_error('Invalid status');
    }
    
    $db = get_db();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_error('Email is already in use');
    }
    
    // Create admin
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO admins (name, email, password_hash, admin_type, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$name, $email, $password_hash, $admin_type, $status]);
    
    $new_id = $db->lastInsertId();
    
    json_success([
        'message' => 'Administrator created successfully',
        'data' => ['id' => $new_id]
    ], 201);
}

/**
 * Update administrator
 */
function update_admin($admin_id) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $db = get_db();
    
    // Check if admin exists
    $stmt = $db->prepare("SELECT id FROM admins WHERE id = ? LIMIT 1");
    $stmt->execute([$admin_id]);
    if (!$stmt->fetch()) {
        json_error('Administrator not found', 404);
    }
    
    $updates = [];
    $params = [];
    
    // Name
    if (isset($input['name'])) {
        $name = trim($input['name']);
        if (empty($name)) {
            json_error('Name cannot be empty');
        }
        $updates[] = "name = ?";
        $params[] = $name;
    }
    
    // Email
    if (isset($input['email'])) {
        $email = trim($input['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error('Invalid email format');
        }
        
        // Check if email is already in use by another admin
        $stmt = $db->prepare("SELECT id FROM admins WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$email, $admin_id]);
        if ($stmt->fetch()) {
            json_error('Email is already in use by another administrator');
        }
        
        $updates[] = "email = ?";
        $params[] = $email;
    }
    
    // Password
    if (isset($input['password']) && !empty($input['password'])) {
        $password = $input['password'];
        if (strlen($password) < 8) {
            json_error('Password must be at least 8 characters');
        }
        $updates[] = "password_hash = ?";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }
    
    // Admin Type
    if (isset($input['admin_type'])) {
        $admin_type = $input['admin_type'];
        if (!in_array($admin_type, ['admin', 'super_admin'])) {
            json_error('Invalid admin type');
        }
        $updates[] = "admin_type = ?";
        $params[] = $admin_type;
    }
    
    // Status
    if (isset($input['status'])) {
        $status = $input['status'];
        if (!in_array($status, ['active', 'inactive'])) {
            json_error('Invalid status');
        }
        $updates[] = "status = ?";
        $params[] = $status;
    }
    
    if (empty($updates)) {
        json_error('No fields to update');
    }
    
    $updates[] = "updated_at = NOW()";
    $params[] = $admin_id;
    
    $sql = "UPDATE admins SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    json_success(['message' => 'Administrator updated successfully']);
}
