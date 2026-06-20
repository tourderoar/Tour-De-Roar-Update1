<?php
/**
 * Admin Donations API
 * CRUD operations for donation types
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/response.php';
require_once __DIR__ . '/../../includes/middleware.php';

// Start session and require admin authentication
session_init();
require_admin();

$db = get_db();
$method = $_SERVER['REQUEST_METHOD'];

// GET - List all donation types
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM donation_types ORDER BY is_recurring ASC, amount ASC");
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_success($donations);
}

// POST - Create new donation type
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['label']) || !isset($data['amount']) || !isset($data['is_recurring'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            INSERT INTO donation_types (label, amount, is_recurring, description, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['label'],
            $data['amount'],
            $data['is_recurring'],
            $data['description'] ?? null,
            $data['status'] ?? 'active'
        ]);
        
        json_success(['id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update existing donation type
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    // Validate required fields
    if (empty($data['id']) || empty($data['label']) || !isset($data['amount']) || !isset($data['is_recurring'])) {
        json_error('Missing required fields');
    }
    
    try {
        $stmt = $db->prepare("
            UPDATE donation_types 
            SET label = ?, amount = ?, is_recurring = ?, description = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['label'],
            $data['amount'],
            $data['is_recurring'],
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $data['id']
        ]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// DELETE - Remove donation type
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }
    
    if (empty($data['id'])) {
        json_error('Donation type ID is required');
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM donation_types WHERE id = ?");
        $stmt->execute([$data['id']]);
        
        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}
