<?php
declare(strict_types=1);

if (!function_exists('humplore_profile_action_state')) {
    function humplore_profile_action_state(): array
    {
        return [
            'ask_error' => '',
            'ask_success' => '',
            'answer_error' => '',
            'answer_success' => '',
        ];
    }
}

if (!function_exists('humplore_profile_handle_actions')) {
    function humplore_profile_handle_actions(
        PDO $pdo,
        array $context,
        array $postData,
        array $files,
        array $server
    ): array {
        $state = humplore_profile_action_state();

        if (($server['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $state;
        }

        $viewerUserId = (int) ($context['viewerUserId'] ?? 0);
        $profileUserId = (int) ($context['profileUserId'] ?? 0);
        $isCreator = !empty($context['isCreator']);
        $isOwnProfile = !empty($context['isOwnProfile']);
        $requestUri = (string) ($server['REQUEST_URI'] ?? ('profile.php?user_id=' . $profileUserId));
        $phpSelf = (string) ($server['PHP_SELF'] ?? 'profile.php');

        if ($viewerUserId <= 0) {
            http_response_code(401);
            die('Bitte zuerst einloggen.');
        }

        if (isset($postData['follow_action'])) {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));
            $action = (string) $postData['follow_action'];

            if ($action === 'follow') {
                try {
                    $stmt = $pdo->prepare('INSERT INTO Follows (follower_id, followed_id) VALUES (?, ?)');
                    $stmt->execute([$viewerUserId, $profileUserId]);
                } catch (PDOException $e) {
                    // Duplicate oder fehlender Unique-Index bleibt absichtlich folgenlos.
                }
            } elseif ($action === 'unfollow') {
                $stmt = $pdo->prepare('DELETE FROM Follows WHERE follower_id = ? AND followed_id = ?');
                $stmt->execute([$viewerUserId, $profileUserId]);
            }

            humplore_redirect($requestUri);
        }

        if (isset($postData['comment_text'], $postData['post_id'])) {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));
            $postId = (int) $postData['post_id'];
            $commentText = trim((string) $postData['comment_text']);

            if ($commentText !== '') {
                $stmtCheckPost = $pdo->prepare('SELECT id FROM Posts WHERE id = ? AND creator_id = ?');
                $stmtCheckPost->execute([$postId, $profileUserId]);
                if ($stmtCheckPost->fetchColumn()) {
                    $stmtInsert = $pdo->prepare('INSERT INTO Comments (post_id, user_id, comment_text) VALUES (?, ?, ?)');
                    $stmtInsert->execute([$postId, $viewerUserId, $commentText]);
                }
            }

            humplore_redirect($requestUri);
        }

        if (($postData['action'] ?? '') === 'ask_question') {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));

            if (!$isCreator) {
                $state['ask_error'] = 'Dieser Nutzer ist kein Creator.';
                return $state;
            }

            $creatorId = (int) ($postData['creator_id'] ?? 0);
            $questionText = trim((string) ($postData['question_text'] ?? ''));

            if ($creatorId !== $profileUserId) {
                $state['ask_error'] = 'Ungültiger Ziel-Creator.';
                return $state;
            }

            if ($questionText === '') {
                $state['ask_error'] = 'Die Frage darf nicht leer sein.';
                return $state;
            }

            $stmtCreator = $pdo->prepare('SELECT is_creator FROM Users WHERE id = ?');
            $stmtCreator->execute([$creatorId]);
            if ((int) $stmtCreator->fetchColumn() !== 1) {
                $state['ask_error'] = 'Ziel ist kein Creator.';
                return $state;
            }

            $stmtInsert = $pdo->prepare('INSERT INTO Questions (creator_id, author_id, question_text) VALUES (?, ?, ?)');
            $stmtInsert->execute([$creatorId, $viewerUserId, $questionText]);
            $state['ask_success'] = 'Frage wurde gesendet.';

            return $state;
        }

        if (($postData['action'] ?? '') === 'answer_question' && $isOwnProfile && $isCreator) {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));

            $questionId = (int) ($postData['question_id'] ?? 0);
            $answerText = trim((string) ($postData['answer_text'] ?? ''));
            $answerMode = (($postData['answer_mode'] ?? 'reply') === 'post') ? 'post' : 'reply';
            $postCategoryRaw = trim((string) ($postData['post_category'] ?? ''));

            if ($questionId <= 0) {
                $state['answer_error'] = 'Die Frage konnte nicht gefunden werden.';
                return $state;
            }

            if ($answerText === '') {
                $state['answer_error'] = 'Die Antwort darf nicht leer sein.';
                return $state;
            }

            $stmtQuestion = $pdo->prepare('SELECT id, question_text FROM Questions WHERE id = ? AND creator_id = ?');
            $stmtQuestion->execute([$questionId, $profileUserId]);
            $question = $stmtQuestion->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                $state['answer_error'] = 'Die Frage gehoert nicht zu diesem Profil.';
                return $state;
            }

            if ($answerMode === 'post') {
                $questionTitle = trim((string) ($question['question_text'] ?? ''));
                if ($questionTitle === '') {
                    $state['answer_error'] = 'Die Frage hat keinen gueltigen Titel.';
                    return $state;
                }

                if ($postCategoryRaw === '') {
                    $state['answer_error'] = 'Bitte eine Kategorie fuer den Beitrag eintragen.';
                    return $state;
                }

                try {
                    $postCategory = humplore_post_editor_validate_category_label($postCategoryRaw);
                } catch (Throwable $e) {
                    $state['answer_error'] = $e->getMessage();
                    return $state;
                }

                try {
                    $pdo->beginTransaction();
                    humplore_post_editor_insert_post($pdo, $profileUserId, $questionTitle, $answerText, [
                        'media_type' => 'text',
                        'category' => $postCategory,
                        'media_image' => null,
                        'is_paid' => 0,
                        'price_cents' => null,
                        'source_question_id' => $questionId,
                    ]);

                    $stmtDeleteLikes = $pdo->prepare('DELETE FROM QuestionLikes WHERE question_id = ?');
                    $stmtDeleteLikes->execute([$questionId]);

                    $stmtDeleteQuestion = $pdo->prepare('DELETE FROM Questions WHERE id = ? AND creator_id = ?');
                    $stmtDeleteQuestion->execute([$questionId, $profileUserId]);

                    $pdo->commit();
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }

                    $state['answer_error'] = 'Der Beitrag konnte nicht erstellt werden.';
                    return $state;
                }
            } else {
                $stmtUpdate = $pdo->prepare('UPDATE Questions SET answer_text = ?, answered_at = CURRENT_TIMESTAMP WHERE id = ?');
                $stmtUpdate->execute([$answerText, $questionId]);
            }

            humplore_redirect($requestUri);
        }

        if ($isOwnProfile && isset($postData['delete_post'])) {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));
            $deletePostId = (int) ($postData['delete_post_id'] ?? 0);

            $stmtDelete = $pdo->prepare('DELETE FROM Posts WHERE id = ? AND creator_id = ?');
            $stmtDelete->execute([$deletePostId, $viewerUserId]);

            humplore_redirect($phpSelf . '?user_id=' . $profileUserId);
        }

        if ($isOwnProfile && isset($postData['save_profile'])) {
            humplore_require_csrf((string) ($postData['csrf_token'] ?? ''));

            $bio = (string) ($postData['bio'] ?? '');
            $imageData = humplore_profile_uploaded_image_data($files['profile_image'] ?? null);

            try {
                $pdo->beginTransaction();

                if ($imageData !== null) {
                    $stmtUpdate = $pdo->prepare('UPDATE Users SET profile_image = ? WHERE id = ?');
                    $stmtUpdate->execute([$imageData, $viewerUserId]);
                }

                $stmtCheck = $pdo->prepare('SELECT id FROM CreatorDetails WHERE user_id = ?');
                $stmtCheck->execute([$viewerUserId]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists) {
                    $stmt = $pdo->prepare('UPDATE CreatorDetails SET bio = ? WHERE user_id = ?');
                    $stmt->execute([$bio, $viewerUserId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO CreatorDetails (user_id, main_topic, bio) VALUES (?, 'Standardthema', ?)");
                    $stmt->execute([$viewerUserId, $bio]);
                }

                $pdo->commit();
                humplore_redirect($phpSelf . '?user_id=' . $profileUserId);
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                die('Fehler beim Speichern: ' . $e->getMessage());
            }
        }

        return $state;
    }
}

if (!function_exists('humplore_profile_uploaded_image_data')) {
    function humplore_profile_uploaded_image_data($profileImageFile): ?string
    {
        if (!is_array($profileImageFile)) {
            return null;
        }

        $tmpName = $profileImageFile['tmp_name'] ?? null;
        if (!$tmpName || !is_uploaded_file($tmpName)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $tmpName) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (is_string($mime) && strpos($mime, 'image/') === 0) {
            return file_get_contents($tmpName) ?: null;
        }

        return null;
    }
}
