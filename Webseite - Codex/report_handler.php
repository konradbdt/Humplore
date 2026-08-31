<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$reporterId = humplore_require_json_login();

if (!humplore_csrf_token_is_valid()) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid CSRF token'], JSON_UNESCAPED_UNICODE);
    exit;
}

$targetType = trim((string) ($_POST['target_type'] ?? ''));
$targetId = isset($_POST['target_id']) ? (int) $_POST['target_id'] : 0;
$reason = trim((string) ($_POST['reason'] ?? ''));
$note = humplore_report_note_normalize(isset($_POST['note']) ? (string) $_POST['note'] : null);

if (!humplore_report_target_type_is_valid($targetType) || $targetId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid target'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!humplore_report_reason_is_valid($reason)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid reason'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo = humplore_db();

try {
    if (!humplore_report_target_exists($pdo, $targetType, $targetId)) {
        http_response_code(404);
        echo json_encode(['error' => 'Target not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT OR IGNORE INTO Reports (reporter_id, target_type, target_id, reason, note, status)
         VALUES (?, ?, ?, ?, ?, 'open')"
    );
    $stmt->execute([$reporterId, $targetType, $targetId, $reason, $note]);

    echo json_encode([
        'target_type' => $targetType,
        'target_id' => $targetId,
        'reported' => true,
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error'], JSON_UNESCAPED_UNICODE);
    exit;
}
