<?php
declare(strict_types=1);

if (!function_exists('humplore_search_empty_result')) {
    function humplore_search_empty_result(): array
    {
        return [
            'resultsProfiles' => [],
            'resultsPosts' => [],
            'countProfiles' => 0,
            'countPosts' => 0,
            'totalFound' => 0,
            'suggestions' => [],
            'relatedTerms' => [],
            'usedFuzzy' => false,
        ];
    }
}

if (!function_exists('humplore_search_normalize')) {
    function humplore_search_normalize(string $value): string
    {
        $value = txt_lower(trim($value));
        $value = (string) preg_replace('/[^\p{L}\p{N}@._-]+/u', ' ', $value);
        $value = (string) preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }
}

if (!function_exists('humplore_search_tokens')) {
    function humplore_search_tokens(string $value): array
    {
        $normalized = humplore_search_normalize($value);
        if ($normalized === '') {
            return [];
        }

        $tokens = preg_split('/\s+/u', $normalized) ?: [];
        $tokens = array_filter($tokens, static fn(string $token): bool => txt_len($token) >= 2);

        return array_values(array_unique($tokens));
    }
}

if (!function_exists('humplore_search_matching_terms')) {
    function humplore_search_matching_terms(array $values, array $terms): array
    {
        $haystack = txt_lower(implode("\n", array_map('strval', $values)));
        $matches = [];
        foreach (array_values(array_unique(array_filter(array_map('trim', $terms)))) as $term) {
            if ($term !== '' && txt_pos($haystack, txt_lower($term)) !== false) {
                $matches[] = $term;
            }
        }

        usort($matches, static fn(string $a, string $b): int => txt_len($b) <=> txt_len($a));
        return $matches;
    }
}

if (!function_exists('humplore_search_highlight')) {
    function humplore_search_highlight(string $value, array $terms): string
    {
        $literalTerms = [];
        foreach ($terms as $term) {
            $literalTerm = trim((string) $term);
            if ($literalTerm !== '') {
                $literalTerms[$literalTerm] = true;
            }
        }
        if ($literalTerms === []) {
            return e($value);
        }

        $alternatives = array_map(
            static fn(string $term): string => preg_quote($term, '/'),
            array_keys($literalTerms)
        );
        usort($alternatives, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
        $pattern = '/(' . implode('|', $alternatives) . ')/iu';
        $parts = preg_split($pattern, $value, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) {
            return e($value);
        }

        $highlighted = '';
        foreach ($parts as $index => $part) {
            $escapedPart = e($part);
            $highlighted .= $index % 2 === 1
                ? '<mark class="search-highlight">' . $escapedPart . '</mark>'
                : $escapedPart;
        }

        return $highlighted;
    }
}

if (!function_exists('humplore_search_annotate_results')) {
    function humplore_search_annotate_results(array $rows, string $query, array $relatedTerms, array $fields): array
    {
        foreach ($rows as &$row) {
            $values = [];
            foreach ($fields as $field) {
                $values[] = (string) ($row[$field] ?? '');
            }
            $literalMatches = humplore_search_matching_terms($values, [$query]);
            $relatedMatches = $literalMatches === []
                ? humplore_search_matching_terms($values, $relatedTerms)
                : [];
            $row['search_match_terms'] = $literalMatches !== [] ? $literalMatches : $relatedMatches;
            $row['search_related_only'] = $literalMatches === [] && $relatedMatches !== [];
        }
        unset($row);

        return $rows;
    }
}

if (!function_exists('humplore_search_build_where')) {
    function humplore_search_build_where(array $terms, array &$params, array $filters = [], string $prefix = 'q'): string
    {
        $clauses = [];

        foreach (array_values(array_unique(array_filter(array_map('trim', $terms)))) as $idx => $term) {
            if ($term === '') {
                continue;
            }

            $param = ':' . $prefix . $idx;
            $params[$param] = '%' . $term . '%';
            $clauses[] = "(p.title LIKE $param
                OR p.content LIKE $param
                OR p.category LIKE $param
                OR COALESCE(cd.main_topic, u.main_topic) LIKE $param
                OR u.username LIKE $param
                OR EXISTS (
                    SELECT 1
                    FROM PostCategories pcx
                    JOIN Categories cx ON cx.id = pcx.category_id
                    WHERE pcx.post_id = p.id
                      AND (cx.name LIKE $param OR cx.slug LIKE $param)
                ))";
        }

        $where = $clauses === [] ? '1 = 0' : '(' . implode(' OR ', $clauses) . ')';
        $filterWhere = function_exists('humplore_platform_filter_where')
            ? humplore_platform_filter_where($filters, $params, $prefix . '_filter')
            : '';

        if ($filterWhere !== '') {
            $where .= ' AND ' . $filterWhere;
        }

        return $where;
    }
}

if (!function_exists('humplore_search_direct')) {
    function humplore_search_direct(PDO $pdo, array $terms, int $limitProfiles, int $limitPosts, array $options = []): array
    {
        $terms = array_values(array_unique(array_filter(array_map('trim', $terms))));
        if ($terms === []) {
            return ['profiles' => [], 'posts' => []];
        }

        $filters = is_array($options['filters'] ?? null) ? $options['filters'] : [];
        $sort = (string) ($options['sort'] ?? 'discover');
        $profileClauses = [];
        $profileParams = [];
        foreach ($terms as $idx => $term) {
            $param = ':pq' . $idx;
            $profileParams[$param] = '%' . $term . '%';
            $profileClauses[] = "(u.username LIKE $param OR u.email LIKE $param OR COALESCE(cd.main_topic, u.main_topic) LIKE $param)";
        }

        $profileWhere = '(' . implode(' OR ', $profileClauses) . ')';
        $profileFilterWhere = function_exists('humplore_platform_creator_filter_where')
            ? humplore_platform_creator_filter_where($filters, $profileParams, 'profile', true)
            : '';

        if ($profileFilterWhere !== '') {
            $profileWhere .= ' AND ' . $profileFilterWhere;
        }

        $stmtProfiles = $pdo->prepare("
            SELECT u.id, u.username, u.email, u.profile_image,
                   CASE WHEN u.profile_image IS NULL OR u.profile_image = '' OR u.profile_image = 'default_profile.png' THEN 0 ELSE 1 END AS has_profile_image,
                   COALESCE(cd.main_topic, u.main_topic) AS main_topic,
                   (SELECT COUNT(*) FROM Follows WHERE followed_id = u.id) AS follower_count
            FROM Users u
            LEFT JOIN CreatorDetails cd ON u.id = cd.user_id
            WHERE u.is_creator = 1
              AND $profileWhere
            ORDER BY u.username ASC
            LIMIT :limit_profiles
        ");
        foreach ($profileParams as $param => $value) {
            $stmtProfiles->bindValue($param, $value, PDO::PARAM_STR);
        }
        $stmtProfiles->bindValue(':limit_profiles', $limitProfiles, PDO::PARAM_INT);
        $stmtProfiles->execute();

        $postParams = [];
        $postWhere = humplore_search_build_where($terms, $postParams, $filters, 'sq');
        $postOrder = 'p.created_at DESC, p.id DESC';
        if ($sort === 'popular') {
            $postOrder = '(SELECT COUNT(*) FROM Likes l WHERE l.post_id = p.id) DESC, p.created_at DESC, p.id DESC';
        }
        $stmtPosts = $pdo->prepare(humplore_platform_posts_select_sql() . "
            WHERE $postWhere
            ORDER BY $postOrder
            LIMIT :limit_posts
        ");
        foreach ($postParams as $param => $value) {
            $stmtPosts->bindValue($param, $value, PDO::PARAM_STR);
        }
        $stmtPosts->bindValue(':limit_posts', $limitPosts, PDO::PARAM_INT);
        $stmtPosts->execute();

        return [
            'profiles' => $stmtProfiles->fetchAll(PDO::FETCH_ASSOC),
            'posts' => $stmtPosts->fetchAll(PDO::FETCH_ASSOC),
        ];
    }
}

if (!function_exists('humplore_search_add_candidate')) {
    function humplore_search_add_candidate(array &$candidates, string $label): void
    {
        $label = trim((string) preg_replace('/\s+/u', ' ', $label));
        if ($label === '' || txt_len($label) < 2 || txt_len($label) > 80) {
            return;
        }

        $key = humplore_search_normalize($label);
        if ($key === '' || isset($candidates[$key])) {
            return;
        }

        $candidates[$key] = $label;
    }
}

if (!function_exists('humplore_search_candidates')) {
    function humplore_search_candidates(PDO $pdo, int $limit = 500): array
    {
        $candidates = [];

        $queries = [
            "SELECT username AS a, email AS b, COALESCE(cd.main_topic, u.main_topic) AS c
             FROM Users u
             LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
             WHERE u.is_creator = 1
             LIMIT $limit",
            "SELECT name AS a, slug AS b, NULL AS c FROM Categories ORDER BY name ASC LIMIT $limit",
            "SELECT title AS a, category AS b, substr(content, 1, 220) AS c FROM Posts ORDER BY created_at DESC LIMIT $limit",
        ];

        foreach ($queries as $sql) {
            try {
                foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    foreach (['a', 'b', 'c'] as $field) {
                        $value = (string) ($row[$field] ?? '');
                        humplore_search_add_candidate($candidates, $value);
                        foreach (humplore_search_tokens($value) as $token) {
                            humplore_search_add_candidate($candidates, $token);
                        }
                    }
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return $candidates;
    }
}

if (!function_exists('humplore_search_similarity_score')) {
    function humplore_search_similarity_score(string $query, string $candidate): int
    {
        $queryNorm = humplore_search_normalize($query);
        $candidateNorm = humplore_search_normalize($candidate);
        if ($queryNorm === '' || $candidateNorm === '') {
            return 0;
        }

        if ($queryNorm === $candidateNorm) {
            return 100;
        }

        if (txt_pos($candidateNorm, $queryNorm) !== false || txt_pos($queryNorm, $candidateNorm) !== false) {
            return 92;
        }

        $best = 0;
        $queryTokens = humplore_search_tokens($queryNorm);
        $candidateTokens = humplore_search_tokens($candidateNorm);
        foreach ($queryTokens as $queryToken) {
            foreach ($candidateTokens as $candidateToken) {
                $maxLen = max(txt_len($queryToken), txt_len($candidateToken));
                if ($maxLen > 40) {
                    continue;
                }

                $distance = levenshtein($queryToken, $candidateToken);
                $allowed = $maxLen <= 5 ? 1 : ($maxLen <= 9 ? 2 : 3);
                if ($distance <= $allowed) {
                    $best = max($best, 88 - ($distance * 8));
                    continue;
                }

                similar_text($queryToken, $candidateToken, $percent);
                if ($percent >= 72) {
                    $best = max($best, (int) round($percent));
                }
            }
        }

        return $best;
    }
}

if (!function_exists('humplore_search_related_terms')) {
    function humplore_search_related_terms(PDO $pdo, string $query, int $limit = 6): array
    {
        if (txt_len(humplore_search_normalize($query)) < 3) {
            return [];
        }

        $scored = [];
        foreach (humplore_search_candidates($pdo) as $normalized => $label) {
            $score = humplore_search_similarity_score($query, $label);
            if ($score < 72) {
                continue;
            }

            $scored[] = [
                'label' => $label,
                'normalized' => $normalized,
                'score' => $score,
            ];
        }

        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'] ?: txt_len($a['label']) <=> txt_len($b['label']);
        });

        $terms = [];
        foreach ($scored as $item) {
            if (humplore_search_normalize($item['label']) === humplore_search_normalize($query)) {
                continue;
            }

            $terms[] = $item['label'];
            if (count($terms) >= $limit) {
                break;
            }
        }

        return $terms;
    }
}

if (!function_exists('humplore_search_discovery')) {
    function humplore_search_discovery(PDO $pdo, string $query, array $options = []): array
    {
        $query = trim($query);
        $result = humplore_search_empty_result();
        if ($query === '') {
            return $result;
        }

        $limitProfiles = (int) ($options['limitProfiles'] ?? 24);
        $limitPosts = (int) ($options['limitPosts'] ?? 48);
        $weakThreshold = (int) ($options['weakThreshold'] ?? 3);
        $searchOptions = [
            'filters' => is_array($options['filters'] ?? null) ? $options['filters'] : [],
            'sort' => (string) ($options['sort'] ?? 'discover'),
        ];

        $direct = humplore_search_direct($pdo, [$query], $limitProfiles, $limitPosts, $searchOptions);
        $profiles = $direct['profiles'];
        $posts = $direct['posts'];
        $directTotal = count($profiles) + count($posts);

        $relatedTerms = [];
        $usedFuzzy = false;
        if ($directTotal < $weakThreshold) {
            $relatedTerms = humplore_search_related_terms($pdo, $query);
            if ($relatedTerms !== []) {
                $expanded = humplore_search_direct($pdo, array_merge([$query], $relatedTerms), $limitProfiles, $limitPosts, $searchOptions);
                if ((count($expanded['profiles']) + count($expanded['posts'])) > $directTotal) {
                    $profiles = $expanded['profiles'];
                    $posts = $expanded['posts'];
                    $usedFuzzy = true;
                }
            }
        }

        $result['resultsProfiles'] = humplore_search_annotate_results(
            $profiles,
            $query,
            $relatedTerms,
            ['username', 'email', 'main_topic']
        );
        $result['resultsPosts'] = humplore_search_annotate_results(
            $posts,
            $query,
            $relatedTerms,
            ['title', 'content', 'category', 'cat_list', 'creator_main_topic', 'username']
        );
        $result['countProfiles'] = count($profiles);
        $result['countPosts'] = count($posts);
        $result['totalFound'] = $result['countProfiles'] + $result['countPosts'];
        $result['suggestions'] = array_slice($relatedTerms, 0, 5);
        $result['relatedTerms'] = $relatedTerms;
        $result['usedFuzzy'] = $usedFuzzy;

        return $result;
    }
}
