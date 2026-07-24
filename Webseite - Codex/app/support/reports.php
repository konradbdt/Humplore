<?php
declare(strict_types=1);

if (!function_exists('humplore_report_reasons')) {
    function humplore_report_reasons(): array
    {
        return [
            'beleidigung_belaestigung' => 'Beleidigung oder Belaestigung',
            'hass_diskriminierung' => 'Hass oder Diskriminierung',
            'spam_werbung' => 'Spam oder Werbung',
            'private_daten' => 'Private Daten',
            'selbstgefaehrdung_akut' => 'Akute Selbstgefaehrdung',
            'sonstiges' => 'Sonstiges',
        ];
    }
}

if (!function_exists('humplore_report_reason_is_valid')) {
    function humplore_report_reason_is_valid(string $reason): bool
    {
        return array_key_exists($reason, humplore_report_reasons());
    }
}

if (!function_exists('humplore_report_target_type_is_valid')) {
    function humplore_report_target_type_is_valid(string $targetType): bool
    {
        return in_array($targetType, ['question', 'comment'], true);
    }
}

if (!function_exists('humplore_report_target_exists')) {
    function humplore_report_target_exists(PDO $pdo, string $targetType, int $targetId): bool
    {
        if ($targetId <= 0 || !humplore_report_target_type_is_valid($targetType)) {
            return false;
        }

        $table = $targetType === 'question' ? 'Questions' : 'Comments';
        $stmt = $pdo->prepare("SELECT 1 FROM {$table} WHERE id = ?");
        $stmt->execute([$targetId]);

        return (bool) $stmt->fetchColumn();
    }
}

if (!function_exists('humplore_report_note_normalize')) {
    function humplore_report_note_normalize(?string $note): ?string
    {
        $note = trim((string) $note);
        if ($note === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($note, 0, 500);
        }

        return substr($note, 0, 500);
    }
}

if (!function_exists('humplore_bulk_reported_targets')) {
    function humplore_bulk_reported_targets(PDO $pdo, int $reporterId, string $targetType, array $targetIds): array
    {
        if ($reporterId <= 0 || !humplore_report_target_type_is_valid($targetType)) {
            return [];
        }

        $targetIds = array_values(array_unique(array_filter(array_map('intval', $targetIds), static function (int $id): bool {
            return $id > 0;
        })));
        if ($targetIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($targetIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT target_id FROM Reports WHERE reporter_id = ? AND target_type = ? AND target_id IN ($placeholders)"
        );
        $stmt->execute(array_merge([$reporterId, $targetType], $targetIds));

        $reported = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $reported[(int) $row['target_id']] = true;
        }

        return $reported;
    }
}

if (!function_exists('humplore_apply_report_state')) {
    function humplore_apply_report_state(array $items, array $reportedById): array
    {
        foreach ($items as &$item) {
            $id = (int) ($item['id'] ?? 0);
            $item['is_reported'] = $id > 0 && !empty($reportedById[$id]);
        }
        unset($item);

        return $items;
    }
}

if (!function_exists('humplore_apply_comment_report_state_map')) {
    function humplore_apply_comment_report_state_map(array $commentsByPost, array $reportedCommentIds): array
    {
        foreach ($commentsByPost as &$comments) {
            if (!is_array($comments)) {
                continue;
            }

            $comments = humplore_apply_report_state($comments, $reportedCommentIds);
        }
        unset($comments);

        return $commentsByPost;
    }
}
