<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = humplore_require_json_login();

if (!humplore_csrf_token_is_valid()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid CSRF token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post_id'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = humplore_db();

try {
    $stmtPost = $pdo->prepare('SELECT 1 FROM Posts WHERE id = ?');
    $stmtPost->execute([$postId]);
    if (!$stmtPost->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo->beginTransaction();

    $stmtSaved = $pdo->prepare('SELECT 1 FROM SavedPosts WHERE user_id = ? AND post_id = ?');
    $stmtSaved->execute([$userId, $postId]);
    $alreadySaved = (bool) $stmtSaved->fetchColumn();

    if ($alreadySaved) {
        $stmtDelete = $pdo->prepare('DELETE FROM SavedPosts WHERE user_id = ? AND post_id = ?');
        $stmtDelete->execute([$userId, $postId]);
        $saved = false;
    } else {
        $stmtInsert = $pdo->prepare('INSERT OR IGNORE INTO SavedPosts (user_id, post_id) VALUES (?, ?)');
        $stmtInsert->execute([$userId, $postId]);
        $saved = true;
    }

    $pdo->commit();

    echo json_encode([
        'post_id' => $postId,
        'saved' => $saved,
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['error' => 'Server error'], JSON_UNESCAPED_UNICODE);
    exit;
}
