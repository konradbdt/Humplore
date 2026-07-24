<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('humplore_server_value')) {
    function humplore_server_value(array $server, string $key): string
    {
        $value = $server[$key] ?? '';

        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }
}

if (!function_exists('humplore_first_header_token')) {
    function humplore_first_header_token(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $parts = explode(',', $value);

        return strtolower(trim((string) ($parts[0] ?? '')));
    }
}

if (!function_exists('humplore_request_is_secure')) {
    function humplore_request_is_secure(array $server): bool
    {
        $https = strtolower(humplore_server_value($server, 'HTTPS'));
        if ($https !== '' && $https !== 'off') {
            return true;
        }

        if (strtolower(humplore_server_value($server, 'REQUEST_SCHEME')) === 'https') {
            return true;
        }

        if (humplore_server_value($server, 'SERVER_PORT') === '443') {
            return true;
        }

        if (humplore_server_value($server, 'HTTP_X_FORWARDED_PORT') === '443') {
            return true;
        }

        if (humplore_first_header_token(humplore_server_value($server, 'HTTP_X_FORWARDED_PROTO')) === 'https') {
            return true;
        }

        $forwardedSsl = strtolower(humplore_server_value($server, 'HTTP_X_FORWARDED_SSL'));
        if ($forwardedSsl === 'on' || $forwardedSsl === '1') {
            return true;
        }

        $frontEndHttps = strtolower(humplore_server_value($server, 'HTTP_FRONT_END_HTTPS'));
        if ($frontEndHttps === 'on' || $frontEndHttps === '1') {
            return true;
        }

        return false;
    }
}

if (!function_exists('humplore_request_scheme')) {
    function humplore_request_scheme(array $server): string
    {
        return humplore_request_is_secure($server) ? 'https' : 'http';
    }
}

if (!function_exists('humplore_request_host')) {
    function humplore_request_host(array $server): string
    {
        $host = humplore_server_value($server, 'HTTP_X_FORWARDED_HOST');
        if ($host === '') {
            $host = humplore_server_value($server, 'HTTP_HOST');
        }
        if ($host === '') {
            $host = humplore_server_value($server, 'SERVER_NAME');
        }
        if ($host === '') {
            return 'localhost';
        }

        $host = trim((string) explode(',', $host)[0]);

        if (preg_match('/^\[[0-9A-Fa-f:.]+\](?::\d+)?$/', $host) === 1) {
            return $host;
        }

        if (preg_match('/^[A-Za-z0-9.-]+(?::\d+)?$/', $host) === 1) {
            return $host;
        }

        return 'localhost';
    }
}

if (!function_exists('humplore_absolute_url')) {
    function humplore_absolute_url(string $path, ?array $server = null): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $server ??= $_SERVER;
        $normalizedPath = '/' . ltrim($path, '/');

        return humplore_request_scheme($server) . '://' . humplore_request_host($server) . $normalizedPath;
    }
}

if (!function_exists('humplore_https_redirect_enabled')) {
    function humplore_https_redirect_enabled(): bool
    {
        if (defined('HUMPLORE_FORCE_HTTPS')) {
            return HUMPLORE_FORCE_HTTPS;
        }

        $flag = strtolower(trim((string) getenv('HUMPLORE_FORCE_HTTPS')));

        return in_array($flag, ['1', 'true', 'on', 'yes'], true);
    }
}

if (!function_exists('humplore_force_https')) {
    function humplore_force_https(array $server): void
    {
        if (!humplore_https_redirect_enabled() || humplore_request_is_secure($server)) {
            return;
        }

        $requestUri = humplore_server_value($server, 'REQUEST_URI');
        if ($requestUri === '') {
            $requestUri = '/';
        }

        header('Location: https://' . humplore_request_host($server) . $requestUri, true, 308);
        exit;
    }
}

if (!function_exists('humplore_configure_session_cookie')) {
    function humplore_configure_session_cookie(array $server): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $params = session_get_cookie_params();

        session_set_cookie_params([
            'lifetime' => (int) ($params['lifetime'] ?? 0),
            'path' => (string) ($params['path'] ?? '/'),
            'domain' => (string) ($params['domain'] ?? ''),
            'secure' => humplore_request_is_secure($server),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => (string) ($params['samesite'] ?? 'Lax'),
        ]);
    }
}

if (!function_exists('profile_img_src')) {
    function profile_img_src(int $userId): string
    {
        return 'media.php?type=profile&user_id=' . (int) $userId;
    }
}

if (!function_exists('post_img_src')) {
    function post_img_src(int $postId): string
    {
        return 'media.php?type=post&post_id=' . (int) $postId;
    }
}

if (!function_exists('humplore_ensure_csrf_token')) {
    function humplore_ensure_csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['csrf_token'];
    }
}

if (!function_exists('humplore_require_csrf')) {
    function humplore_require_csrf(?string $token = null): void
    {
        $providedToken = $token;

        if ($providedToken === null) {
            $providedToken = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }

        if (!humplore_csrf_token_is_valid($providedToken)) {
            http_response_code(400);
            die('Ungueltiges CSRF-Token.');
        }
    }
}

if (!function_exists('humplore_csrf_token_is_valid')) {
    function humplore_csrf_token_is_valid(?string $token = null): bool
    {
        $sessionToken = humplore_ensure_csrf_token();
        $providedToken = $token;

        if ($providedToken === null) {
            $providedToken = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        }

        return $providedToken !== '' && hash_equals($sessionToken, $providedToken);
    }
}

if (!function_exists('humplore_table_columns')) {
    function humplore_table_columns(PDO $pdo, string $table): array
    {
        $table = preg_replace('/[^A-Za-z0-9_]/', '', $table);
        if ($table === '') {
            return [];
        }

        $columns = [];
        $stmt = $pdo->query('PRAGMA table_info(' . $table . ')');
        if (!$stmt) {
            return $columns;
        }

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name !== '') {
                $columns[$name] = $row;
            }
        }

        return $columns;
    }
}

if (!function_exists('humplore_ensure_database_schema')) {
    function humplore_ensure_database_schema(PDO $pdo): void
    {
        static $isChecked = false;

        if ($isChecked) {
            return;
        }

        $postColumns = humplore_table_columns($pdo, 'Posts');
        if (!isset($postColumns['source_question_id'])) {
            $pdo->exec('ALTER TABLE Posts ADD COLUMN source_question_id INTEGER DEFAULT NULL');
        }

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS SavedPosts (
              user_id INTEGER NOT NULL,
              post_id INTEGER NOT NULL,
              created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (user_id, post_id)
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Reports (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              reporter_id INTEGER NOT NULL,
              target_type TEXT NOT NULL,
              target_id INTEGER NOT NULL,
              reason TEXT NOT NULL,
              note TEXT NULL,
              status TEXT NOT NULL DEFAULT 'open',
              created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
              UNIQUE (reporter_id, target_type, target_id)
            )
        ");

        $isChecked = true;
    }
}

if (!function_exists('humplore_redirect')) {
    function humplore_redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('humplore_normalize_redirect_target')) {
    function humplore_normalize_redirect_target(string $target): string
    {
        $target = trim($target);

        if ($target === '') {
            return '';
        }

        if (!preg_match('#^(?!//)(?!https?://)/?[A-Za-z0-9_./?&=%-]+$#', $target)) {
            return '';
        }

        return ltrim($target, '/');
    }
}
