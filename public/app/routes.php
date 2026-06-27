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
    '/worldbuilding' => 'app/pages/worldbuilding.php',

    '/admin' => 'app/admin/index.php',
    '/admin/login' => 'app/admin/adminlogin.php',
    '/admin/content-library' => 'app/admin/content-library.php',
    '/admin/email-library' => 'app/admin/email-library.php',
    '/admin/api-tester' => 'app/admin/api-tester.php',
    '/admin/users' => 'app/admin/users.php',
    '/admin/event-logs' => 'app/admin/event-logs.php',
    '/admin/waitlist' => 'app/admin/waitlist.php',
];
