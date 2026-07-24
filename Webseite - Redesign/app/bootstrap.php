<?php
declare(strict_types=1);

if (!defined('HUMPLORE_FORCE_HTTPS')) {
    // Enable after the certificate is live on the public domain.
    define('HUMPLORE_FORCE_HTTPS', false);
}

if (!defined('HUMPLORE_BOOTSTRAPPED')) {
    define('HUMPLORE_BOOTSTRAPPED', true);

    require_once __DIR__ . '/support/helpers.php';

    humplore_force_https($_SERVER);

    if (session_status() !== PHP_SESSION_ACTIVE) {
        humplore_configure_session_cookie($_SERVER);
        session_start();
    }

    date_default_timezone_set('Europe/Berlin');

    require_once __DIR__ . '/support/auth.php';
    require_once __DIR__ . '/support/navigation.php';
    require_once __DIR__ . '/support/content.php';
    require_once __DIR__ . '/support/reports.php';
    require_once __DIR__ . '/support/profile-page.php';
    require_once __DIR__ . '/support/profile-actions.php';
    require_once __DIR__ . '/support/platform-page.php';
    require_once __DIR__ . '/support/search-discovery.php';
    require_once __DIR__ . '/support/post-editor.php';
}

if (!function_exists('humplore_db')) {
    function humplore_db(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $pdo = require __DIR__ . '/../config/database.php';

        if (!$pdo instanceof PDO) {
            throw new RuntimeException('Database bootstrap did not return a PDO instance.');
        }

        humplore_ensure_database_schema($pdo);

        return $pdo;
    }
}
