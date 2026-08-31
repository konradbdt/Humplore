<?php
require_once __DIR__ . '/app/bootstrap.php';

humplore_require_resource_login();

$requestMethod = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (!in_array($requestMethod, ['GET', 'HEAD'], true)) {
  http_response_code(405);
  header('Allow: GET, HEAD');
  header('Cache-Control: no-store');
  exit;
}

$pdo = humplore_db();

$type = isset($_GET['type']) ? (string) $_GET['type'] : '';

function send_file_response(string $path, array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']): void
{
  if (!is_file($path)) {
    http_response_code(404);
    exit;
  }
  $imageInfo = @getimagesize($path);
  $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;
  if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $detected = $fi ? finfo_file($fi, $path) : false;
    if ($fi) {
      finfo_close($fi);
    }
    if (is_string($detected) && $detected !== '' && $mime !== $detected) {
      http_response_code(404);
      exit;
    }
  }
  if (!in_array($mime, $allowedMimeTypes, true)) {
    http_response_code(404);
    exit;
  }
  header('X-Content-Type-Options: nosniff');
  header('Content-Type: ' . $mime);
  header('Cache-Control: private, no-store');
  readfile($path);
  exit;
}

function send_binary_response(string $bin, array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']): void
{
  $imageInfo = @getimagesizefromstring($bin);
  $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;
  if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $detected = $fi ? finfo_buffer($fi, $bin) : false;
    if ($fi) {
      finfo_close($fi);
    }
    if (is_string($detected) && $detected !== '' && $mime !== $detected) {
      http_response_code(404);
      exit;
    }
  }
  if (!in_array($mime, $allowedMimeTypes, true)) {
    http_response_code(404);
    exit;
  }
  header('X-Content-Type-Options: nosniff');
  header('Content-Type: ' . $mime);
  header('Cache-Control: private, no-store');
  echo $bin;
  exit;
}

function normalize_db_binary($value): ?string
{
  if ($value === null) {
    return null;
  }
  if (is_resource($value)) {
    $data = stream_get_contents($value);
    return is_string($data) && $data !== '' ? $data : null;
  }
  if (is_string($value) && $value !== '') {
    return $value;
  }
  return null;
}

function resolve_local_path(string $dbValue): ?string
{
  $value = trim($dbValue);
  if ($value === '' || $value === 'default_profile.png') {
    return null;
  }
  // Binärdaten niemals als Pfad behandeln
  if (strpos($value, "\0") !== false) {
    return null;
  }
  // Nur "pfadartige" Werte zulassen (ASCII-safe)
  if (!preg_match('/^[A-Za-z0-9_\\-.\\/\\\\]+$/', $value)) {
    return null;
  }
  $full = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($value, '/\\'));
  $base = realpath(__DIR__);
  if ($full && $base && str_starts_with($full, $base) && is_file($full)) {
    return $full;
  }
  return null;
}

try {
  if ($type === 'profile') {
    $userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
    if ($userId <= 0) {
      http_response_code(404);
      exit;
    }
    $stmt = $pdo->prepare("SELECT profile_image FROM Users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $bin = $stmt->fetchColumn();
    if (empty($bin)) {
      // Fallback für ältere Datenstände (Profilbild evtl. in CreatorDetails)
      try {
        $stmtCd = $pdo->prepare("SELECT profile_image FROM CreatorDetails WHERE user_id = ? LIMIT 1");
        $stmtCd->execute([$userId]);
        $bin = $stmtCd->fetchColumn();
      } catch (Throwable $e) {
        $bin = null;
      }
    }
    if (empty($bin)) {
      http_response_code(404);
      exit;
    }
    $binNorm = normalize_db_binary($bin);
    if ($binNorm === null) {
      http_response_code(404);
      exit;
    }
    if (is_string($binNorm)) {
      $localPath = resolve_local_path($binNorm);
      if ($localPath !== null) {
        send_file_response($localPath);
      }
      if ($binNorm === 'default_profile.png') {
        http_response_code(404);
        exit;
      }
      send_binary_response($binNorm);
    }
    http_response_code(404);
    exit;
  }

  if ($type === 'post') {
    $postImageMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $postId = isset($_GET['post_id']) ? (int) $_GET['post_id'] : 0;
    if ($postId <= 0) {
      http_response_code(404);
      exit;
    }
    $stmt = $pdo->prepare("SELECT media_type, media_image, media_url FROM Posts WHERE id = ? LIMIT 1");
    $stmt->execute([$postId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || (($row['media_type'] ?? '') !== 'image')) {
      http_response_code(404);
      exit;
    }
    $bin = $row['media_image'] ?? null;
    $url = $row['media_url'] ?? null;
    $binNorm = normalize_db_binary($bin);
    if (!empty($binNorm)) {
      if (is_string($binNorm)) {
        $localPath = resolve_local_path($binNorm);
        if ($localPath !== null) {
          send_file_response($localPath, $postImageMimeTypes);
        }
        send_binary_response($binNorm, $postImageMimeTypes);
      }
      http_response_code(404);
      exit;
    }
    if (is_string($url) && trim($url) !== '') {
      $localPath = resolve_local_path($url);
      if ($localPath !== null) {
        send_file_response($localPath, $postImageMimeTypes);
      }
    }
    http_response_code(404);
    exit;
  }

  http_response_code(400);
} catch (Throwable $e) {
  http_response_code(500);
}
