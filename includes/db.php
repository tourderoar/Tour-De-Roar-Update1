<?php
/**
 * File: includes/db.php
 * Location: /tour_update/includes/db.php
 *
 * Provides a single shared PDO database connection for the entire application.
 * Uses the Singleton pattern — one connection is created on the first call,
 * then reused on every subsequent call throughout the request lifecycle.
 *
 * Usage in any PHP file:
 *   $db   = get_db();
 *   $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
 *   $stmt->execute([$id]);
 *   $row  = $stmt->fetch();
 *
 * All queries must use prepared statements — never interpolate variables into SQL.
 */

// Load config if it hasn't been loaded yet (provides DB_HOST, DB_NAME, etc.)
if (!defined('DB_HOST')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Returns the single shared PDO database connection.
 * Creates it on the first call, then reuses it on every subsequent call.
 *
 * @return PDO
 * @throws RuntimeException if the connection cannot be established
 */
function get_db(): PDO
{
    // $instance is declared static — it survives between calls to this function
    // within the same HTTP request, so the connection is only made once
    static $instance = null;

    if ($instance === null) {
        // Build the DSN (Data Source Name) — the connection string for MySQL
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        // PDO connection options:
        // ERRMODE_EXCEPTION  — throw exceptions on DB errors so we can catch them
        // FETCH_ASSOC        — return rows as associative arrays (column name => value)
        // EMULATE_PREPARES 0 — use real prepared statements for proper security
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Never expose detailed DB error messages in production —
            // they can reveal your database structure to attackers
            if (APP_ENV === 'local') {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage());
            } else {
                error_log('Database connection failed: ' . $e->getMessage());
                throw new RuntimeException('A database error occurred. Please try again later.');
            }
        }
    }

    return $instance;
}
