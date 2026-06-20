<?php
/**
 * File: api/donations/index.php
 * Location: /tour_update/api/donations/index.php
 *
 * GET /api/donations
 * Returns all active donation types from the database.
 * No authentication required (public endpoint).
 */

$db = get_db();

try {
    $stmt = $db->prepare("
        SELECT 
            id,
            label,
            amount,
            is_recurring,
            description,
            created_at
        FROM donation_types
        WHERE status = 'active'
        ORDER BY is_recurring ASC, amount ASC
    ");
    
    $stmt->execute();
    $donations = $stmt->fetchAll();
    
    // Format the donation types for frontend
    $formatted_donations = array_map(function($donation) {
        return [
            'id' => (int)$donation['id'],
            'label' => $donation['label'],
            'amount' => (float)$donation['amount'],
            'is_recurring' => (int)$donation['is_recurring'],
            'description' => $donation['description'],
            'created_at' => $donation['created_at']
        ];
    }, $donations);
    
    json_success($formatted_donations);
    
} catch (PDOException $e) {
    if (APP_ENV === 'local') {
        json_error('Database error: ' . $e->getMessage(), 500);
    } else {
        error_log('Donations API error: ' . $e->getMessage());
        json_error('Unable to fetch donation types', 500);
    }
}
