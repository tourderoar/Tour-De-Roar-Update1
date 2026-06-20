<?php
/**
 * File: config.sample.php
 * Location: /tour_update/config.sample.php
 *
 * CONFIGURATION TEMPLATE
 * ----------------------
 * This is a sample configuration file for the Tour de Roar application.
 * Copy this file to config.php and fill in your actual credentials.
 *
 * SETUP INSTRUCTIONS:
 * 1. Copy this file: cp config.sample.php config.php
 * 2. Edit config.php with your actual database credentials
 * 3. Add your Stripe API keys (get from https://dashboard.stripe.com/apikeys)
 * 4. Add your ZeptoMail token (production only - get from https://zeptomail.zoho.com)
 * 5. Never commit config.php to git (it's in .gitignore)
 *
 * IMPORTANT: Every other PHP file that needs these values must require_once config.php.
 * NEVER hardcode URLs, API keys, or credentials anywhere else in the codebase.
 */

// -----------------------------------------------------------------------
// ENVIRONMENT DETECTION
// -----------------------------------------------------------------------
// We detect 'local' by checking if the request is coming from localhost.
// On a production server the host will be a real domain (e.g. tourderoar.org).
// You can also override this by setting APP_ENV in your Apache VirtualHost:
//   SetEnv APP_ENV "production"
$detected_env = (
    isset($_SERVER['HTTP_HOST']) &&
    (
        $_SERVER['HTTP_HOST'] === 'localhost' ||
        strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
        $_SERVER['HTTP_HOST'] === '127.0.0.1'
    )
) ? 'local' : 'production';

// getenv('APP_ENV') allows a manual override via the server environment
define('APP_ENV', getenv('APP_ENV') ?: $detected_env);


// -----------------------------------------------------------------------
// APPLICATION BASE URL
// -----------------------------------------------------------------------
// APP_URL is used everywhere — PHP redirects, email links, JS AJAX calls.
// It must never be hardcoded anywhere else in the codebase.
//
// LOCAL:      Project lives in a subdirectory of htdocs
// PRODUCTION: Project files are at the domain root — adjust if different
if (APP_ENV === 'local') {
    // Change 'tour_update' to match your local folder name
    define('APP_URL', 'http://localhost/tour_update');
} else {
    // On production, read from a server environment variable set in Apache/Nginx config.
    // Apache example (in VirtualHost block): SetEnv APP_URL "https://yourdomain.com"
    define('APP_URL', rtrim(getenv('APP_URL') ?: 'https://yourdomain.com', '/'));
}


// -----------------------------------------------------------------------
// ERROR LOGGING
// -----------------------------------------------------------------------
// Configure PHP to log errors to logs/error.log
$log_dir = __DIR__ . '/logs';
$error_log_file = $log_dir . '/error.log';

// Create logs directory if it doesn't exist
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Configure error logging
ini_set('log_errors', '1');
ini_set('error_log', $error_log_file);

// Set error reporting level
if (APP_ENV === 'local') {
    // Show all errors in development
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // Log errors but don't display them in production
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}


// -----------------------------------------------------------------------
// DATABASE CREDENTIALS
// -----------------------------------------------------------------------
if (APP_ENV === 'local') {
    define('DB_HOST',    'localhost');
    define('DB_NAME',    'tour_de_roar');   // Create this DB in phpMyAdmin first
    define('DB_USER',    'root');
    define('DB_PASS',    '');               // XAMPP default: blank password
    define('DB_CHARSET', 'utf8mb4');
} else {
    // On production, credentials are set via server environment variables.
    // Never put real credentials in this file.
    // Set these in your Apache/Nginx config:
    //   SetEnv DB_HOST "your_db_host"
    //   SetEnv DB_NAME "your_db_name"
    //   SetEnv DB_USER "your_db_user"
    //   SetEnv DB_PASS "your_db_password"
    define('DB_HOST',    getenv('DB_HOST')    ?: 'localhost');
    define('DB_NAME',    getenv('DB_NAME')    ?: '');
    define('DB_USER',    getenv('DB_USER')    ?: '');
    define('DB_PASS',    getenv('DB_PASS')    ?: '');
    define('DB_CHARSET', 'utf8mb4');
}


// -----------------------------------------------------------------------
// STRIPE KEYS
// -----------------------------------------------------------------------
// Test keys are used locally; live keys are used on production.
//
// IMPORTANT:
//   pk_ (publishable key) — safe to expose to the browser via JavaScript
//   sk_ (secret key)      — server-side ONLY, never sent to the browser
//   whsec_ (webhook secret) — used to verify Stripe's webhook signatures
//
// To get your keys: https://dashboard.stripe.com/apikeys
if (APP_ENV === 'local') {
    // REPLACE THESE WITH YOUR STRIPE TEST KEYS
    define('STRIPE_PUBLIC_KEY',     'pk_test_YOUR_STRIPE_TEST_PUBLISHABLE_KEY');
    define('STRIPE_SECRET_KEY',     'sk_test_YOUR_STRIPE_TEST_SECRET_KEY');
    define('STRIPE_WEBHOOK_SECRET', 'whsec_YOUR_STRIPE_WEBHOOK_SECRET');
} else {
    // On production, set these via server environment variables:
    //   SetEnv STRIPE_PUBLIC_KEY "pk_live_YOUR_LIVE_KEY"
    //   SetEnv STRIPE_SECRET_KEY "sk_live_YOUR_LIVE_KEY"
    //   SetEnv STRIPE_WEBHOOK_SECRET "whsec_YOUR_WEBHOOK_SECRET"
    define('STRIPE_PUBLIC_KEY',     getenv('STRIPE_PUBLIC_KEY')     ?: '');
    define('STRIPE_SECRET_KEY',     getenv('STRIPE_SECRET_KEY')     ?: '');
    define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET') ?: '');
}


// -----------------------------------------------------------------------
// ZEPTOMAIL CREDENTIALS
// -----------------------------------------------------------------------
// ZeptoMail sends transactional emails (activation, password reset, receipts).
// On local: emails are NOT sent — they are written to logs/mail.log instead.
// On production: emails are sent via the ZeptoMail API.
//
// To get your token: https://zeptomail.zoho.com -> Settings -> API Tokens
// Set via environment variable on production:
//   SetEnv ZEPTOMAIL_TOKEN "your_zeptomail_api_token"
define('ZEPTOMAIL_API_URL',  'https://api.zeptomail.com/v1.1/email');
define('ZEPTOMAIL_TOKEN',    getenv('ZEPTOMAIL_TOKEN') ?: '');
define('MAIL_FROM_ADDRESS',  'noreply@yourdomain.com');  // CHANGE THIS to your domain
define('MAIL_FROM_NAME',     'Your Organization Name');   // CHANGE THIS to your org name


// -----------------------------------------------------------------------
// PHP ERROR REPORTING
// -----------------------------------------------------------------------
// Show all errors locally to catch issues early.
// On production: hide errors from the browser, but still log them.
if (APP_ENV === 'local') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}
