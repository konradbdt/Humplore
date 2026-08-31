<?php
declare(strict_types=1);

if (!function_exists('humplore_is_logged_in')) {
    function humplore_is_logged_in(): bool
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('humplore_current_user_id')) {
    function humplore_current_user_id(): int
    {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}

if (!function_exists('humplore_sync_creator_session')) {
    function humplore_sync_creator_session(PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT is_creator FROM Users WHERE id = ?');
        $stmt->execute([$userId]);

        $isCreator = (int) ($stmt->fetchColumn() ?? 0);
        $_SESSION['is_creator'] = $isCreator;

        return $isCreator;
    }
}

if (!function_exists('humplore_current_user_is_creator')) {
    function humplore_current_user_is_creator(?PDO $pdo = null): bool
    {
        if (array_key_exists('is_creator', $_SESSION)) {
            return (int) $_SESSION['is_creator'] === 1;
        }

        $userId = humplore_current_user_id();
        if ($userId <= 0 || !$pdo instanceof PDO) {
            return false;
        }

        return humplore_sync_creator_session($pdo, $userId) === 1;
    }
}

if (!function_exists('humplore_require_login')) {
    function humplore_require_login(?string $redirect = null): void
    {
        if (humplore_is_logged_in()) {
            return;
        }

        $target = humplore_normalize_redirect_target($redirect ?? (string) ($_SERVER['REQUEST_URI'] ?? ''));
        if ($target !== '') {
            humplore_redirect('login.php?redirect=' . rawurlencode($target));
        }

        humplore_redirect('login.php');
    }
}

if (!function_exists('humplore_require_json_login')) {
    function humplore_require_json_login(array $payload = ['error' => 'Unauthorized']): int
    {
        $userId = humplore_current_user_id();
        if ($userId > 0) {
            return $userId;
        }

        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('humplore_require_resource_login')) {
    function humplore_require_resource_login(): int
    {
        $userId = humplore_current_user_id();
        if ($userId > 0) {
            return $userId;
        }

        http_response_code(401);
        header('Cache-Control: no-store');
        exit;
    }
}

if (!function_exists('humplore_deny_nonproduction_route')) {
    function humplore_deny_nonproduction_route(): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store');
        echo 'Not found.';
        exit;
    }
}

if (!function_exists('humplore_require_creator')) {
    function humplore_require_creator(?PDO $pdo = null): void
    {
        if (!humplore_is_logged_in()) {
            humplore_redirect('login.php');
        }

        if (humplore_current_user_is_creator($pdo)) {
            return;
        }

        http_response_code(403);
        die('Nur Creator duerfen diesen Bereich nutzen.');
    }
}
