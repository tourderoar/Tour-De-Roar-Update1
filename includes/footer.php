<?php
/**
 * File: includes/footer.php
 * Location: /tour_update/includes/footer.php
 *
 * Shared HTML footer for all public-facing pages.
 * Closes the <main> tag opened by header.php, then outputs the full footer,
 * loads main.js, and closes </body> and </html>.
 *
 * Always require header.php first, then footer.php at the very bottom of every page.
 */

if (!defined('APP_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}
?>
</main><!-- /main — opened in header.php -->

<!-- =====================================================================
     FOOTER
====================================================================== -->
<footer class="text-white pt-14 pb-8"
        style="background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);">

    <div class="container mx-auto px-6">

        <!-- Top grid: brand / quick links / contact -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">

            <!-- ── Brand column ── -->
            <div>
                <div class="flex items-center mb-4">
                    <img src="<?= APP_URL ?>/images/logos/logo-white-version.png"
                         alt="Tour de Roar Lion Logo"
                         class="h-14 w-14 mr-3">
                    <span class="text-xl font-bold logo-colors">Tour de Roar</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-5">
                    Cycling for the children of Mpumalanga. Every pedal stroke funds education,
                    nutrition, and hope for vulnerable children in our community.
                </p>
                <!-- Social links -->
                <div class="flex items-center space-x-4">
                    <a href="https://www.facebook.com/tourderoar" target="_blank" rel="noopener noreferrer"
                       aria-label="Facebook"
                       class="w-9 h-9 rounded-full flex items-center justify-center
                              bg-gray-700 hover:bg-blue-600 transition-colors duration-200">
                        <i class="fab fa-facebook-f text-sm"></i>
                    </a>
                    <a href="https://www.instagram.com/tourderoar" target="_blank" rel="noopener noreferrer"
                       aria-label="Instagram"
                       class="w-9 h-9 rounded-full flex items-center justify-center
                              bg-gray-700 hover:bg-pink-600 transition-colors duration-200">
                        <i class="fab fa-instagram text-sm"></i>
                    </a>
                    <a href="https://www.tiktok.com/@tourderoar" target="_blank" rel="noopener noreferrer"
                       aria-label="TikTok"
                       class="w-9 h-9 rounded-full flex items-center justify-center
                              bg-gray-700 hover:bg-gray-500 transition-colors duration-200">
                        <i class="fab fa-tiktok text-sm"></i>
                    </a>
                    <a href="https://www.youtube.com/@tourderoar" target="_blank" rel="noopener noreferrer"
                       aria-label="YouTube"
                       class="w-9 h-9 rounded-full flex items-center justify-center
                              bg-gray-700 hover:bg-red-600 transition-colors duration-200">
                        <i class="fab fa-youtube text-sm"></i>
                    </a>
                </div>
            </div>

            <!-- ── Quick links ── -->
            <div>
                <h3 class="font-bold text-white mb-5 text-sm uppercase tracking-wider">
                    Quick Links
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="<?= APP_URL ?>/"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/about"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            About
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/events"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Events
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/gallery"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Gallery
                        </a>
                    </li>
                </ul>
            </div>

            <!-- ── Get Involved ── -->
            <div>
                <h3 class="font-bold text-white mb-5 text-sm uppercase tracking-wider">
                    Get Involved
                </h3>
                <ul class="space-y-3">
                    <li>
                        <a href="<?= APP_URL ?>/sponsorship"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Sponsorship
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/donate"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Donate
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/store"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Store
                        </a>
                    </li>
                    <li>
                        <a href="<?= APP_URL ?>/contact"
                           class="text-gray-400 hover:text-orange-400 transition-colors text-sm">
                            Contact
                        </a>
                    </li>
                </ul>
            </div>

        </div><!-- /top grid -->

        <!-- Divider -->
        <div class="border-t border-gray-700 pt-8 flex flex-col md:flex-row
                    justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">
                &copy; <?= date('Y') ?> Tour de Roar. All rights reserved.
            </p>
        </div>

    </div><!-- /container -->
</footer>

<!-- Shared JavaScript — loaded at the bottom so it doesn't block page render -->
<script src="<?= APP_URL ?>/js/main.js"></script>

<script>
    /**
     * Toggle the mobile navigation menu open/closed.
     * The hamburger icon switches between bars and X to give visual feedback.
     */
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('hamburger-icon');
        if (menu && icon) {
            menu.classList.toggle('hidden');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        }
    }

    /**
     * Toggle the user account dropdown in the desktop nav.
     * Clicking outside the dropdown closes it (handled by the document click listener below).
     */
    const userMenuBtn      = document.getElementById('user-menu-btn');
    const userDropdown     = document.getElementById('user-dropdown');
    const userMenuWrapper  = document.getElementById('user-menu-wrapper');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        // Close the dropdown if the user clicks anywhere outside it
        document.addEventListener('click', function (e) {
            if (userMenuWrapper && !userMenuWrapper.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }

    /**
     * Log the current user out by posting to the API, then redirect to home.
     * The CSRF token is attached automatically via $.ajaxSetup (set in header.php).
     */
    function logoutUser() {
        $.post(APP_URL + '/api/auth/logout')
            .always(function () {
                // Redirect regardless of success/failure —
                // worst case the session cookie just expires on its own
                window.location.href = APP_URL + '/';
            });
    }
</script>

</body>
</html>
