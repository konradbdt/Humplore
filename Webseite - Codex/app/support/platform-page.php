<?php
declare(strict_types=1);

if (!function_exists('humplore_platform_feed_seed')) {
    function humplore_platform_feed_seed(): string
    {
        if (empty($_SESSION['feed_seed'])) {
            $_SESSION['feed_seed'] = bin2hex(random_bytes(8));
        }

        return (string) $_SESSION['feed_seed'];
    }
}

if (!function_exists('humplore_platform_page_state')) {
    function humplore_platform_page_state(array $query): array
    {
        $perPage = 12;
        $page = isset($query['page']) ? max(1, (int) $query['page']) : 1;
        $sort = (string) ($query['sort'] ?? 'discover');
        if (!in_array($sort, ['discover', 'latest', 'popular'], true)) {
            $sort = 'discover';
        }

        $topicCategories = humplore_platform_filter_values($query, 'topic_cat');
        $topics = humplore_platform_filter_values($query, 'topic');
        $categories = humplore_platform_filter_values($query, 'cat');
        $profileCities = humplore_platform_filter_values($query, 'profile_city');
        $profileLanguages = humplore_platform_profile_language_values($query);

        $topicCategory = (string) ($topicCategories[0] ?? '');
        $topic = (string) ($topics[0] ?? '');
        $category = (string) ($categories[0] ?? '');
        $profileCity = (string) ($profileCities[0] ?? '');
        $profileLanguage = (string) ($profileLanguages[0] ?? '');
        $hasProfileFilters = $profileCities !== [] || $profileLanguages !== [];

        return [
            'perPage' => $perPage,
            'page' => $page,
            'offset' => ($page - 1) * $perPage,
            'mode' => isset($query['mode']) && $query['mode'] === 'following' ? 'following' : 'discover',
            'searchQuery' => trim((string) ($query['q'] ?? '')),
            'hasSearch' => array_key_exists('q', $query),
            'sort' => $sort,
            'filters' => [
                'topicCategory' => $topicCategory,
                'topicCategories' => $topicCategories,
                'topic' => $topic,
                'topics' => $topics,
                'category' => $category,
                'categories' => $categories,
                'profileCity' => $profileCity,
                'profileCities' => $profileCities,
                'profileLanguage' => $profileLanguage,
                'profileLanguages' => $profileLanguages,
            ],
            'hasProfileFilters' => $hasProfileFilters,
            'hasFilters' => $topicCategories !== [] || $topics !== [] || $categories !== [] || $hasProfileFilters,
        ];
    }
}

if (!function_exists('humplore_platform_raw_query_values')) {
    function humplore_platform_raw_query_values(string $key): array
    {
        $queryString = (string) ($_SERVER['QUERY_STRING'] ?? '');
        if ($queryString === '') {
            return [];
        }

        $values = [];
        foreach (explode('&', $queryString) as $part) {
            if ($part === '') {
                continue;
            }

            $pair = explode('=', $part, 2);
            $rawKey = urldecode(str_replace('+', ' ', (string) ($pair[0] ?? '')));
            if ($rawKey !== $key) {
                continue;
            }

            $values[] = urldecode(str_replace('+', ' ', (string) ($pair[1] ?? '')));
        }

        return $values;
    }
}

if (!function_exists('humplore_platform_filter_value')) {
    function humplore_platform_filter_value(string $value): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value));
        $value = (string) preg_replace('/[\x00-\x1F\x7F]/', '', $value);

        if (txt_len($value) > 80) {
            $value = txt_sub($value, 0, 80);
        }

        return $value;
    }
}

if (!function_exists('humplore_platform_filter_values')) {
    function humplore_platform_filter_values(array $query, string $key, int $limit = 16): array
    {
        $rawValues = humplore_platform_raw_query_values($key);
        if ($rawValues === [] && array_key_exists($key, $query)) {
            $queryValue = $query[$key];
            $rawValues = is_array($queryValue) ? $queryValue : [$queryValue];
        }

        $values = [];
        $seen = [];
        foreach ($rawValues as $rawValue) {
            $value = humplore_platform_filter_value((string) $rawValue);
            if ($value === '') {
                continue;
            }

            $seenKey = txt_lower($value);
            if (isset($seen[$seenKey])) {
                continue;
            }

            $seen[$seenKey] = true;
            $values[] = $value;
            if (count($values) >= $limit) {
                break;
            }
        }

        return $values;
    }
}

if (!function_exists('humplore_platform_filter_list')) {
    function humplore_platform_filter_list(array $filters, string $listKey, string $singleKey = ''): array
    {
        $rawValues = [];
        if (isset($filters[$listKey]) && is_array($filters[$listKey])) {
            $rawValues = $filters[$listKey];
        } elseif ($singleKey !== '' && isset($filters[$singleKey])) {
            $rawValues = [$filters[$singleKey]];
        }

        $values = [];
        $seen = [];
        foreach ($rawValues as $rawValue) {
            $value = humplore_platform_filter_value((string) $rawValue);
            if ($value === '') {
                continue;
            }

            $seenKey = txt_lower($value);
            if (isset($seen[$seenKey])) {
                continue;
            }

            $seen[$seenKey] = true;
            $values[] = $value;
        }

        return $values;
    }
}

if (!function_exists('humplore_platform_profile_language_catalog')) {
    function humplore_platform_profile_language_catalog(): array
    {
        return [
            'Deutsch',
            'Englisch',
            'Franzoesisch',
            'Spanisch',
            'Italienisch',
            'Tuerkisch',
            'Arabisch',
            'Russisch',
            'Polnisch',
            'Ukrainisch',
            'Portugiesisch',
            'Niederlaendisch',
        ];
    }
}

if (!function_exists('humplore_platform_profile_language_values')) {
    function humplore_platform_profile_language_values(array $query): array
    {
        $allowed = [];
        foreach (humplore_platform_profile_language_catalog() as $label) {
            $allowed[txt_lower($label)] = $label;
        }

        $values = [];
        foreach (humplore_platform_filter_values($query, 'profile_language') as $value) {
            $key = txt_lower($value);
            if (isset($allowed[$key])) {
                $values[] = $allowed[$key];
            }
        }

        return $values;
    }
}

if (!function_exists('humplore_platform_profile_language_filter_list')) {
    function humplore_platform_profile_language_filter_list(array $filters): array
    {
        $allowed = [];
        foreach (humplore_platform_profile_language_catalog() as $label) {
            $allowed[txt_lower($label)] = $label;
        }

        $values = [];
        foreach (humplore_platform_filter_list($filters, 'profileLanguages', 'profileLanguage') as $value) {
            $key = txt_lower($value);
            if (isset($allowed[$key])) {
                $values[] = $allowed[$key];
            }
        }

        return $values;
    }
}

if (!function_exists('humplore_platform_add_query_param')) {
    function humplore_platform_add_query_param(array &$params, string $key, $value): void
    {
        if (!isset($params[$key])) {
            $params[$key] = [];
        }

        $values = is_array($value) ? $value : [$value];
        foreach ($values as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            $params[$key][] = $item;
        }
    }
}

if (!function_exists('humplore_platform_build_repeated_query')) {
    function humplore_platform_build_repeated_query(array $params): string
    {
        $parts = [];
        foreach ($params as $key => $values) {
            foreach ((array) $values as $value) {
                $parts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
            }
        }

        return implode('&', $parts);
    }
}

if (!function_exists('humplore_platform_query_string')) {
    function humplore_platform_query_string(array $pageState, array $changes = []): string
    {
        $params = [];

        if (($pageState['mode'] ?? 'discover') === 'following') {
            $params['mode'] = 'following';
        }

        if (($pageState['hasSearch'] ?? false) || ($pageState['searchQuery'] ?? '') !== '') {
            $params['q'] = (string) ($pageState['searchQuery'] ?? '');
        }

        $filters = $pageState['filters'] ?? [];
        foreach (humplore_platform_filter_list($filters, 'topicCategories', 'topicCategory') as $value) {
            humplore_platform_add_query_param($params, 'topic_cat', $value);
        }
        foreach (humplore_platform_filter_list($filters, 'topics', 'topic') as $value) {
            humplore_platform_add_query_param($params, 'topic', $value);
        }
        foreach (humplore_platform_filter_list($filters, 'categories', 'category') as $value) {
            humplore_platform_add_query_param($params, 'cat', $value);
        }
        foreach (humplore_platform_filter_list($filters, 'profileCities', 'profileCity') as $value) {
            humplore_platform_add_query_param($params, 'profile_city', $value);
        }
        foreach (humplore_platform_profile_language_filter_list($filters) as $value) {
            humplore_platform_add_query_param($params, 'profile_language', $value);
        }

        if (($pageState['sort'] ?? 'discover') !== 'discover') {
            $params['sort'] = (string) $pageState['sort'];
        }

        if ((int) ($pageState['page'] ?? 1) > 1) {
            $params['page'] = (int) $pageState['page'];
        }

        foreach ($changes as $key => $value) {
            if ($value === null || $value === '') {
                unset($params[$key]);
                continue;
            }

            $params[$key] = [];
            humplore_platform_add_query_param($params, $key, $value);
        }

        if (array_key_exists('page', $changes) && ($changes['page'] === null || (int) $changes['page'] <= 1)) {
            unset($params['page']);
        }

        return humplore_platform_build_repeated_query($params);
    }
}

if (!function_exists('humplore_platform_url')) {
    function humplore_platform_url(array $pageState, array $changes = []): string
    {
        $query = humplore_platform_query_string($pageState, $changes);

        return 'platform.php' . ($query === '' ? '' : '?' . $query);
    }
}

if (!function_exists('humplore_platform_topic_category_catalog')) {
    function humplore_platform_topic_category_catalog(): array
    {
        return [
            [
                'name' => 'Krankheit',
                'icon' => 'K',
                'keywords' => [
                    'krankheit',
                    'diagnose',
                    'adhs',
                    'ads',
                    'aids',
                    'hiv',
                    'demenz',
                    'depression',
                    'autismus',
                    'ptbs',
                    'ptsd',
                    'krebs',
                    'diabetes',
                    'chronisch',
                    'borderline',
                    'angst',
                    'burnout',
                    'pflegefall',
                ],
            ],
            [
                'name' => 'Religion',
                'icon' => 'R',
                'keywords' => [
                    'religion',
                    'glaube',
                    'christ',
                    'christentum',
                    'islam',
                    'muslim',
                    'jude',
                    'judentum',
                    'buddh',
                    'hindu',
                    'kirche',
                    'moschee',
                    'synagoge',
                ],
            ],
            [
                'name' => 'Beruf',
                'icon' => 'B',
                'keywords' => [
                    'beruf',
                    'job',
                    'arbeit',
                    'karriere',
                    'anwalt',
                    'lehrer',
                    'arzt',
                    'pflege',
                    'therapeut',
                    'gruender',
                    'founder',
                    'cofounder',
                    'co-founder',
                    'selbststaendig',
                    'ausbildung',
                ],
            ],
            [
                'name' => 'Herkunft',
                'icon' => 'H',
                'keywords' => [
                    'herkunft',
                    'migration',
                    'migrant',
                    'flucht',
                    'gefluechtet',
                    'heimat',
                    'kultur',
                    'sprache',
                    'land',
                    'ausland',
                    'rassismus',
                ],
            ],
            [
                'name' => 'Alter',
                'icon' => 'A',
                'keywords' => [
                    'alter',
                    'jugend',
                    'teen',
                    'senior',
                    'rente',
                    'kindheit',
                    'erwachsen',
                    'generation',
                    'midlife',
                ],
            ],
            [
                'name' => 'Geschlecht/Identitaet',
                'icon' => 'G',
                'keywords' => [
                    'geschlecht',
                    'identitaet',
                    'identitat',
                    'gender',
                    'trans',
                    'nonbinary',
                    'nichtbinaer',
                    'queer',
                    'lgbt',
                    'lgbtq',
                    'frau',
                    'mann',
                ],
            ],
        ];
    }
}

if (!function_exists('humplore_platform_topic_category_names')) {
    function humplore_platform_topic_category_names(): array
    {
        return array_map(static fn(array $item): string => (string) $item['name'], humplore_platform_topic_category_catalog());
    }
}

if (!function_exists('humplore_platform_topic_category_sql')) {
    function humplore_platform_topic_category_sql(string $topicExpression): string
    {
        $normalized = "LOWER(TRIM(COALESCE($topicExpression, '')))";
        $cases = [];

        foreach (humplore_platform_topic_category_catalog() as $category) {
            $label = str_replace("'", "''", (string) $category['name']);
            $termsByKey = [];
            foreach (array_merge([(string) $category['name']], (array) ($category['keywords'] ?? [])) as $term) {
                $term = txt_lower(trim((string) $term));
                if ($term !== '') {
                    $termsByKey[$term] = $term;
                }
            }

            $conditions = [];
            foreach (array_values($termsByKey) as $term) {
                $term = str_replace("'", "''", $term);
                $conditions[] = "$normalized = '$term'";
                $conditions[] = "$normalized LIKE '%$term%'";
            }

            if ($conditions !== []) {
                $cases[] = 'WHEN ' . implode(' OR ', $conditions) . " THEN '$label'";
            }
        }

        return '(CASE ' . implode(' ', $cases) . " ELSE 'Sonstiges' END)";
    }
}

if (!function_exists('humplore_platform_topic_category_for_topic')) {
    function humplore_platform_topic_category_for_topic(string $topic): string
    {
        $topic = humplore_platform_filter_value($topic);
        $normalizedTopic = txt_lower($topic);
        if ($normalizedTopic === '') {
            return '';
        }

        foreach (humplore_platform_topic_category_catalog() as $category) {
            $termsByKey = [];
            foreach (array_merge([(string) $category['name']], (array) ($category['keywords'] ?? [])) as $term) {
                $normalizedTerm = txt_lower(trim((string) $term));
                if ($normalizedTerm !== '') {
                    $termsByKey[$normalizedTerm] = $normalizedTerm;
                }
            }

            foreach (array_values($termsByKey) as $normalizedTerm) {
                if ($normalizedTerm !== '' && txt_pos($normalizedTopic, $normalizedTerm) !== false) {
                    return (string) $category['name'];
                }
            }
        }

        return 'Sonstiges';
    }
}

if (!function_exists('humplore_platform_values_without')) {
    function humplore_platform_values_without(array $values, string $value): array
    {
        return array_values(array_filter($values, static function ($item) use ($value): bool {
            return strcasecmp((string) $item, $value) !== 0;
        }));
    }
}

if (!function_exists('humplore_platform_values_toggle')) {
    function humplore_platform_values_toggle(array $values, string $value): array
    {
        foreach ($values as $item) {
            if (strcasecmp((string) $item, $value) === 0) {
                return humplore_platform_values_without($values, $value);
            }
        }

        $values[] = $value;

        return $values;
    }
}

if (!function_exists('humplore_platform_load_filter_options')) {
    function humplore_platform_load_filter_options(PDO $pdo): array
    {
        $topicCategories = [];
        $topics = [];
        $categories = [];
        $profileCities = [];
        $profileLanguages = [];

        try {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $stmtTopics = $pdo->query("
                SELECT
                  $topicCategorySql AS name,
                  COUNT(DISTINCT u.id) AS creator_count,
                  COUNT(p.id) AS post_count
                FROM Users u
                LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
                LEFT JOIN Posts p ON p.creator_id = u.id
                WHERE u.is_creator = 1
                  AND TRIM(COALESCE(cd.main_topic, u.main_topic, '')) <> ''
                GROUP BY $topicCategorySql
                ORDER BY post_count DESC, creator_count DESC, name ASC
                LIMIT 24
            ");
            $topicCategories = $stmtTopics ? $stmtTopics->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $topicCategories = [];
        }

        $topicCategoryNames = array_flip(humplore_platform_topic_category_names());
        $hasTopicCategoryContent = [];
        foreach ($topicCategories as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name !== '') {
                $hasTopicCategoryContent[$name] = true;
            }
        }

        foreach (humplore_platform_topic_category_catalog() as $catalogItem) {
            $name = (string) $catalogItem['name'];
            if (isset($hasTopicCategoryContent[$name])) {
                continue;
            }

            $topicCategories[] = [
                'name' => $name,
                'creator_count' => 0,
                'post_count' => 0,
                'icon' => (string) ($catalogItem['icon'] ?? '#'),
            ];
        }

        foreach ($topicCategories as &$topicCategory) {
            $name = (string) ($topicCategory['name'] ?? '');
            if (isset($topicCategoryNames[$name])) {
                foreach (humplore_platform_topic_category_catalog() as $catalogItem) {
                    if ((string) $catalogItem['name'] === $name) {
                        $topicCategory['icon'] = (string) ($catalogItem['icon'] ?? '#');
                        break;
                    }
                }
            } else {
                $topicCategory['icon'] = 'S';
            }
        }
        unset($topicCategory);

        try {
            $categoryRows = [];
            $stmtCategories = $pdo->query("
                SELECT c.name AS name, COUNT(DISTINCT pc.post_id) AS post_count
                FROM Categories c
                LEFT JOIN PostCategories pc ON pc.category_id = c.id
                GROUP BY c.id, c.name
            ");
            if ($stmtCategories) {
                $categoryRows = array_merge($categoryRows, $stmtCategories->fetchAll(PDO::FETCH_ASSOC));
            }

            $stmtPostCategories = $pdo->query("
                SELECT TRIM(category) AS name, COUNT(*) AS post_count
                FROM Posts
                WHERE TRIM(COALESCE(category, '')) <> ''
                GROUP BY LOWER(TRIM(category))
            ");
            if ($stmtPostCategories) {
                $categoryRows = array_merge($categoryRows, $stmtPostCategories->fetchAll(PDO::FETCH_ASSOC));
            }

            $byName = [];
            foreach ($categoryRows as $row) {
                $name = humplore_platform_filter_value((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $key = txt_lower($name);
                if (!isset($byName[$key])) {
                    $byName[$key] = [
                        'name' => $name,
                        'post_count' => 0,
                        'icon' => '#',
                    ];
                }

                $byName[$key]['post_count'] += (int) ($row['post_count'] ?? 0);
            }

            $categories = array_values($byName);
            usort($categories, static function (array $a, array $b): int {
                return ((int) $b['post_count'] <=> (int) $a['post_count'])
                    ?: strcasecmp((string) $a['name'], (string) $b['name']);
            });
            $categories = array_slice($categories, 0, 24);
        } catch (Throwable $e) {
            $categories = [];
        }

        try {
            $stmtCities = $pdo->query("
                SELECT TRIM(ort) AS name, COUNT(DISTINCT user_id) AS creator_count
                FROM CreatorDetails
                WHERE TRIM(COALESCE(ort, '')) <> ''
                GROUP BY LOWER(TRIM(ort))
                ORDER BY creator_count DESC, name ASC
                LIMIT 40
            ");
            $profileCities = $stmtCities ? $stmtCities->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $profileCities = [];
        }

        foreach (humplore_platform_profile_language_catalog() as $languageLabel) {
            $creatorCount = 0;
            try {
                $stmtLanguage = $pdo->prepare("
                    SELECT COUNT(DISTINCT user_id)
                    FROM CreatorDetails
                    WHERE TRIM(COALESCE(sprache, '')) <> ''
                      AND LOWER(COALESCE(sprache, '')) LIKE LOWER(:language_like)
                ");
                $stmtLanguage->bindValue(':language_like', '%' . $languageLabel . '%', PDO::PARAM_STR);
                $stmtLanguage->execute();
                $creatorCount = (int) $stmtLanguage->fetchColumn();
            } catch (Throwable $e) {
                $creatorCount = 0;
            }

            $profileLanguages[] = [
                'name' => $languageLabel,
                'creator_count' => $creatorCount,
            ];
        }

        return [
            'topicCategories' => $topicCategories,
            'topics' => $topics,
            'categories' => $categories,
            'profileCities' => $profileCities,
            'profileLanguages' => $profileLanguages,
        ];
    }
}

if (!function_exists('humplore_platform_should_show_overview')) {
    function humplore_platform_should_show_overview(array $pageState): bool
    {
        $filters = $pageState['filters'] ?? [];

        return ($pageState['mode'] ?? 'discover') === 'discover'
            && trim((string) ($pageState['searchQuery'] ?? '')) === ''
            && humplore_platform_filter_list($filters, 'topicCategories', 'topicCategory') === []
            && humplore_platform_filter_list($filters, 'topics', 'topic') === []
            && humplore_platform_filter_list($filters, 'categories', 'category') === []
            && humplore_platform_filter_list($filters, 'profileCities', 'profileCity') === []
            && humplore_platform_profile_language_filter_list($filters) === [];
    }
}

if (!function_exists('humplore_platform_load_overview')) {
    function humplore_platform_load_overview(PDO $pdo, array $options = []): array
    {
        $groupLimit = max(1, (int) ($options['groupLimit'] ?? 4));
        $postLimit = max(1, (int) ($options['postLimit'] ?? 2));
        $creatorLimit = max(1, (int) ($options['creatorLimit'] ?? 2));

        $topics = humplore_platform_load_overview_topics($pdo, $groupLimit, $postLimit, $creatorLimit);
        $categories = humplore_platform_load_overview_categories($pdo, $groupLimit, $postLimit);

        return [
            'topics' => $topics,
            'categories' => $categories,
        ];
    }
}

if (!function_exists('humplore_platform_load_overview_topics')) {
    function humplore_platform_load_overview_topics(PDO $pdo, int $groupLimit, int $postLimit, int $creatorLimit): array
    {
        try {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $stmt = $pdo->prepare("
                SELECT
                  $topicCategorySql AS name,
                  COUNT(DISTINCT u.id) AS creator_count,
                  COUNT(DISTINCT p.id) AS post_count,
                  MAX(p.created_at) AS latest_post_at
                FROM Users u
                LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
                LEFT JOIN Posts p ON p.creator_id = u.id
                WHERE u.is_creator = 1
                  AND TRIM(COALESCE(cd.main_topic, u.main_topic, '')) <> ''
                GROUP BY $topicCategorySql
                ORDER BY post_count DESC, latest_post_at DESC, creator_count DESC, name ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $groupLimit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }

        $groups = [];
        foreach ($rows as $row) {
            $name = humplore_platform_filter_value((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $groups[] = [
                'name' => $name,
                'creator_count' => (int) ($row['creator_count'] ?? 0),
                'post_count' => (int) ($row['post_count'] ?? 0),
                'topics' => humplore_platform_load_topics_for_category($pdo, $name, 4),
                'posts' => humplore_platform_load_overview_posts($pdo, ['topicCategory' => $name], $postLimit),
                'creators' => humplore_platform_load_overview_creators($pdo, $name, $creatorLimit),
            ];
        }

        return $groups;
    }
}

if (!function_exists('humplore_platform_load_topics_for_category')) {
    function humplore_platform_load_topics_for_category(PDO $pdo, string $topicCategory, int $limit): array
    {
        try {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $stmt = $pdo->prepare("
                SELECT
                  TRIM(COALESCE(cd.main_topic, u.main_topic)) AS name,
                  COUNT(DISTINCT u.id) AS creator_count
                FROM Users u
                LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
                WHERE u.is_creator = 1
                  AND TRIM(COALESCE(cd.main_topic, u.main_topic, '')) <> ''
                  AND LOWER(TRIM($topicCategorySql)) = LOWER(TRIM(:topic_category))
                GROUP BY LOWER(TRIM(COALESCE(cd.main_topic, u.main_topic)))
                ORDER BY creator_count DESC, name ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':topic_category', $topicCategory, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('humplore_platform_load_overview_categories')) {
    function humplore_platform_load_overview_categories(PDO $pdo, int $groupLimit, int $postLimit): array
    {
        try {
            $categoryRows = [];

            $stmtCategories = $pdo->query("
                SELECT c.name AS name, COUNT(DISTINCT pc.post_id) AS post_count, MAX(p.created_at) AS latest_post_at
                FROM Categories c
                LEFT JOIN PostCategories pc ON pc.category_id = c.id
                LEFT JOIN Posts p ON p.id = pc.post_id
                GROUP BY c.id, c.name
            ");
            if ($stmtCategories) {
                $categoryRows = array_merge($categoryRows, $stmtCategories->fetchAll(PDO::FETCH_ASSOC));
            }

            $stmtPostCategories = $pdo->query("
                SELECT TRIM(category) AS name, COUNT(DISTINCT id) AS post_count, MAX(created_at) AS latest_post_at
                FROM Posts
                WHERE TRIM(COALESCE(category, '')) <> ''
                GROUP BY LOWER(TRIM(category))
            ");
            if ($stmtPostCategories) {
                $categoryRows = array_merge($categoryRows, $stmtPostCategories->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (Throwable $e) {
            return [];
        }

        $byName = [];
        foreach ($categoryRows as $row) {
            $name = humplore_platform_filter_value((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $key = txt_lower($name);
            if (!isset($byName[$key])) {
                $byName[$key] = [
                    'name' => $name,
                    'post_count' => 0,
                    'latest_post_at' => null,
                ];
            }

            $byName[$key]['post_count'] += (int) ($row['post_count'] ?? 0);
            $latest = (string) ($row['latest_post_at'] ?? '');
            if ($latest !== '' && ((string) ($byName[$key]['latest_post_at'] ?? '') === '' || $latest > (string) $byName[$key]['latest_post_at'])) {
                $byName[$key]['latest_post_at'] = $latest;
            }
        }

        $groups = array_values($byName);
        usort($groups, static function (array $a, array $b): int {
            return ((int) $b['post_count'] <=> (int) $a['post_count'])
                ?: strcmp((string) ($b['latest_post_at'] ?? ''), (string) ($a['latest_post_at'] ?? ''))
                ?: strcasecmp((string) $a['name'], (string) $b['name']);
        });

        $groups = array_slice($groups, 0, $groupLimit);
        foreach ($groups as &$group) {
            $group['posts'] = humplore_platform_load_overview_posts($pdo, ['category' => (string) $group['name']], $postLimit);
        }
        unset($group);

        return $groups;
    }
}

if (!function_exists('humplore_platform_load_overview_posts')) {
    function humplore_platform_load_overview_posts(PDO $pdo, array $filters, int $limit): array
    {
        $params = [];
        $whereSql = humplore_platform_filter_where($filters, $params, 'overview_post');
        if ($whereSql !== '') {
            $whereSql = ' WHERE ' . $whereSql;
        }

        try {
            $stmt = $pdo->prepare(humplore_platform_posts_select_sql() . "
                $whereSql
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT :limit
            ");
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('humplore_platform_load_overview_creators')) {
    function humplore_platform_load_overview_creators(PDO $pdo, string $topicCategory, int $limit): array
    {
        try {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $stmt = $pdo->prepare("
                SELECT
                  u.id,
                  u.username,
                  u.profile_image,
                  COALESCE(cd.main_topic, u.main_topic) AS main_topic,
                  COUNT(p.id) AS post_count
                FROM Users u
                LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
                LEFT JOIN Posts p ON p.creator_id = u.id
                WHERE u.is_creator = 1
                  AND LOWER(TRIM($topicCategorySql)) = LOWER(TRIM(:topic_category))
                GROUP BY u.id, u.username, u.profile_image, COALESCE(cd.main_topic, u.main_topic)
                ORDER BY post_count DESC, u.username ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':topic_category', $topicCategory, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('humplore_platform_filter_where')) {
    function humplore_platform_filter_where(array $filters, array &$params, string $prefix, string $postAlias = 'p'): string
    {
        $clauses = [];
        $topicCategories = humplore_platform_filter_list($filters, 'topicCategories', 'topicCategory');
        $topics = humplore_platform_filter_list($filters, 'topics', 'topic');
        $categories = humplore_platform_filter_list($filters, 'categories', 'category');
        $profileCities = humplore_platform_filter_list($filters, 'profileCities', 'profileCity');
        $profileLanguages = humplore_platform_profile_language_filter_list($filters);

        if ($topicCategories !== []) {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $orClauses = [];
            foreach ($topicCategories as $idx => $topicCategory) {
                $param = ':' . $prefix . '_topic_category_' . $idx;
                $params[$param] = $topicCategory;
                $orClauses[] = "LOWER(TRIM($topicCategorySql)) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($topics !== []) {
            $orClauses = [];
            foreach ($topics as $idx => $topic) {
                $param = ':' . $prefix . '_topic_' . $idx;
                $params[$param] = $topic;
                $orClauses[] = "LOWER(TRIM(COALESCE(cd.main_topic, u.main_topic, ''))) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($categories !== []) {
            $orClauses = [];
            foreach ($categories as $idx => $category) {
                $param = ':' . $prefix . '_cat_' . $idx;
                $params[$param] = $category;
                $orClauses[] = "(
                LOWER(TRIM(COALESCE($postAlias.category, ''))) = LOWER(TRIM($param))
                OR EXISTS (
                    SELECT 1
                    FROM PostCategories pcf
                    JOIN Categories cf ON cf.id = pcf.category_id
                    WHERE pcf.post_id = $postAlias.id
                      AND LOWER(TRIM(cf.name)) = LOWER(TRIM($param))
                )
            )";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($profileCities !== []) {
            $orClauses = [];
            foreach ($profileCities as $idx => $profileCity) {
                $param = ':' . $prefix . '_profile_city_' . $idx;
                $params[$param] = $profileCity;
                $orClauses[] = "LOWER(TRIM(COALESCE(cd.ort, ''))) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($profileLanguages !== []) {
            $orClauses = [];
            foreach ($profileLanguages as $idx => $profileLanguage) {
                $param = ':' . $prefix . '_profile_language_' . $idx;
                $likeParam = ':' . $prefix . '_profile_language_like_' . $idx;
                $params[$param] = $profileLanguage;
                $params[$likeParam] = '%' . $profileLanguage . '%';
                $orClauses[] = "(
                    LOWER(TRIM(COALESCE(cd.sprache, ''))) = LOWER(TRIM($param))
                    OR LOWER(COALESCE(cd.sprache, '')) LIKE LOWER($likeParam)
                )";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        return implode(' AND ', $clauses);
    }
}

if (!function_exists('humplore_platform_creator_filter_where')) {
    function humplore_platform_creator_filter_where(array $filters, array &$params, string $prefix, bool $includeCategory = false): string
    {
        $clauses = [];
        $topicCategories = humplore_platform_filter_list($filters, 'topicCategories', 'topicCategory');
        $topics = humplore_platform_filter_list($filters, 'topics', 'topic');
        $categories = humplore_platform_filter_list($filters, 'categories', 'category');
        $profileCities = humplore_platform_filter_list($filters, 'profileCities', 'profileCity');
        $profileLanguages = humplore_platform_profile_language_filter_list($filters);

        if ($topicCategories !== []) {
            $topicCategorySql = humplore_platform_topic_category_sql('COALESCE(cd.main_topic, u.main_topic)');
            $orClauses = [];
            foreach ($topicCategories as $idx => $topicCategory) {
                $param = ':' . $prefix . '_topic_category_' . $idx;
                $params[$param] = $topicCategory;
                $orClauses[] = "LOWER(TRIM($topicCategorySql)) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($topics !== []) {
            $orClauses = [];
            foreach ($topics as $idx => $topic) {
                $param = ':' . $prefix . '_topic_' . $idx;
                $params[$param] = $topic;
                $orClauses[] = "LOWER(TRIM(COALESCE(cd.main_topic, u.main_topic, ''))) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($includeCategory && $categories !== []) {
            $orClauses = [];
            foreach ($categories as $idx => $category) {
                $param = ':' . $prefix . '_cat_' . $idx;
                $params[$param] = $category;
                $orClauses[] = "EXISTS (
                    SELECT 1
                    FROM Posts pp
                    WHERE pp.creator_id = u.id
                      AND (
                        LOWER(TRIM(COALESCE(pp.category, ''))) = LOWER(TRIM($param))
                        OR EXISTS (
                            SELECT 1
                            FROM PostCategories ppc
                            JOIN Categories pcc ON pcc.id = ppc.category_id
                            WHERE ppc.post_id = pp.id
                              AND LOWER(TRIM(pcc.name)) = LOWER(TRIM($param))
                        )
                      )
                )";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($profileCities !== []) {
            $orClauses = [];
            foreach ($profileCities as $idx => $profileCity) {
                $param = ':' . $prefix . '_profile_city_' . $idx;
                $params[$param] = $profileCity;
                $orClauses[] = "LOWER(TRIM(COALESCE(cd.ort, ''))) = LOWER(TRIM($param))";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        if ($profileLanguages !== []) {
            $orClauses = [];
            foreach ($profileLanguages as $idx => $profileLanguage) {
                $param = ':' . $prefix . '_profile_language_' . $idx;
                $likeParam = ':' . $prefix . '_profile_language_like_' . $idx;
                $params[$param] = $profileLanguage;
                $params[$likeParam] = '%' . $profileLanguage . '%';
                $orClauses[] = "(
                    LOWER(TRIM(COALESCE(cd.sprache, ''))) = LOWER(TRIM($param))
                    OR LOWER(COALESCE(cd.sprache, '')) LIKE LOWER($likeParam)
                )";
            }
            $clauses[] = '(' . implode(' OR ', $orClauses) . ')';
        }

        return implode(' AND ', $clauses);
    }
}

if (!function_exists('humplore_platform_feed_order_sql')) {
    function humplore_platform_feed_order_sql(string $sort, int $feedSeedInt): string
    {
        if ($sort === 'latest') {
            return 'p.created_at DESC, p.id DESC';
        }

        if ($sort === 'popular') {
            return '(SELECT COUNT(*) FROM Likes l WHERE l.post_id = p.id) DESC, p.created_at DESC, p.id DESC';
        }

        return '((p.id * 1103515245 + ' . $feedSeedInt . ') % 2147483647) ASC, p.id ASC';
    }
}

if (!function_exists('humplore_platform_posts_select_sql')) {
    function humplore_platform_posts_select_sql(): string
    {
        return <<<SQL
SELECT
  p.id,
  p.creator_id,
  p.title,
  p.content,
  p.media_type,
  p.media_image,
  p.media_url,
  p.source_question_id,
  CASE WHEN (p.media_image IS NOT NULL AND length(p.media_image) > 0) OR (p.media_url IS NOT NULL AND p.media_url <> '') THEN 1 ELSE 0 END AS has_media_image,
  p.category,
  p.created_at,
  p.is_paid,
  p.price_cents,
  u.username,
  u.profile_image,
  CASE WHEN u.profile_image IS NULL OR u.profile_image = '' OR u.profile_image = 'default_profile.png' THEN 0 ELSE 1 END AS has_profile_image,
  COALESCE(cd.main_topic, u.main_topic) AS creator_main_topic,
  (SELECT GROUP_CONCAT(c.name, ' · ')
   FROM PostCategories pc
   JOIN Categories c ON c.id = pc.category_id
   WHERE pc.post_id = p.id) AS cat_list
FROM Posts p
JOIN Users u ON p.creator_id = u.id
LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
SQL;
    }
}

if (!function_exists('humplore_platform_questions_select_sql')) {
    function humplore_platform_questions_select_sql(): string
    {
        return <<<SQL
SELECT
  q.id,
  q.question_text,
  q.answer_text,
  q.created_at,
  cu.id AS creator_id,
  cu.username AS creator_name,
  COALESCE(cd.main_topic, cu.main_topic) AS creator_main_topic,
  au.username AS author_name
FROM Questions q
JOIN Users cu ON cu.id = q.creator_id
LEFT JOIN CreatorDetails cd ON cd.user_id = cu.id
LEFT JOIN Users au ON au.id = q.author_id
WHERE TRIM(COALESCE(q.question_text, '')) <> ''
SQL;
    }
}

if (!function_exists('humplore_platform_handle_comment_submission')) {
    function humplore_platform_handle_comment_submission(
        PDO $pdo,
        int $userId,
        array $postData,
        array $queryParams,
        string $requestUri
    ): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        if (!isset($postData['comment_text'], $postData['post_id'])) {
            return;
        }

        humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));

        $postId = (int) $postData['post_id'];
        $commentText = trim((string) $postData['comment_text']);

        if ($commentText !== '' && $postId > 0) {
            $stmtInsert = $pdo->prepare('INSERT INTO Comments (post_id, user_id, comment_text) VALUES (?, ?, ?)');
            $stmtInsert->execute([$postId, $userId, $commentText]);
        }

        $redirectPath = (string) strtok($requestUri, '?');
        if ($redirectPath === '') {
            $redirectPath = 'platform.php';
        }

        if ($queryParams !== []) {
            $redirectPath .= '?' . http_build_query($queryParams);
        }

        humplore_redirect($redirectPath);
    }
}

if (!function_exists('humplore_platform_load_search_results')) {
    function humplore_platform_load_search_results(PDO $pdo, bool $hasSearch, string $searchQuery, array $filters = [], string $sort = 'discover'): array
    {
        if (!$hasSearch) {
            return humplore_search_empty_result();
        }

        return humplore_search_discovery($pdo, $searchQuery, [
            'filters' => $filters,
            'sort' => $sort,
        ]);
    }
}

if (!function_exists('humplore_platform_load_questions')) {
    function humplore_platform_load_questions(PDO $pdo): array
    {
        $questionsData = [
            'randomQuestions' => [],
            'allQuestions' => [],
        ];

        try {
            $questionsData['randomQuestions'] = $pdo
                ->query(humplore_platform_questions_select_sql() . " ORDER BY RANDOM() LIMIT 5")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $questionsData['randomQuestions'] = [];
        }

        try {
            $questionsData['allQuestions'] = $pdo
                ->query(humplore_platform_questions_select_sql() . " ORDER BY q.created_at DESC, q.id DESC")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $questionsData['allQuestions'] = [];
        }

        return $questionsData;
    }
}

if (!function_exists('humplore_platform_load_feed')) {
    function humplore_platform_load_feed(
        PDO $pdo,
        int $userId,
        string $mode,
        int $perPage,
        int $offset,
        int $feedSeedInt,
        array $filters = [],
        string $sort = 'discover'
    ): array {
        $params = [];
        $clauses = [];
        if ($mode !== 'discover') {
            $clauses[] = "(p.creator_id = :me OR p.creator_id IN (SELECT followed_id FROM Follows WHERE follower_id = :me2))";
            $params[':me'] = $userId;
            $params[':me2'] = $userId;
        }

        $filterWhere = humplore_platform_filter_where($filters, $params, 'feed');
        if ($filterWhere !== '') {
            $clauses[] = $filterWhere;
        }

        $whereSql = $clauses === [] ? '' : ' WHERE ' . implode(' AND ', $clauses);
        $orderSql = humplore_platform_feed_order_sql($sort, $feedSeedInt);

        $stmt = $pdo->prepare(humplore_platform_posts_select_sql() . "
            $whereSql
            ORDER BY $orderSql
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $explorePosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtCount = $pdo->prepare("
            SELECT COUNT(*)
            FROM Posts p
            JOIN Users u ON p.creator_id = u.id
            LEFT JOIN CreatorDetails cd ON cd.user_id = u.id
            $whereSql
        ");
        foreach ($params as $param => $value) {
            $stmtCount->bindValue($param, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmtCount->execute();
        $total = (int) $stmtCount->fetchColumn();

        return [
            'explorePosts' => $explorePosts,
            'total' => $total,
            'totalPages' => max(1, (int) ceil($total / $perPage)),
        ];
    }
}

