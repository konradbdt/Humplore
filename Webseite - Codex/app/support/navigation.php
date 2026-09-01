<?php
declare(strict_types=1);

if (!function_exists('humplore_explore_route')) {
    function humplore_explore_route(): string
    {
        $root = dirname(__DIR__, 2);

        if (is_file($root . '/explore.php')) {
            return 'explore.php';
        }

        return 'platform.php';
    }
}

if (!function_exists('humplore_news_route')) {
    function humplore_news_route(): string
    {
        $root = dirname(__DIR__, 2);

        if (is_file($root . '/news.php')) {
            return 'news.php';
        }

        if (is_file($root . '/new.php')) {
            return 'new.php';
        }

        return 'news.php';
    }
}

if (!function_exists('humplore_nav_routes')) {
    function humplore_nav_routes(?PDO $pdo = null): array
    {
        $userId = humplore_current_user_id();

        return [
            'explore' => humplore_explore_route(),
            'saved' => 'gemerkt.php',
            'post' => 'posten.php',
            'news' => humplore_news_route(),
            'profile' => $userId > 0 ? 'profile.php?user_id=' . $userId : 'login.php',
        ];
    }
}

if (!function_exists('humplore_nav_active_class')) {
    function humplore_nav_active_class(string $active, string $key): string
    {
        return $active === $key ? 'is-active' : '';
    }
}

if (!function_exists('humplore_show_creator_nav')) {
    function humplore_show_creator_nav(?PDO $pdo = null): bool
    {
        return humplore_current_user_is_creator($pdo);
    }
}
