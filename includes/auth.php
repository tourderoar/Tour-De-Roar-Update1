<?php
/**
 * File: includes/auth.php
 * Location: /tour_update/includes/auth.php
 *
 * Session management and authentication helpers for the entire application.
 * Handles session initialisation, inactivity timeouts, CSRF token generation,
 * and functions for reading the currently logged-in user or admin.
 *
 * Call session_init() at the top of every page (via header.php) before any output.
 */

if (!defined('APP_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Initialise the PHP session with secure cookie settings.
 * Safe to call multiple times — checks if a session is already active first.
 * Also handles inactivity timeout and CSRF token generation.
 */
function session_init(): void
{
    // Only start a new session if one isn't already running
    if (session_status() === PHP_SESSION_NONE) {

        // Configure the session cookie to be as secure as possible
        session_set_cookie_params([
            'lifetime' => 0,                          // Session cookie — expires when browser closes
            'path'     => '/',
            'domain'   => '',
            'secure'   => (APP_ENV === 'production'), // Only send over HTTPS on production
            'httponly' => true,                       // JS cannot read this cookie (blocks XSS theft)
            'samesite' => 'Strict',                   // Cookie not sent on cross-site requests (blocks CSRF)
        ]);

        session_start();
    }

    // Generate a CSRF token if this session doesn't have one yet.
    // This token is injected into every page and must be echoed back
    // by JavaScript on every write (POST/PUT/DELETE) API request.
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes(32) generates 32 cryptographically secure random bytes
        // bin2hex converts them to a 64-character hex string
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // -----------------------------------------------------------------------
    // INACTIVITY TIMEOUT
    // Automatically log out sessions that have been idle too long.
    // This protects users who forget to log out on a shared computer.
    // -----------------------------------------------------------------------
    $now = time();

    if (isset($_SESSION['user'])) {
        $user_timeout = 2 * 60 * 60; // 2 hours for regular users
        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $user_timeout) {
            // Session expired — destroy it and start a fresh one
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return;
        }
    }

    if (isset($_SESSION['admin'])) {
        $admin_timeout = 1 * 60 * 60; // 1 hour for admins (stricter)
        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $admin_timeout) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            return;
        }
    }

    // Record when the user last made a request — used for the timeout check above
    $_SESSION['last_activity'] = $now;
}

/**
 * Returns the currently logged-in user's data array, or null if no user is logged in.
 * The array contains: id, first_name, last_name, email (set at login time).
 *
 * @return array|null
 */
function get_logged_in_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Returns the currently logged-in admin's data array, or null if no admin is logged in.
 *
 * @return array|null
 */
function get_logged_in_admin(): ?array
{
    return $_SESSION['admin'] ?? null;
}

/**
 * Returns true if a regular user is currently logged in.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return isset($_SESSION['user']['id']) && !empty($_SESSION['user']['id']);
}

/**
 * Returns true if an admin is currently logged in.
 *
 * @return bool
 */
function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin']['id']) && !empty($_SESSION['admin']['id']);
}

/**
 * Returns the CSRF token string for the current session.
 * Used by header.php to inject the token into the page's <meta> tag.
 *
 * @return string
 */
function get_csrf_token(): string
{
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Validates the CSRF token sent in an incoming API request header.
 * Call this at the start of every API endpoint that modifies data (POST/PUT/DELETE).
 *
 * The token is sent by jQuery in the 'X-CSRF-Token' request header.
 * We use hash_equals() instead of === to prevent timing attacks
 * (timing attacks can be used to guess the token character by character).
 *
 * @return bool  True if the token is valid, false if it is missing or wrong
 */
function validate_csrf_token(): bool
{
    $received = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';

    return !empty($expected) && hash_equals($expected, $received);
}
