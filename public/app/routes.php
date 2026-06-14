<?php
/**
 * Public site routes
 * Maps URL paths to files relative to /public
 */

return [
    '/' => 'app/home.php',
    '/home' => 'app/home.php',
    '/about' => 'app/pages/about.php',
    '/contact' => 'app/pages/contact.php',
    '/about-the-engine' => 'app/pages/colophon.php',

    '/admin' => 'app/admin/index.php',
    '/admin/login' => 'app/admin/adminlogin.php',
];
