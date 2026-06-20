<?php
/**
 * File: includes/middleware.php
 * Location: /tour_update/includes/middleware.php
 *
 * Authentication guard functions that protect pages and API endpoints.
 *
 * - require_user()  → blocks access unless a regular user is logged in
 * - require_admin() → blocks access unless an admin is logged in
 * - require_csrf()  → blocks write API requests with an invalid CSRF token
 *
 * Use $is_api = true when calling from an API endpoint (returns JSON error).
 * Use $is_api = false (default) when calling from a page (does a redirect).
 */

if (!defined('APP_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/auth.php';
}
if (!function_exists('json_error')) {
    require_once __DIR__ . '/response.php';
}

/**
 * Require a logged-in regular user.
 *
 * Page usage (default):
 *   The user is redirected to /account/login with a ?redirect= parameter
 *   so they are sent back to the right page after logging in.
 *
 * API usage ($is_api = true):
 *   Returns a 401 Unauthorized JSON response and stops execution.
 *
 * @param bool $is_api  Set true when calling from an API endpoint
 */
function require_user(bool $is_api = false): void
{
    if (!is_logged_in()) {
        if ($is_api) {
            json_error('Authentication required. Please log in.', 401);
        } else {
            // Capture the current URL so we can redirect back after login
            $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
            header('Location: ' . APP_URL . '/account/login' . ($redirect ? '?redirect=' . $redirect : ''));
            exit;
        }
    }
}

/**
 * Require a logged-in admin.
 *
 * Page usage (default):
 *   Redirects to /admin/login.
 *
 * API usage ($is_api = true):
 *   Returns a 401 Unauthorized JSON response and stops execution.
 *
 * Note: An active user session does NOT satisfy this check.
 *       User and admin sessions are completely separate.
 *
 * @param bool $is_api  Set true when calling from an API endpoint
 */
function require_admin(bool $is_api = false): void
{
    if (!is_admin_logged_in()) {
        if ($is_api) {
            json_error('Admin authentication required.', 401);
        } else {
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }
    }
}

/**
 * Require a valid CSRF token for write operations (POST, PUT, DELETE).
 * Only used by API endpoints — HTML pages never call this directly.
 *
 * If the token is missing or doesn't match the session, execution is stopped
 * with a 403 Forbidden JSON response.
 *
 * The token is sent by jQuery automatically (set up in header.php's ajaxSetup)
 * via the X-CSRF-Token request header.
 */
function require_csrf(): void
{
    if (!validate_csrf_token()) {
        json_error('Invalid or missing security token. Please refresh the page and try again.', 403);
    }
}
