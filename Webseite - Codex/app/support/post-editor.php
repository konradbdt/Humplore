<?php
declare(strict_types=1);

if (!function_exists('humplore_post_editor_header_query')) {
    function humplore_post_editor_header_query(array $query): string
    {
        return trim((string) ($query['q'] ?? ''));
    }
}

if (!function_exists('humplore_post_editor_handle_submission')) {
    function humplore_post_editor_handle_submission(PDO $pdo, int $userId, array $postData, array $files): array
    {
        $result = [
            'error' => '',
            'success' => '',
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $result;
        }

        try {
            $sessionToken = humplore_ensure_csrf_token();
            $providedToken = (string) ($postData['csrf_token'] ?? '');
            if ($providedToken === '' || !hash_equals($sessionToken, $providedToken)) {
                throw new Exception('Sicherheitspruefung fehlgeschlagen. Bitte Seite neu laden.');
            }

            $title = trim((string) ($postData['title'] ?? ''));
            $content = trim((string) ($postData['content'] ?? ''));
            $category = humplore_post_editor_resolve_category(
                (string) ($postData['category'] ?? 'Allgemein'),
                (string) ($postData['category_new'] ?? '')
            );

            $isPaid = isset($postData['is_paid']) ? 1 : 0;
            $priceCents = humplore_post_editor_resolve_price_cents($isPaid === 1, (string) ($postData['price_eur'] ?? ''));

            if ($title === '' || $content === '') {
                throw new Exception('Bitte Titel und Inhalt ausfuellen.');
            }

            $media = humplore_post_editor_resolve_media($files['media'] ?? null);

            $pdo->beginTransaction();
            humplore_post_editor_insert_post($pdo, $userId, $title, $content, [
                'media_type' => $media['media_type'],
                'category' => $category,
                'media_image' => $media['media_image'],
                'is_paid' => $isPaid,
                'price_cents' => $priceCents,
            ]);
            $pdo->commit();

            humplore_redirect('profile.php?success=1');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}

if (!function_exists('humplore_post_editor_insert_post')) {
    function humplore_post_editor_insert_post(PDO $pdo, int $userId, string $title, string $content, array $payload = []): int
    {
        $mediaType = (string) ($payload['media_type'] ?? 'text');
        $category = trim((string) ($payload['category'] ?? 'Allgemein'));
        $mediaImage = $payload['media_image'] ?? null;
        $isPaid = !empty($payload['is_paid']) ? 1 : 0;
        $sourceQuestionId = array_key_exists('source_question_id', $payload) && $payload['source_question_id'] !== null
            ? max(1, (int) $payload['source_question_id'])
            : null;
        $priceCents = array_key_exists('price_cents', $payload) && $payload['price_cents'] !== null
            ? (int) $payload['price_cents']
            : null;

        $stmt = $pdo->prepare("
            INSERT INTO Posts (creator_id, title, content, media_type, category, media_image, is_paid, price_cents, source_question_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $title,
            $content,
            $mediaType,
            $category,
            $mediaImage,
            $isPaid,
            $priceCents,
            $sourceQuestionId,
        ]);

        return (int) $pdo->lastInsertId();
    }
}

if (!function_exists('humplore_post_editor_validate_category_label')) {
    function humplore_post_editor_validate_category_label(string $categoryRaw): string
    {
        $category = trim((string) preg_replace('/\s+/u', ' ', trim($categoryRaw)));
        $length = mb_strlen($category, 'UTF-8');

        if ($length < 2 || $length > 40) {
            throw new Exception('Kategorie muss zwischen 2 und 40 Zeichen lang sein.');
        }

        if (!preg_match('/^[\p{L}\p{N}\s\-_&\/]+$/u', $category)) {
            throw new Exception('Kategorie enthaelt ungueltige Zeichen.');
        }

        return $category;
    }
}

if (!function_exists('humplore_post_editor_resolve_category')) {
    function humplore_post_editor_resolve_category(string $category, string $categoryNewRaw): string
    {
        $categoryNewRaw = trim($categoryNewRaw);
        if ($categoryNewRaw === '') {
            return humplore_post_editor_validate_category_label($category);
        }

        return humplore_post_editor_validate_category_label($categoryNewRaw);
    }
}

if (!function_exists('humplore_post_editor_resolve_price_cents')) {
    function humplore_post_editor_resolve_price_cents(bool $isPaid, string $priceEurRaw): ?int
    {
        if (!$isPaid) {
            return null;
        }

        $priceEurRaw = str_replace(',', '.', trim($priceEurRaw));
        if ($priceEurRaw === '' || !is_numeric($priceEurRaw)) {
            throw new Exception('Bitte gueltigen Preis angeben.');
        }

        $priceValue = (float) $priceEurRaw;
        if ($priceValue < 0.5 || $priceValue > 9999) {
            throw new Exception('Preis muss zwischen 0,50 EUR und 9.999,00 EUR liegen.');
        }

        return (int) round($priceValue * 100);
    }
}

if (!function_exists('humplore_post_editor_resolve_media')) {
    function humplore_post_editor_resolve_media(?array $mediaFile): array
    {
        $result = [
            'media_image' => null,
            'media_type' => 'text',
        ];

        if (!is_array($mediaFile)) {
            return $result;
        }

        $uploadError = (int) ($mediaFile['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            return $result;
        }
        if ($uploadError !== UPLOAD_ERR_OK) {
            throw new Exception('Das Bild konnte nicht vollstaendig hochgeladen werden.');
        }

        $tmpName = (string) ($mediaFile['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName) || !is_file($tmpName)) {
            throw new Exception('Die hochgeladene Datei ist ungueltig.');
        }

        $actualSize = filesize($tmpName);
        if ($actualSize === false || $actualSize <= 0) {
            throw new Exception('Das hochgeladene Bild ist leer oder unlesbar.');
        }
        if ($actualSize > 5 * 1024 * 1024) {
            throw new Exception('Das Bild ist groesser als 5 MB.');
        }

        $detectedMime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected = $finfo ? finfo_file($finfo, $tmpName) : false;
            if ($finfo) {
                finfo_close($finfo);
            }
            if (is_string($detected) && $detected !== '') {
                $detectedMime = $detected;
            }
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $imageInfo = @getimagesize($tmpName);
        $imageMime = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;
        if (!is_string($imageMime) || !in_array($imageMime, $allowedMimeTypes, true)
            || ($detectedMime !== null && $detectedMime !== $imageMime)) {
            throw new Exception('Nur gueltige JPEG-, PNG- und WebP-Bilder sind erlaubt.');
        }

        $binary = file_get_contents($tmpName);
        if (!is_string($binary) || $binary === '') {
            throw new Exception('Das hochgeladene Bild konnte nicht gelesen werden.');
        }

        $result['media_image'] = $binary;
        $result['media_type'] = 'image';

        return $result;
    }
}
