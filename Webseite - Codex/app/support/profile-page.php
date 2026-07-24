<?php
declare(strict_types=1);

if (!function_exists('humplore_profile_sidebar_categories')) {
    function humplore_profile_sidebar_categories(): array
    {
        return [
            ['name' => 'Allgemein', 'icon' => '#'],
            ['name' => 'Alltag', 'icon' => 'A'],
            ['name' => 'Familie', 'icon' => 'F'],
            ['name' => 'Schule & Studium', 'icon' => 'S'],
            ['name' => 'Hobbys', 'icon' => 'H'],
            ['name' => 'Liebe', 'icon' => 'L'],
            ['name' => 'Sport', 'icon' => 'S'],
            ['name' => 'Erfahrung', 'icon' => 'E'],
            ['name' => 'Frage', 'icon' => '?'],
        ];

        return [
            ['name' => 'Religion', 'icon' => '✝'],
            ['name' => 'Umstände', 'icon' => '⚙️'],
            ['name' => 'Hobbys', 'icon' => '🎨'],
            ['name' => 'Berufe', 'icon' => '💼'],
            ['name' => 'Erfahrung', 'icon' => '📚'],
            ['name' => 'Krankheit', 'icon' => '🩺'],
            ['name' => 'Familie', 'icon' => '👨‍👩‍👧'],
            ['name' => 'Schule & Studium', 'icon' => '🎓'],
            ['name' => 'Sport', 'icon' => '⚽'],
        ];
    }
}

if (!function_exists('humplore_profile_resolve_user_id')) {
    function humplore_profile_resolve_user_id(array $query, int $viewerUserId): int
    {
        if (isset($query['user_id'])) {
            return (int) $query['user_id'];
        }

        if (isset($query['creator_id'])) {
            return (int) $query['creator_id'];
        }

        if (isset($query['id'])) {
            return (int) $query['id'];
        }

        return $viewerUserId;
    }
}

if (!function_exists('humplore_profile_active_category_slug')) {
    function humplore_profile_active_category_slug(array $query): string
    {
        return isset($query['cat']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $query['cat']) : '';
    }
}

if (!function_exists('humplore_profile_load_context')) {
    function humplore_profile_load_context(PDO $pdo, int $profileUserId, int $viewerUserId): array
    {
        $stmtUser = $pdo->prepare("
            SELECT
              id,
              username,
              is_creator,
              CASE WHEN profile_image IS NULL OR profile_image = '' OR profile_image = 'default_profile.png' THEN 0 ELSE 1 END AS has_profile_image
            FROM Users
            WHERE id = ?
        ");
        $stmtUser->execute([$profileUserId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            throw new RuntimeException('Benutzer nicht gefunden');
        }

        $isOwnProfile = $profileUserId === $viewerUserId;
        $isCreator = ((int) ($user['is_creator'] ?? 0)) === 1;

        $viewerIsCreator = 0;
        try {
            $stmtViewerCreator = $pdo->prepare('SELECT is_creator FROM Users WHERE id = ?');
            $stmtViewerCreator->execute([$viewerUserId]);
            $viewerIsCreator = (int) ($stmtViewerCreator->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            $viewerIsCreator = 0;
        }

        $recommendedCreator = null;
        try {
            $stmtReco = $pdo->prepare("
                SELECT
                  u.id,
                  u.username,
                  COALESCE(cd.main_topic, 'Creator') AS main_topic,
                  (
                    SELECT COUNT(*)
                    FROM Follows f
                    WHERE f.followed_id = u.id
                  ) AS follower_count
                FROM Users u
                LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
                WHERE u.is_creator = 1
                  AND u.id <> ?
                ORDER BY RAND()
                LIMIT 1
            ");
            $stmtReco->execute([$profileUserId]);
            $recommendedCreator = $stmtReco->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            $recommendedCreator = null;
        }

        $isFollowing = false;
        if ($viewerUserId > 0 && !$isOwnProfile) {
            try {
                $stmtFollow = $pdo->prepare('SELECT COUNT(*) FROM Follows WHERE follower_id = ? AND followed_id = ?');
                $stmtFollow->execute([$viewerUserId, $profileUserId]);
                $isFollowing = ((int) $stmtFollow->fetchColumn()) > 0;
            } catch (PDOException $e) {
                $isFollowing = false;
            }
        }

        $stmtPostsCount = $pdo->prepare('SELECT COUNT(*) FROM Posts WHERE creator_id = ?');
        $stmtPostsCount->execute([$profileUserId]);
        $postsCount = (int) $stmtPostsCount->fetchColumn();

        $followerCount = 0;
        $followingCount = 0;
        try {
            $stmtFollower = $pdo->prepare('SELECT COUNT(*) FROM Follows WHERE followed_id = ?');
            $stmtFollower->execute([$profileUserId]);
            $followerCount = (int) $stmtFollower->fetchColumn();

            $stmtFollowing = $pdo->prepare('SELECT COUNT(*) FROM Follows WHERE follower_id = ?');
            $stmtFollowing->execute([$profileUserId]);
            $followingCount = (int) $stmtFollowing->fetchColumn();
        } catch (PDOException $e) {
            $followerCount = 0;
            $followingCount = 0;
        }

        $creatorData = null;
        if ($isCreator) {
            $stmtCreator = $pdo->prepare('SELECT * FROM CreatorDetails WHERE user_id = ?');
            $stmtCreator->execute([$profileUserId]);
            $creatorData = $stmtCreator->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $allCategories = [];
        try {
            $allCategories = $pdo->query('SELECT id, name, slug FROM Categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $allCategories = [];
        }

        return [
            'user' => $user,
            'isOwnProfile' => $isOwnProfile,
            'isCreator' => $isCreator,
            'viewerIsCreator' => $viewerIsCreator,
            'recommendedCreator' => $recommendedCreator,
            'isFollowing' => $isFollowing,
            'postsCount' => $postsCount,
            'followerCount' => $followerCount,
            'followingCount' => $followingCount,
            'data' => $creatorData,
            'allCategories' => $allCategories,
        ];
    }
}

if (!function_exists('humplore_profile_load_questions')) {
    function humplore_profile_load_questions(PDO $pdo, int $profileUserId, bool $isCreator): array
    {
        if (!$isCreator) {
            return [];
        }

        try {
            $stmtQuestions = $pdo->prepare("
                SELECT q.*,
                       COUNT(ql.id) AS like_count,
                       u.username AS author_name
                FROM Questions q
                LEFT JOIN QuestionLikes ql ON q.id = ql.question_id
                JOIN Users u ON q.author_id = u.id
                WHERE q.creator_id = ?
                GROUP BY q.id
                ORDER BY like_count DESC, q.created_at DESC
                LIMIT 20
            ");
            $stmtQuestions->execute([$profileUserId]);

            return $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

if (!function_exists('humplore_profile_view_model')) {
    function humplore_profile_view_model(array $user, bool $isCreator, ?array $creatorData): array
    {
        $viewModel = [
            'profileBio' => 'Noch keine Bio vorhanden',
            'profileTitle' => 'Thema',
            'profileUsername' => '@' . (string) ($user['username'] ?? ''),
            'profileTagline' => '',
            'profileHashtags' => [''],
            'profileLocation' => 'Ort nicht angegeben',
            'profileLanguages' => 'Sprache nicht angegeben',
            'profileExchange' => 'Austausch nicht angegeben',
        ];

        if ($isCreator && !empty($creatorData)) {
            $viewModel['profileBio'] = $creatorData['bio'] ?? $viewModel['profileBio'];
            $viewModel['profileTitle'] = $creatorData['main_topic'] ?? $viewModel['profileTitle'];
            if (!empty($creatorData['ort'])) {
                $viewModel['profileLocation'] = $creatorData['ort'];
            }
            if (!empty($creatorData['sprache'])) {
                $viewModel['profileLanguages'] = $creatorData['sprache'];
            }
            if (!empty($creatorData['austausch'])) {
                $viewModel['profileExchange'] = $creatorData['austausch'];
            }
            if (!empty($creatorData['hashtags'])) {
                $viewModel['profileHashtags'] = array_map('trim', explode(',', (string) $creatorData['hashtags']));
            }
        }

        return $viewModel;
    }
}

if (!function_exists('humplore_profile_load_posts')) {
    function humplore_profile_load_posts(
        PDO $pdo,
        int $profileUserId,
        string $activeCatSlug,
        int $viewerUserId,
        array $query
    ): array {
        $posts = [];
        $postsPerPage = 8;
        $postsPage = isset($query['page']) ? max(1, (int) $query['page']) : 1;
        $postsOffset = ($postsPage - 1) * $postsPerPage;
        $postsTotal = 0;

        try {
            if ($activeCatSlug === '') {
                $stmtCountPosts = $pdo->prepare('SELECT COUNT(*) FROM Posts WHERE creator_id = ?');
                $stmtCountPosts->execute([$profileUserId]);
                $postsTotal = (int) $stmtCountPosts->fetchColumn();

                $stmtPosts = $pdo->prepare("
                    SELECT
                      p.id,
                      p.creator_id,
                      p.title,
                      p.content,
                      p.media_type,
                      p.media_url,
                      p.source_question_id,
                      CASE WHEN p.media_image IS NOT NULL AND length(p.media_image) > 0 THEN 1 ELSE 0 END AS has_media_image,
                      p.category,
                      p.created_at,
                      p.is_paid,
                      p.price_cents,
                      u.username,
                      COALESCE(c.name, p.category) AS cat_list
                    FROM Posts p
                    JOIN Users u ON p.creator_id = u.id
                    LEFT JOIN Categories c ON c.slug = p.category
                    WHERE p.creator_id = :creator_id
                    ORDER BY p.created_at DESC
                    LIMIT :limit OFFSET :offset
                ");
                $stmtPosts->bindValue(':creator_id', $profileUserId, PDO::PARAM_INT);
                $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
                $stmtPosts->bindValue(':offset', $postsOffset, PDO::PARAM_INT);
                $stmtPosts->execute();
            } else {
                $stmtCountPosts = $pdo->prepare('SELECT COUNT(*) FROM Posts WHERE creator_id = ? AND category = ?');
                $stmtCountPosts->execute([$profileUserId, $activeCatSlug]);
                $postsTotal = (int) $stmtCountPosts->fetchColumn();

                $stmtPosts = $pdo->prepare("
                    SELECT
                      p.id,
                      p.creator_id,
                      p.title,
                      p.content,
                      p.media_type,
                      p.media_url,
                      p.source_question_id,
                      CASE WHEN p.media_image IS NOT NULL AND length(p.media_image) > 0 THEN 1 ELSE 0 END AS has_media_image,
                      p.category,
                      p.created_at,
                      p.is_paid,
                      p.price_cents,
                      u.username,
                      COALESCE(c.name, p.category) AS cat_list
                    FROM Posts p
                    JOIN Users u ON p.creator_id = u.id
                    LEFT JOIN Categories c ON c.slug = p.category
                    WHERE p.creator_id = :creator_id
                      AND p.category = :category
                    ORDER BY p.created_at DESC
                    LIMIT :limit OFFSET :offset
                ");
                $stmtPosts->bindValue(':creator_id', $profileUserId, PDO::PARAM_INT);
                $stmtPosts->bindValue(':category', $activeCatSlug, PDO::PARAM_STR);
                $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
                $stmtPosts->bindValue(':offset', $postsOffset, PDO::PARAM_INT);
                $stmtPosts->execute();
            }

            $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $posts = [];
            $postsTotal = 0;
        }

        $postIds = array_values(array_filter(array_map(static function ($post) {
            return (int) ($post['id'] ?? 0);
        }, $posts), static function ($id) {
            return $id > 0;
        }));

        [$likeCountsByPost, $likedByViewer] = getBulkLikeInfo($pdo, $postIds, $viewerUserId);
        $commentsByPost = getBulkComments($pdo, $postIds);
        $commentIds = [];
        foreach ($commentsByPost as $comments) {
            foreach ($comments as $comment) {
                $commentId = (int) ($comment['id'] ?? 0);
                if ($commentId > 0) {
                    $commentIds[] = $commentId;
                }
            }
        }
        $reportedComments = humplore_bulk_reported_targets($pdo, $viewerUserId, 'comment', $commentIds);
        $commentsByPost = humplore_apply_comment_report_state_map($commentsByPost, $reportedComments);
        $savedByViewer = getBulkSavedPostInfo($pdo, $postIds, $viewerUserId);

        return [
            'posts' => $posts,
            'postsPerPage' => $postsPerPage,
            'postsPage' => $postsPage,
            'postsOffset' => $postsOffset,
            'postsTotal' => $postsTotal,
            'postsTotalPages' => max(1, (int) ceil($postsTotal / $postsPerPage)),
            'likeCountsByPost' => $likeCountsByPost,
            'likedByViewer' => $likedByViewer,
            'commentsByPost' => $commentsByPost,
            'savedByViewer' => $savedByViewer,
        ];
    }
}

if (!function_exists('humplore_profile_share_link')) {
    function humplore_profile_share_link(array $server, int $profileUserId): string
    {
        return humplore_absolute_url('profile.php?user_id=' . $profileUserId, $server);
    }
}
