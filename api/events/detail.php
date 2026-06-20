<?php
/**
 * File: api/events/detail.php
 * Location: /tour_update/api/events/detail.php
 *
 * GET /api/events/{id}
 * Returns a single event by ID.
 * No authentication required (public endpoint).
 */

$event_id = $segments[1] ?? null;

if (!$event_id || !is_numeric($event_id)) {
    json_error('Invalid event ID', 400);
}

$db = get_db();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            title,
            description,
            event_date,
            time_start,
            location,
            distances,
            price,
            image_path,
            status,
            created_at,
            updated_at
        FROM events
        WHERE id = :id AND status = 'active'
    ");
    
    $stmt->execute(['id' => $event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        json_error('Event not found', 404);
    }
    
    // Format the event for frontend
    $formatted_event = [
        'id' => (int)$event['id'],
        'title' => $event['title'],
        'description' => $event['description'],
        'event_date' => $event['event_date'],
        'time_start' => $event['time_start'],
        'location' => $event['location'],
        'distances' => $event['distances'],
        'price' => (float)$event['price'],
        'image_path' => $event['image_path'],
        'created_at' => $event['created_at'],
        'updated_at' => $event['updated_at']
    ];
    
    json_success($formatted_event);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Event detail API error: ' . $e->getMessage());
        json_error('Unable to fetch event', 500);
    }
}
