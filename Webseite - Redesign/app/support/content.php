<?php
declare(strict_types=1);

if (!function_exists('htime')) {
    function htime(string $ts): string
    {
        $timestamp = strtotime($ts);
        if ($timestamp === false) {
            return e($ts);
        }

        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'gerade eben';
        }

        $units = [
            31536000 => 'Jahr',
            2592000 => 'Monat',
            604800 => 'Woche',
            86400 => 'Tag',
            3600 => 'Stunde',
            60 => 'Minute',
        ];

        foreach ($units as $seconds => $label) {
            $value = (int) floor($diff / $seconds);
            if ($value >= 1) {
                return $value . ' ' . $label . ($value === 1 ? '' : 'n');
            }
        }

        return date('d.m.Y H:i', $timestamp);
    }
}

if (!function_exists('getLikeInfo')) {
    function getLikeInfo(PDO $pdo, int $postId, int $userId): array
    {
        $stmtLikes = $pdo->prepare('SELECT COUNT(*) AS c FROM Likes WHERE post_id = ?');
        $stmtLikes->execute([$postId]);
        $likeCount = (int) ($stmtLikes->fetch()['c'] ?? 0);

        $stmtUser = $pdo->prepare('SELECT COUNT(*) AS c FROM Likes WHERE post_id = ? AND user_id = ?');
        $stmtUser->execute([$postId, $userId]);
        $hasLiked = ((int) ($stmtUser->fetch()['c'] ?? 0)) > 0;

        return [$likeCount, $hasLiked];
    }
}

if (!function_exists('getComments')) {
    function getComments(PDO $pdo, int $postId): array
    {
        $stmt = $pdo->prepare(
            'SELECT c.*, u.username
             FROM Comments c
             JOIN Users u ON u.id = c.user_id
             WHERE c.post_id = ?
             ORDER BY c.created_at DESC'
        );
        $stmt->execute([$postId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('getBulkLikeInfo')) {
    function getBulkLikeInfo(PDO $pdo, array $postIds, int $userId): array
    {
        $counts = [];
        $liked = [];
        if ($postIds === []) {
            return [$counts, $liked];
        }

        $postIds = array_values(array_unique(array_map('intval', $postIds)));
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $stmtCounts = $pdo->prepare(
            "SELECT post_id, COUNT(*) AS c FROM Likes WHERE post_id IN ($placeholders) GROUP BY post_id"
        );
        $stmtCounts->execute($postIds);
        foreach ($stmtCounts->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[(int) $row['post_id']] = (int) $row['c'];
        }

        if ($userId > 0) {
            $stmtLiked = $pdo->prepare(
                "SELECT post_id FROM Likes WHERE user_id = ? AND post_id IN ($placeholders)"
            );
            $stmtLiked->execute(array_merge([$userId], $postIds));
            foreach ($stmtLiked->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $liked[(int) $row['post_id']] = true;
            }
        }

        return [$counts, $liked];
    }
}

if (!function_exists('getBulkComments')) {
    function getBulkComments(PDO $pdo, array $postIds): array
    {
        $byPost = [];
        if ($postIds === []) {
            return $byPost;
        }

        $postIds = array_values(array_unique(array_map('intval', $postIds)));
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));

        $stmt = $pdo->prepare(
            "SELECT c.*, u.username
             FROM Comments c
             JOIN Users u ON u.id = c.user_id
             WHERE c.post_id IN ($placeholders)
             ORDER BY c.created_at DESC"
        );
        $stmt->execute($postIds);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $postId = (int) $row['post_id'];
            $byPost[$postId] ??= [];
            $byPost[$postId][] = $row;
        }

        return $byPost;
    }
}

if (!function_exists('getBulkSavedPostInfo')) {
    function getBulkSavedPostInfo(PDO $pdo, array $postIds, int $userId): array
    {
        $saved = [];
        if ($postIds === [] || $userId <= 0) {
            return $saved;
        }

        $postIds = array_values(array_unique(array_filter(array_map('intval', $postIds), static function (int $id): bool {
            return $id > 0;
        })));
        if ($postIds === []) {
            return $saved;
        }

        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        $stmtSaved = $pdo->prepare(
            "SELECT post_id FROM SavedPosts WHERE user_id = ? AND post_id IN ($placeholders)"
        );
        $stmtSaved->execute(array_merge([$userId], $postIds));

        foreach ($stmtSaved->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $saved[(int) $row['post_id']] = true;
        }

        return $saved;
    }
}

if (!function_exists('humplore_post_has_access')) {
    function humplore_post_has_access(array $post, int $viewerId): bool
    {
        if (empty($post['is_paid']) || (int) $post['is_paid'] === 0) {
            return true;
        }

        if ((int) ($post['creator_id'] ?? 0) === $viewerId) {
            return true;
        }

        return false;
    }
}

if (!function_exists('hasAccess')) {
    function hasAccess(array $post, int $viewerId): bool
    {
        return humplore_post_has_access($post, $viewerId);
    }
}

if (!function_exists('post_has_access')) {
    function post_has_access(array $post, int $viewerId): bool
    {
        return humplore_post_has_access($post, $viewerId);
    }
}

if (!function_exists('formatEuroCents')) {
    function formatEuroCents($cents): string
    {
        if ($cents === null) {
            return '';
        }

        return number_format(((int) $cents) / 100, 2, ',', '.') . ' €';
    }
}

if (!function_exists('parse_paragraphs')) {
    function parse_paragraphs(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = (string) preg_replace("/[ \t]+$/m", '', $text);
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        $blocks = preg_split("/\n\s*\n+/", $text) ?: [];
        $paragraphs = [];

        foreach ($blocks as $block) {
            $block = trim((string) preg_replace("/\n+/", ' ', $block));
            $block = (string) preg_replace("/\s{2,}/", ' ', $block);
            if ($block !== '') {
                $paragraphs[] = $block;
            }
        }

        $merged = [];
        foreach ($paragraphs as $paragraph) {
            if ($merged === []) {
                $merged[] = $paragraph;
                continue;
            }

            $previous = $merged[array_key_last($merged)];
            $previousEnd = mb_substr(rtrim($previous), -1);
            $nextStart = mb_substr(ltrim($paragraph), 0, 1);

            $previousHasSentenceEnd = (bool) preg_match('/[.!?…:]$/u', $previousEnd);
            $nextLooksContinuation = (bool) preg_match('/[0-9a-zäöüß]/u', $nextStart);

            if (!$previousHasSentenceEnd && $nextLooksContinuation) {
                $merged[array_key_last($merged)] = rtrim($previous) . ' ' . ltrim($paragraph);
            } else {
                $merged[] = $paragraph;
            }
        }

        return $merged;
    }
}

if (!function_exists('category_chip_style')) {
    function category_chip_style(string $slug): string
    {
        $slug = trim(strtolower($slug));
        if ($slug === '') {
            return '';
        }

        $hue = (int) (sprintf('%u', crc32($slug)) % 360);

        return sprintf(
            'background:hsla(%1$d, 70%%, 94%%, 1);border-color:hsla(%1$d, 45%%, 78%%, 1);color:hsla(%1$d, 45%%, 28%%, 1);',
            $hue
        );
    }
}

if (!function_exists('txt_len')) {
    function txt_len(string $text): int
    {
        return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
    }
}

if (!function_exists('txt_sub')) {
    function txt_sub(string $text, int $start, $length = null): string
    {
        if (function_exists('mb_substr')) {
            return $length === null ? mb_substr($text, $start) : mb_substr($text, $start, $length);
        }

        return $length === null ? substr($text, $start) : substr($text, $start, $length);
    }
}

if (!function_exists('txt_rpos')) {
    function txt_rpos(string $haystack, string $needle)
    {
        return function_exists('mb_strrpos') ? mb_strrpos($haystack, $needle) : strrpos($haystack, $needle);
    }
}

if (!function_exists('txt_lower')) {
    function txt_lower(string $text): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
    }
}

if (!function_exists('txt_pos')) {
    function txt_pos(string $haystack, string $needle)
    {
        return function_exists('mb_strpos') ? mb_strpos($haystack, $needle) : strpos($haystack, $needle);
    }
}

if (!function_exists('smart_split')) {
    function smart_split(string $text, int $limit): array
    {
        $length = txt_len($text);
        if ($length <= $limit) {
            return [$text, ''];
        }

        $head = txt_sub($text, 0, $limit);
        $window = txt_sub($head, max(0, txt_len($head) - 120));
        $paragraphPos = txt_rpos($window, "\n\n");
        if ($paragraphPos !== false) {
            $cut = (txt_len($head) - txt_len($window)) + $paragraphPos;
            return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
        }

        foreach (['. ', '! ', '? '] as $needle) {
            $sentencePos = txt_rpos($head, $needle);
            if ($sentencePos !== false && $sentencePos > $limit * 0.6) {
                $cut = $sentencePos + txt_len($needle);
                return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
            }
        }

        $spacePos = txt_rpos($head, ' ');
        if ($spacePos !== false && $spacePos > $limit * 0.6) {
            $cut = $spacePos + 1;
            return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
        }

        return [trim($head), trim(txt_sub($text, $limit))];
    }
}
