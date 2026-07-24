<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');

$pdo = humplore_db();

// 1) Auth
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// 2) Nur POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// 3) Parameter prüfen
$postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$userId = (int) $_SESSION['user_id'];
if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid post_id']);
    exit;
}

// (Optional) 4) CSRF prüfen, wenn du in explore.php ein Meta-Tag setzt
$csrfSess = $_SESSION['csrf_token'] ?? '';
$csrfPost = $_POST['csrf_token'] ?? '';
$csrfHead = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if ($csrfSess && !hash_equals($csrfSess, $csrfPost ?: $csrfHead)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

try {
    // 5) Post-Existenz zuverlässig prüfen (fetchColumn statt rowCount)
    $chk = $pdo->prepare("SELECT 1 FROM Posts WHERE id = ?");
    $chk->execute([$postId]);
    if (!$chk->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Post not found']);
        exit;
    }

    $pdo->beginTransaction();

    // Toggle Like
    $sel = $pdo->prepare("SELECT 1 FROM Likes WHERE post_id = ? AND user_id = ?");
    $sel->execute([$postId, $userId]);
    $already = (bool) $sel->fetchColumn();

    if ($already) {
        $del = $pdo->prepare("DELETE FROM Likes WHERE post_id = ? AND user_id = ?");
        $del->execute([$postId, $userId]);
        $liked = false;
    } else {
        $ins = $pdo->prepare("INSERT INTO Likes (post_id, user_id) VALUES (?, ?)");
        $ins->execute([$postId, $userId]);
        $liked = true;
    }

    // Zähler
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM Likes WHERE post_id = ?");
    $cnt->execute([$postId]);
    $likeCount = (int) $cnt->fetchColumn();


    $pdo->commit();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likeCount' => $likeCount
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    // In Produktion bitte $e loggen
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
    exit;
}
