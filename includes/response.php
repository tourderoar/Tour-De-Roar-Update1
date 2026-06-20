<?php
/**
 * File: includes/response.php
 * Location: /tour_update/includes/response.php
 *
 * Standardised JSON response helpers for every API endpoint.
 * Every endpoint uses these two functions so the frontend JavaScript
 * always receives a consistent, predictable response shape.
 *
 * Success shape:  { "success": true,  "data": <payload> }
 * Error shape:    { "success": false, "error": "<message>" }
 */

/**
 * Send a successful JSON response and stop execution.
 *
 * @param mixed $data    The data payload — array, object, or null
 * @param int   $status  HTTP status code (default 200 OK)
 */
function json_success($data = null, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    // json_encode converts the PHP array to a JSON string
    echo json_encode([
        'success' => true,
        'data'    => $data,
    ]);

    // Nothing should run after sending a response — exit immediately
    exit;
}

/**
 * Send an error JSON response and stop execution.
 *
 * @param string $message  A clear, human-readable error message
 * @param int    $status   HTTP status code — e.g. 400 Bad Request,
 *                         401 Unauthorized, 403 Forbidden, 404 Not Found, 500 Server Error
 */
function json_error(string $message, int $status = 400): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'error'   => $message,
    ]);

    exit;
}
