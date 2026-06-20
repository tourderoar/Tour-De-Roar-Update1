<?php
/**
 * File: includes/header.php
 * Location: /tour_update/includes/header.php
 *
 * Shared HTML header for all public-facing pages.
 * Outputs: full <head> section, sticky navigation bar, opens <main>.
 *
 * USAGE — at the very top of any public page:
 *   <?php
 *   $page_title = 'Events';
 *   require_once __DIR__ . '/includes/header.php';
 *   ?>
 *
 * footer.php closes </main>, </body>, and </html>.
 * Always use header.php and footer.php together.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';

// Start the session with secure settings (safe to call on every page load)
session_init();

// Default title if the page didn't set $page_title before requiring this file
$page_title = $page_title ?? 'Tour de Roar';

// -----------------------------------------------------------------------
// ACTIVE NAV DETECTION
// Determine which page is active so we can highlight the correct nav link.
// Strips the RewriteBase prefix and .php extension to get a clean identifier.
// -----------------------------------------------------------------------
$current_uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$current_page = strtolower(basename($current_uri, '.php'));

// Root paths all map to 'index'
if ($current_page === '' || $current_page === 'tour_update' || $current_page === '/') {
    $current_page = 'index';
}

/**
 * Returns the CSS class string for a desktop nav link.
 * Adds the 'active' class (defined in styles.css) when the link matches the current page.
 *
 * @param string $page     Page identifier for this link (e.g. 'events')
 * @param string $current  The current page identifier
 * @return string
 */
function nav_active(string $page, string $current): string
{
    $base = 'nav-link text-gray-700 font-semibold transition-colors';
    return ($page === $current) ? $base . ' active' : $base;
}

// Read the logged-in user for the auth section of the nav (null if guest)
$current_user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?> &mdash; Tour de Roar</title>

    <!--
        CSRF TOKEN META TAG
        The CSRF token is stored here so JavaScript can read it once at page load.
        jQuery is configured below (in ajaxSetup) to automatically attach this token
        as an X-CSRF-Token header on every AJAX request. This prevents CSRF attacks
        on all write operations (POST / PUT / DELETE).
    -->
    <meta name="csrf-token" content="<?= htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

    <!-- Tailwind CSS v3 CDN -->
    <script src="https://cdn.tailwindcss.com/"></script>

    <!-- Font Awesome 6 — icons throughout the UI -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

    <!-- jQuery 3.7.1 — all AJAX calls use this -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
            crossorigin="anonymous"></script>

    <!--
        Stripe.js v3 — loaded globally on every page.
        Stripe recommends this (not just on checkout pages) so their fraud
        detection signals are collected throughout the user's session.
    -->
    <script src="https://js.stripe.com/v3/"></script>

    <!-- Shared custom styles (brand gradient text, nav-link hover effects, etc.) -->
    <link rel="stylesheet" href="<?= APP_URL ?>/css/styles.css">

    <!--
        Register Tour de Roar brand colours as Tailwind utility classes.
        Usage examples: text-roar-orange  bg-roar-purple  border-roar-blue
    -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'roar-orange': '#FF6B1A',
                        'roar-red':    '#E53E3E',
                        'roar-purple': '#805AD5',
                        'roar-blue':   '#3182CE',
                        'roar-green':  '#68D391',
                        'roar-yellow': '#F6E05E',
                    }
                }
            }
        }
    </script>

    <!--
        APP_URL — injected from PHP so JavaScript never has a hardcoded base URL.
        All AJAX endpoint paths are built as: APP_URL + '/api/...'

        STRIPE_PK — the publishable key is safe to expose in JS (used by Stripe.js).

        ajaxSetup — attaches X-CSRF-Token to every $.ajax / $.post / $.get call
        automatically, so individual API calls don't need to set it manually.
    -->
    <script>
        const APP_URL    = '<?= APP_URL ?>';
        const STRIPE_PK  = '<?= htmlspecialchars(STRIPE_PUBLIC_KEY, ENT_QUOTES, 'UTF-8') ?>';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const IS_LOGGED_IN = <?= is_logged_in() ? 'true' : 'false' ?>;

        $.ajaxSetup({
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        });
    </script>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">

<!-- =====================================================================
     NAVIGATION BAR
     Sticky, white background, 4px brand-gradient bottom border.
====================================================================== -->
<nav class="bg-white shadow-sm sticky top-0 z-50"
     style="border-bottom: 4px solid transparent;
            border-image: linear-gradient(90deg, #FF6B1A, #E53E3E, #805AD5, #3182CE, #68D391, #F6E05E) 1;">

    <div class="container mx-auto px-6 py-3">
        <div class="flex justify-between items-center">

            <!-- ── Logo ── -->
            <a href="<?= APP_URL ?>/" class="flex items-center group">
                <img src="<?= APP_URL ?>/images/logos/logo-white-version.png"
                     alt="Tour de Roar Lion Logo"
                     class="h-14 w-14 mr-3 transition-transform duration-300 group-hover:scale-105">
                <span class="text-2xl font-bold logo-colors">Tour de Roar</span>
            </a>

            <!-- ── Desktop nav links ── -->
            <div class="hidden md:flex items-center space-x-5">

                <a href="<?= APP_URL ?>/"
                   class="<?= nav_active('index', $current_page) ?>">
                    <i class="fas fa-home mr-1"></i>Home
                </a>
                <a href="<?= APP_URL ?>/about"
                   class="<?= nav_active('about', $current_page) ?>">
                    <i class="fas fa-info-circle mr-1"></i>About
                </a>
                <a href="<?= APP_URL ?>/events"
                   class="<?= nav_active('events', $current_page) ?>">
                    <i class="fas fa-calendar-alt mr-1"></i>Events
                </a>
                <a href="<?= APP_URL ?>/store"
                   class="<?= nav_active('store', $current_page) ?>">
                    <i class="fas fa-shopping-bag mr-1"></i>Store
                </a>
                <a href="<?= APP_URL ?>/gallery"
                   class="<?= nav_active('gallery', $current_page) ?>">
                    <i class="fas fa-images mr-1"></i>Gallery
                </a>
                <a href="<?= APP_URL ?>/sponsorship"
                   class="<?= nav_active('sponsorship', $current_page) ?>">
                    <i class="fas fa-handshake mr-1"></i>Sponsorship
                </a>
                <a href="<?= APP_URL ?>/donate"
                   class="<?= nav_active('donate', $current_page) ?>">
                    <i class="fas fa-heart mr-1"></i>Donate
                </a>
                <a href="<?= APP_URL ?>/contact"
                   class="<?= nav_active('contact', $current_page) ?>">
                    <i class="fas fa-envelope mr-1"></i>Contact
                </a>

                <!-- ── Auth section ── -->
                <?php if ($current_user): ?>
                    <!-- Logged-in: username dropdown -->
                    <div class="relative" id="user-menu-wrapper">
                        <button id="user-menu-btn"
                                class="flex items-center nav-link text-gray-700 font-semibold
                                       focus:outline-none cursor-pointer">
                            <i class="fas fa-user-circle mr-1" style="color:#805AD5;"></i>
                            <?= htmlspecialchars($current_user['first_name'], ENT_QUOTES, 'UTF-8') ?>
                            <i class="fas fa-chevron-down ml-1 text-xs text-gray-400"></i>
                        </button>
                        <!-- Dropdown panel -->
                        <div id="user-dropdown"
                             class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl
                                    shadow-2xl border border-gray-100 z-50 overflow-hidden">
                            <a href="<?= APP_URL ?>/account/dashboard"
                               class="flex items-center px-4 py-3 text-sm text-gray-700
                                      hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                <i class="fas fa-tachometer-alt w-5 mr-2 text-gray-400"></i>
                                Dashboard
                            </a>
                            <a href="<?= APP_URL ?>/account/transactions"
                               class="flex items-center px-4 py-3 text-sm text-gray-700
                                      hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                <i class="fas fa-receipt w-5 mr-2 text-gray-400"></i>
                                My Transactions
                            </a>
                            <a href="<?= APP_URL ?>/account/profile"
                               class="flex items-center px-4 py-3 text-sm text-gray-700
                                      hover:bg-orange-50 hover:text-orange-600 transition-colors">
                                <i class="fas fa-user-cog w-5 mr-2 text-gray-400"></i>
                                Profile
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <button onclick="logoutUser()"
                                    class="w-full flex items-center px-4 py-3 text-sm
                                           text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                                <i class="fas fa-sign-out-alt w-5 mr-2"></i>
                                Sign Out
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Guest: Login link + Register button -->
                    <a href="<?= APP_URL ?>/account/login"
                       class="nav-link text-gray-700 font-semibold transition-colors">
                        <i class="fas fa-sign-in-alt mr-1"></i>Login
                    </a>
                    <a href="<?= APP_URL ?>/account/register"
                       class="px-5 py-2 rounded-lg font-bold text-white text-sm shadow-md
                              transition-all duration-200 hover:shadow-lg hover:scale-105"
                       style="background: linear-gradient(45deg, #FF6B1A, #E53E3E);">
                        <i class="fas fa-user-plus mr-1"></i>Register
                    </a>
                <?php endif; ?>

            </div><!-- /desktop nav -->

            <!-- ── Mobile hamburger button ── -->
            <button onclick="toggleMobileMenu()"
                    class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none p-2">
                <i class="fas fa-bars text-xl" id="hamburger-icon"></i>
            </button>

        </div><!-- /flex row -->

        <!-- ── Mobile menu (hidden by default) ── -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-gray-100 pt-4">
            <div class="flex flex-col space-y-1">
                <a href="<?= APP_URL ?>/"            class="mobile-nav-link"><i class="fas fa-home w-5"></i> Home</a>
                <a href="<?= APP_URL ?>/about"       class="mobile-nav-link"><i class="fas fa-info-circle w-5"></i> About</a>
                <a href="<?= APP_URL ?>/events"      class="mobile-nav-link"><i class="fas fa-calendar-alt w-5"></i> Events</a>
                <a href="<?= APP_URL ?>/store"       class="mobile-nav-link"><i class="fas fa-shopping-bag w-5"></i> Store</a>
                <a href="<?= APP_URL ?>/gallery"     class="mobile-nav-link"><i class="fas fa-images w-5"></i> Gallery</a>
                <a href="<?= APP_URL ?>/sponsorship" class="mobile-nav-link"><i class="fas fa-handshake w-5"></i> Sponsorship</a>
                <a href="<?= APP_URL ?>/donate"      class="mobile-nav-link"><i class="fas fa-heart w-5"></i> Donate</a>
                <a href="<?= APP_URL ?>/contact"     class="mobile-nav-link"><i class="fas fa-envelope w-5"></i> Contact</a>
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <?php if ($current_user): ?>
                        <a href="<?= APP_URL ?>/account/dashboard"   class="mobile-nav-link"><i class="fas fa-tachometer-alt w-5"></i> Dashboard</a>
                        <a href="<?= APP_URL ?>/account/transactions" class="mobile-nav-link"><i class="fas fa-receipt w-5"></i> Transactions</a>
                        <a href="<?= APP_URL ?>/account/profile"      class="mobile-nav-link"><i class="fas fa-user-cog w-5"></i> Profile</a>
                        <button onclick="logoutUser()" class="mobile-nav-link text-red-600 w-full text-left">
                            <i class="fas fa-sign-out-alt w-5"></i> Sign Out
                        </button>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/account/login"    class="mobile-nav-link"><i class="fas fa-sign-in-alt w-5"></i> Login</a>
                        <a href="<?= APP_URL ?>/account/register" class="mobile-nav-link"><i class="fas fa-user-plus w-5"></i> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /mobile menu -->

    </div><!-- /container -->
</nav><!-- /nav -->

<style>
    .mobile-nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        color: #374151;
        font-weight: 500;
        font-size: 15px;
        transition: background 0.15s, color 0.15s;
        text-decoration: none;
    }
    .mobile-nav-link:hover { background: #fff7ed; color: #FF6B1A; }
</style>

<!-- Page-specific content starts here. footer.php closes </main>, </body>, </html>. -->
<main class="flex-grow">
