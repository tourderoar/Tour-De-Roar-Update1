<?php
/**
 * File: includes/stripe.php
 * Location: /tour_update/includes/stripe.php
 *
 * Initialises the Stripe PHP SDK with the correct API key for the current
 * environment (test key locally, live key on production).
 *
 * Require this file in any PHP script that needs to call the Stripe API.
 * The SDK is loaded via Composer's autoloader.
 *
 * Before using this file, install the SDK by running in your project root:
 *   composer require stripe/stripe-php
 */

if (!defined('APP_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}

// The Stripe SDK is installed by Composer into the vendor/ folder.
// vendor/autoload.php loads ALL Composer-managed packages automatically.
$autoload_path = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($autoload_path)) {
    // Composer hasn't been run yet — guide the developer
    if (APP_ENV === 'local') {
        die(
            '<p style="font-family: monospace; padding: 20px;">' .
            '<strong>Stripe SDK not found.</strong><br>' .
            'Run this command in your project root:<br><br>' .
            '<code>composer require stripe/stripe-php</code>' .
            '</p>'
        );
    } else {
        error_log('CRITICAL: Stripe SDK not found — vendor/autoload.php is missing.');
        die('A configuration error occurred. Please contact support.');
    }
}

require_once $autoload_path;

// Configure the Stripe library with the secret key for the current environment.
// STRIPE_SECRET_KEY is defined in config.php — test key locally, live key on production.
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Pin the Stripe API version so behaviour doesn't change if Stripe releases an update.
// If Stripe releases a new version, upgrade here deliberately after reading the changelog.
\Stripe\Stripe::setApiVersion('2024-06-20');
