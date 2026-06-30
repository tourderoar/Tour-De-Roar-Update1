<?php
/**
 * File: api/events/index.php
 * Location: /tour_update/api/events/index.php
 *
 * GET /api/events
 * Returns all active events from the database.
 * No authentication required (public endpoint).
 */

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
            created_at
        FROM events
        WHERE status = 'active'
        ORDER BY event_date ASC
    ");
    
    $stmt->execute();
    $events = $stmt->fetchAll();
    
    // Format the events for frontend
    $formatted_events = array_map(function($event) {
        $display_date = $event['event_date'];
        if (!empty($event['event_date'])) {
            $date_obj = DateTime::createFromFormat('Y-m-d', $event['event_date']);
            if ($date_obj !== false) {
                $display_date = $date_obj->format('l, F j, Y');
            }
        }

        return [
            'id' => (int)$event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'event_date' => $event['event_date'],
            'event_date_display' => $display_date,
            'time_start' => $event['time_start'],
            'location' => $event['location'],
            'distances' => $event['distances'],
            'price' => (float)$event['price'],
            'image_path' => $event['image_path'],
            'created_at' => $event['created_at']
        ];
    }, $events);
    
    json_success($formatted_events);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Events API error: ' . $e->getMessage());
        json_error('Unable to fetch events', 500);
    }
}
