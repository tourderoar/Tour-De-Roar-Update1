<?php
/**
 * Admin Events API
 * CRUD operations for events
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

// GET - List all events
if ($method === 'GET') {
    $stmt = $db->query("SELECT * FROM events ORDER BY event_date DESC");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json_success($events);
}

// POST - Create new event
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }

    // Validate required fields
    if (empty($data['title']) || empty($data['event_date']) || empty($data['location']) ||
        empty($data['description']) || !isset($data['price'])) {
        json_error('Missing required fields');
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO events (title, event_date, time_start, location, distances, description, price, image_path, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        $stmt->execute([
            $data['title'],
            $data['event_date'],
            $data['time_start'] ?? null,
            $data['location'],
            $data['distances'] ?? null,
            $data['description'],
            $data['price'],
            $data['image_path'] ?? null,
            $data['status'] ?? 'active'
        ]);

        json_success(['id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// PUT - Update existing event
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }

    // Validate required fields
    if (empty($data['id']) || empty($data['title']) || empty($data['event_date']) ||
        empty($data['location']) || empty($data['description']) || !isset($data['price'])) {
        json_error('Missing required fields');
    }

    try {
        // Get old image path if updating with new image
        if (!empty($data['image_path'])) {
            $stmt = $db->prepare("SELECT image_path FROM events WHERE id = ?");
            $stmt->execute([$data['id']]);
            $old_event = $stmt->fetch();

            // Delete old image if it's different from the new one
            if ($old_event && !empty($old_event['image_path']) &&
                $old_event['image_path'] !== $data['image_path']) {
                $old_image = __DIR__ . '/../../images/events/' . $old_event['image_path'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
        }

        $stmt = $db->prepare("
            UPDATE events
            SET title = ?, event_date = ?, time_start = ?, location = ?, distances = ?, description = ?,
                price = ?, image_path = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->execute([
            $data['title'],
            $data['event_date'],
            $data['time_start'] ?? null,
            $data['location'],
            $data['distances'] ?? null,
            $data['description'],
            $data['price'],
            $data['image_path'] ?? null,
            $data['status'] ?? 'active',
            $data['id']
        ]);

        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}

// DELETE - Remove event
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        json_error('Invalid security token', 403);
    }

    if (empty($data['id'])) {
        json_error('Event ID is required');
    }

    try {
        // Get image path before deleting
        $stmt = $db->prepare("SELECT image_path FROM events WHERE id = ?");
        $stmt->execute([$data['id']]);
        $event = $stmt->fetch();

        // Delete the database record
        $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$data['id']]);

        // Delete the image file if it exists
        if ($event && !empty($event['image_path'])) {
            $image_file = __DIR__ . '/../../images/events/' . $event['image_path'];
            if (file_exists($image_file)) {
                unlink($image_file);
            }
        }

        json_success(null);
    } catch (PDOException $e) {
        json_error('Database error: ' . $e->getMessage(), 500);
    }
}
