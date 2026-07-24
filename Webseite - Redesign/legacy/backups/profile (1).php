<?php
// Debug-Endpunkte für IONOS:
// profile.php?debug=ping
// profile.php?user_id=3&debug=1
$debugMode = isset($_GET['debug']) && $_GET['debug'] === '1';
if (isset($_GET['debug']) && $_GET['debug'] === 'ping') {
  header('Content-Type: text/plain; charset=utf-8');
  echo "PROFILE_PING_OK\n";
  echo "FILE: " . __FILE__ . "\n";
  echo "PHP: " . PHP_VERSION . "\n";
  echo "TIME: " . date('c') . "\n";
  exit;
}
if (isset($_GET['debug']) && $_GET['debug'] === 'trace') {
  header('Content-Type: text/plain; charset=utf-8');
  echo "TRACE_START\n";
  echo "PHP: " . PHP_VERSION . "\n";
  session_start();
  $traceViewer = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
  echo "SESSION_USER_ID: " . $traceViewer . "\n";
  $traceUserId = 0;
  if (isset($_GET['user_id'])) {
    $traceUserId = (int) $_GET['user_id'];
  } elseif (isset($_GET['creator_id'])) {
    $traceUserId = (int) $_GET['creator_id'];
  } elseif (isset($_GET['id'])) {
    $traceUserId = (int) $_GET['id'];
  } else {
    $traceUserId = $traceViewer;
  }
  echo "TARGET_USER_ID: " . $traceUserId . "\n";
  $traceDbPath = __DIR__ . "/config/database.php";
  echo "DB_CONFIG_READABLE: " . (is_readable($traceDbPath) ? "yes" : "no") . "\n";
  require $traceDbPath;
  echo "PDO_OK: " . ((isset($pdo) && $pdo instanceof PDO) ? "yes" : "no") . "\n";
  if (isset($pdo) && $pdo instanceof PDO) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM Users WHERE id = ?");
    $st->execute([$traceUserId]);
    echo "USER_EXISTS: " . ((int) $st->fetchColumn() > 0 ? "yes" : "no") . "\n";
  }
  echo "TRACE_END\n";
  exit;
}
if ($debugMode) {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);
  set_exception_handler(function (Throwable $ex) {
    http_response_code(500);
    echo '<pre style="padding:12px;background:#fff3f3;border:1px solid #e6b8b8;color:#8b1e1e">';
    echo "Uncaught exception:\n";
    echo htmlspecialchars((string) $ex, ENT_QUOTES, 'UTF-8');
    echo '</pre>';
    exit;
  });
  set_error_handler(function ($severity, $message, $file, $line) {
    echo '<pre style="padding:12px;background:#fff3f3;border:1px solid #e6b8b8;color:#8b1e1e">';
    echo "PHP error [$severity] in $file:$line\n";
    echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '</pre>';
    return false;
  });
  register_shutdown_function(function () {
    $e = error_get_last();
    if ($e) {
      echo '<pre style="padding:12px;background:#fff3f3;border:1px solid #e6b8b8;color:#8b1e1e">';
      echo "Fatal shutdown error:\n";
      echo htmlspecialchars(print_r($e, true), ENT_QUOTES, 'UTF-8');
      echo '</pre>';
    }
  });
}

session_start();
if (!isset($_SESSION['user_id'])) {
  $redirectTarget = urlencode($_SERVER['REQUEST_URI'] ?? 'profile.php');
  header("Location: login.php?redirect={$redirectTarget}");
  exit;
}
$viewerUserId = (int) $_SESSION['user_id'];

$dbConfigPath = __DIR__ . "/config/database.php";
if (!is_readable($dbConfigPath)) {
  http_response_code(500);
  die("Serverfehler: Datei 'config/database.php' ist nicht lesbar oder fehlt. Bitte FTP-Rechte prüfen (Ordner 755, Datei 644).");
}
require $dbConfigPath;
if (!isset($pdo) || !($pdo instanceof PDO)) {
  http_response_code(500);
  die("Serverfehler: Datenbankverbindung (\$pdo) wurde nicht korrekt initialisiert.");
}

/* ===========================
   Security: CSRF Token
   =========================== */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function profile_img_src($userId): string
{
  return 'media.php?type=profile&user_id=' . (int) $userId;
}

function post_img_src($postId): string
{
  return 'media.php?type=post&post_id=' . (int) $postId;
}

function require_csrf()
{
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    die("Ungültiges CSRF-Token.");
  }
}

/* ===========================
   Eingangsparameter
   =========================== */
$profile_user_id = 0;
if (isset($_GET['user_id'])) {
  $profile_user_id = (int) $_GET['user_id'];
} elseif (isset($_GET['creator_id'])) {
  $profile_user_id = (int) $_GET['creator_id'];
} elseif (isset($_GET['id'])) {
  $profile_user_id = (int) $_GET['id'];
} else {
  $profile_user_id = $viewerUserId;
}

$is_own_profile = ($profile_user_id === $viewerUserId);


/* ===========================
   Benutzerdaten
   =========================== */
$stmt = $pdo->prepare("
  SELECT
    id,
    username,
    is_creator,
    CASE WHEN profile_image IS NOT NULL AND length(profile_image) > 0 THEN 1 ELSE 0 END AS has_profile_image
  FROM Users
  WHERE id = ?
");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
  die("Benutzer nicht gefunden");
}
$isCreator = ((int) $user['is_creator'] === 1);

/* ===========================
   Follow-Status
   =========================== */
$isFollowing = false;
if ($viewerUserId > 0 && !$is_own_profile) {
  try {
    $stmtFollow = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ? AND followed_id = ?");
    $stmtFollow->execute([$viewerUserId, $profile_user_id]);
    $isFollowing = $stmtFollow->fetchColumn() > 0;
  } catch (PDOException $e) {
    $isFollowing = false;
  }
}

/* ===========================
   Statistiken
   =========================== */
$stmtPostsCount = $pdo->prepare("SELECT COUNT(*) FROM Posts WHERE creator_id = ?");
$stmtPostsCount->execute([$profile_user_id]);
$postsCount = (int) $stmtPostsCount->fetchColumn();

$followerCount = 0;
$followingCount = 0;
try {
  $stmtFollower = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE followed_id = ?");
  $stmtFollower->execute([$profile_user_id]);
  $followerCount = (int) $stmtFollower->fetchColumn();

  $stmtFollowing = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ?");
  $stmtFollowing->execute([$profile_user_id]);
  $followingCount = (int) $stmtFollowing->fetchColumn();
} catch (PDOException $e) {
  $followerCount = 0;
  $followingCount = 0;
}

/* Subscriptions (falls vorhanden) */
$subscriberCount = 0;
try {
  $stmtSubs = $pdo->prepare("SELECT COUNT(*) FROM Subscriptions WHERE creator_id = ? AND status = 'active'");
  $stmtSubs->execute([$profile_user_id]);
  $subscriberCount = (int) $stmtSubs->fetchColumn();
} catch (PDOException $e) {
  $subscriberCount = 0;
}

/* ===========================
   CreatorDetails
   =========================== */
$data = null;
if ($isCreator) {
  $stmt = $pdo->prepare("SELECT * FROM CreatorDetails WHERE user_id = ?");
  $stmt->execute([$profile_user_id]);
  $data = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ===========================
   Profil-Schlagwörter (max 3)
   =========================== */
$profileKeywords = [];
try {
  $stmtPK = $pdo->prepare("SELECT keyword, position FROM ProfileKeywords WHERE user_id = ? ORDER BY position ASC");
  $stmtPK->execute([$profile_user_id]);
  $profileKeywords = $stmtPK->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // Tabelle evtl. noch nicht vorhanden -> einfach ignorieren
}

/* ===========================
   Kategorien (für Filter & Anzeige)
   =========================== */
$allCategories = [];
try {
  $allCategories = $pdo->query("SELECT id, name, slug FROM Categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $allCategories = [];
}
$activeCatSlug = isset($_GET['cat']) ? preg_replace('/[^a-z0-9_-]/i', '', $_GET['cat']) : '';

function category_chip_style(string $slug): string
{
  $slug = trim(strtolower($slug));
  if ($slug === '') {
    return '';
  }
  $h = (int) (sprintf('%u', crc32($slug)) % 360);
  $bg = "hsla($h, 70%, 94%, 1)";
  $bd = "hsla($h, 45%, 78%, 1)";
  $tx = "hsla($h, 45%, 28%, 1)";
  return "background:$bg;border-color:$bd;color:$tx;";
}


/* ===========================
   Flash-Messages für Q&A
   =========================== */
$ask_error = '';
$ask_success = '';
$answer_success = '';

/* ===========================
   POST-Verarbeitung (global)
   =========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($viewerUserId <= 0) {
    http_response_code(401);
    die("Bitte zuerst einloggen.");
  }

  /* Folgen/Entfolgen (alle Profile) */
  if (isset($_POST['follow_action'])) {
    require_csrf();
    $action = $_POST['follow_action'];
    if ($action === 'follow') {
      try {
        $stmt = $pdo->prepare("INSERT INTO Follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$viewerUserId, $profile_user_id]);
      } catch (PDOException $e) {
        // Duplicate oder fehlender Unique-Index -> ignorieren
      }
    } elseif ($action === 'unfollow') {
      $stmt = $pdo->prepare("DELETE FROM Follows WHERE follower_id = ? AND followed_id = ?");
      $stmt->execute([$viewerUserId, $profile_user_id]);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }

  /* Kommentar schreiben (ALLE eingeloggten Nutzer) */
  if (isset($_POST['comment_text'], $_POST['post_id'])) {
    require_csrf();
    $post_id = (int) $_POST['post_id'];
    $user_id = $viewerUserId;
    $comment_text = trim((string) $_POST['comment_text']);
    if ($comment_text !== '') {
      // optional: sicherstellen, dass Post zum aktuell angezeigten Profil gehört
      $stmtCheckPost = $pdo->prepare("SELECT id FROM Posts WHERE id = ? AND creator_id = ?");
      $stmtCheckPost->execute([$post_id, $profile_user_id]);
      if ($stmtCheckPost->fetchColumn()) {
        $stmtInsert = $pdo->prepare("INSERT INTO Comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmtInsert->execute([$post_id, $user_id, $comment_text]);
      }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }

  /* Frage stellen (nur Besucher an Creator) */
  if (isset($_POST['action']) && $_POST['action'] === 'ask_question') {
    require_csrf();
    if (!$isCreator) {
      $ask_error = "Dieser Nutzer ist kein Creator.";
    } else {
      $creator_id = (int) $_POST['creator_id'];
      $author_id = $viewerUserId;
      $question_tx = trim((string) ($_POST['question_text'] ?? ''));
      if ($creator_id !== $profile_user_id) {
        $ask_error = "Ungültiger Ziel-Creator.";
      } elseif ($question_tx === '') {
        $ask_error = "Die Frage darf nicht leer sein.";
      } else {
        $stmtC = $pdo->prepare("SELECT is_creator FROM Users WHERE id = ?");
        $stmtC->execute([$creator_id]);
        $isc = (int) $stmtC->fetchColumn();
        if ($isc !== 1) {
          $ask_error = "Ziel ist kein Creator.";
        } else {
          $stmtIns = $pdo->prepare("INSERT INTO Questions (creator_id, author_id, question_text) VALUES (?, ?, ?)");
          $stmtIns->execute([$creator_id, $author_id, $question_tx]);
          $ask_success = "Frage wurde gesendet.";
        }
      }
    }
  }

  /* Frage beantworten (nur Creator auf eigenem Profil) */
  if (isset($_POST['action']) && $_POST['action'] === 'answer_question' && $is_own_profile && $isCreator) {
    require_csrf();
    $qid = (int) ($_POST['question_id'] ?? 0);
    $ansTx = trim((string) ($_POST['answer_text'] ?? ''));
    if ($qid > 0 && $ansTx !== '') {
      // Sicherstellen, dass Frage zu diesem Creator gehört
      $stmtQ = $pdo->prepare("SELECT id FROM Questions WHERE id = ? AND creator_id = ?");
      $stmtQ->execute([$qid, $profile_user_id]);
      if ($stmtQ->fetchColumn()) {
        $stmtUp = $pdo->prepare("UPDATE Questions SET answer_text = ?, answered_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmtUp->execute([$ansTx, $qid]);
        $answer_success = "Antwort gespeichert.";
      }
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }

  /* Rest nur für eigenes Profil (z. B. Post löschen, Profil speichern) */
  if ($is_own_profile) {

    // Beitrag löschen
    if (isset($_POST['delete_post'])) {
      require_csrf();
      $delete_post_id = (int) $_POST['delete_post_id'];
      $stmtDelete = $pdo->prepare("DELETE FROM Posts WHERE id = ? AND creator_id = ?");
      $stmtDelete->execute([$delete_post_id, $viewerUserId]);
      header("Location: " . $_SERVER['PHP_SELF'] . "?user_id=$profile_user_id");
      exit;
    }

    // Profil speichern (Bio + Profilbild)
    if (isset($_POST['save_profile'])) {
      require_csrf();
      $bio = $_POST['bio'] ?? '';
      $profileImage = $_FILES['profile_image']['tmp_name'] ?? null;
      $imageData = null;

      if ($profileImage && is_uploaded_file($profileImage)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $profileImage);
        finfo_close($finfo);
        if (strpos($mime, 'image/') === 0) {
          $imageData = file_get_contents($profileImage);
        }
      }

      try {
        $pdo->beginTransaction();
        if ($imageData) {
          $stmtUpdate = $pdo->prepare("UPDATE Users SET profile_image = ? WHERE id = ?");
          $stmtUpdate->execute([$imageData, $viewerUserId]);
        }
        $stmtCheck = $pdo->prepare("SELECT id FROM CreatorDetails WHERE user_id = ?");
        $stmtCheck->execute([$viewerUserId]);
        $exists = $stmtCheck->fetchColumn();

        if ($exists) {
          $stmt = $pdo->prepare("UPDATE CreatorDetails SET bio = ? WHERE user_id = ?");
          $stmt->execute([$bio, $viewerUserId]);
        } else {
          $stmt = $pdo->prepare("INSERT INTO CreatorDetails (user_id, main_topic, bio) VALUES (?, 'Standardthema', ?)");
          $stmt->execute([$viewerUserId, $bio]);
        }

        $pdo->commit();
        header("Location: " . $_SERVER['PHP_SELF'] . "?user_id=$profile_user_id");
        exit;
      } catch (PDOException $e) {
        $pdo->rollBack();
        die("Fehler beim Speichern: " . $e->getMessage());
      }
    }
  }
}

/* ===========================
   Basis-URLs
   =========================== */
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$profileLink = $baseUrl . "/profile.php?user_id=" . $profile_user_id;

/* ===========================
   Fragen (Top-20 nach Likes)
   =========================== */
$questions = [];
if ($isCreator) {
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
    $stmtQuestions->execute([$profile_user_id]);
    $questions = $stmtQuestions->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $questions = [];
  }
}

/* ===========================
   Profil-Standardwerte
   =========================== */
$profileBio = 'Noch keine Bio vorhanden';
$profileTitle = 'Thema';
$profileUsername = "@" . $user['username'];
$profileTagline = "";
$profileHashtags = [""];
$profileLocation = "Ort nicht angegeben";
$profileLanguages = "Sprache nicht angegeben";
$profileExchange = "Austausch nicht angegeben";

if ($isCreator && !empty($data)) {
  $profileBio = $data['bio'] ?? $profileBio;
  $profileTitle = $data['main_topic'] ?? $profileTitle;
  if (!empty($data['ort']))
    $profileLocation = $data['ort'];
  if (!empty($data['sprache']))
    $profileLanguages = $data['sprache'];
  if (!empty($data['austausch']))
    $profileExchange = $data['austausch'];
  if (!empty($data['hashtags']))
    $profileHashtags = array_map('trim', explode(',', $data['hashtags']));
}

/* ===========================
   Posts laden (+ Kategorien/Filter)
   =========================== */
$posts = [];
$postsPerPage = 8;
$postsPage = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$postsOffset = ($postsPage - 1) * $postsPerPage;
$postsTotal = 0;

try {
  if ($activeCatSlug === '') {
    $stmtCountPosts = $pdo->prepare("SELECT COUNT(*) FROM Posts WHERE creator_id = ?");
    $stmtCountPosts->execute([$profile_user_id]);
    $postsTotal = (int) $stmtCountPosts->fetchColumn();

    $stmtPosts = $pdo->prepare("
      SELECT
        p.id,
        p.creator_id,
        p.title,
        p.content,
        p.media_type,
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
    $stmtPosts->bindValue(':creator_id', $profile_user_id, PDO::PARAM_INT);
    $stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmtPosts->bindValue(':offset', $postsOffset, PDO::PARAM_INT);
    $stmtPosts->execute();
  } else {
    $stmtCountPosts = $pdo->prepare("SELECT COUNT(*) FROM Posts WHERE creator_id = ? AND category = ?");
    $stmtCountPosts->execute([$profile_user_id, $activeCatSlug]);
    $postsTotal = (int) $stmtCountPosts->fetchColumn();

    $stmtPosts = $pdo->prepare("
      SELECT
        p.id,
        p.creator_id,
        p.title,
        p.content,
        p.media_type,
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
    $stmtPosts->bindValue(':creator_id', $profile_user_id, PDO::PARAM_INT);
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
$postsTotalPages = max(1, (int) ceil($postsTotal / $postsPerPage));

// Performance: Likes/Kommentare in Bulk laden (statt N+1 pro Post)
$likeCountsByPost = [];
$likedByViewer = [];
$commentsByPost = [];
$postIds = array_values(array_unique(array_map(static function ($p) {
  return (int) ($p['id'] ?? 0);
}, $posts)));
$postIds = array_values(array_filter($postIds, static function ($id) {
  return $id > 0;
}));

if (!empty($postIds)) {
  $ph = implode(',', array_fill(0, count($postIds), '?'));
  try {
    $stmtBulkLikes = $pdo->prepare("SELECT post_id, COUNT(*) AS c FROM Likes WHERE post_id IN ($ph) GROUP BY post_id");
    $stmtBulkLikes->execute($postIds);
    foreach ($stmtBulkLikes->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $likeCountsByPost[(int) $row['post_id']] = (int) $row['c'];
    }
  } catch (PDOException $e) {
    $likeCountsByPost = [];
  }

  if ($viewerUserId > 0) {
    try {
      $stmtBulkLiked = $pdo->prepare("SELECT post_id FROM Likes WHERE user_id = ? AND post_id IN ($ph)");
      $stmtBulkLiked->execute(array_merge([$viewerUserId], $postIds));
      foreach ($stmtBulkLiked->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $likedByViewer[(int) $row['post_id']] = true;
      }
    } catch (PDOException $e) {
      $likedByViewer = [];
    }
  }

  try {
    $stmtBulkComments = $pdo->prepare("
      SELECT Comments.*, Users.username
      FROM Comments
      JOIN Users ON Comments.user_id = Users.id
      WHERE post_id IN ($ph)
      ORDER BY created_at DESC
    ");
    $stmtBulkComments->execute($postIds);
    foreach ($stmtBulkComments->fetchAll(PDO::FETCH_ASSOC) as $row) {
      $pid = (int) $row['post_id'];
      if (!isset($commentsByPost[$pid])) {
        $commentsByPost[$pid] = [];
      }
      $commentsByPost[$pid][] = $row;
    }
  } catch (PDOException $e) {
    $commentsByPost = [];
  }
}

$active = 'profile';

// Zugriff auf einen Post: free oder eigener Post -> Zugriff
function post_has_access(array $post, int $viewerId): bool
{
  if (empty($post['is_paid']) || (int) $post['is_paid'] === 0)
    return true;
  if ((int) $post['creator_id'] === $viewerId)
    return true;
  // später: Käufe prüfen (PostPurchases)
  return false;
}

function formatEuroCents($cents): string
{
  if ($cents === null)
    return '';
  return number_format(((int) $cents) / 100, 2, ',', '.') . ' €';
}

if (!function_exists('smart_split')) {
  function txt_len(string $text): int {
    return function_exists('mb_strlen') ? mb_strlen($text) : strlen($text);
  }

  function txt_sub(string $text, int $start, $length = null): string {
    if (function_exists('mb_substr')) {
      return $length === null ? mb_substr($text, $start) : mb_substr($text, $start, $length);
    }
    return $length === null ? substr($text, $start) : substr($text, $start, $length);
  }

  function txt_rpos(string $haystack, string $needle) {
    return function_exists('mb_strrpos') ? mb_strrpos($haystack, $needle) : strrpos($haystack, $needle);
  }

  function smart_split(string $text, int $limit): array {
    $len = txt_len($text);
    if ($len <= $limit) return [$text, ''];

    $head = txt_sub($text, 0, $limit);

    // 1) wenn möglich an Absatz-Grenze in den letzten 120 Zeichen schneiden
    $window = txt_sub($head, max(0, txt_len($head) - 120));
    $pos = txt_rpos($window, "\n\n");
    if ($pos !== false) {
      $cut = (txt_len($head) - txt_len($window)) + $pos;
      return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
    }

    // 2) sonst an Satzende
    foreach (['. ', '! ', '? '] as $needle) {
      $pos2 = txt_rpos($head, $needle);
      if ($pos2 !== false && $pos2 > $limit * 0.6) {
        $cut = $pos2 + txt_len($needle);
        return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
      }
    }

    // 3) sonst am letzten Leerzeichen
    $pos3 = txt_rpos($head, ' ');
    if ($pos3 !== false && $pos3 > $limit * 0.6) {
      $cut = $pos3 + 1;
      return [trim(txt_sub($text, 0, $cut)), trim(txt_sub($text, $cut))];
    }

    // 4) fallback hart
    return [trim($head), trim(txt_sub($text, $limit))];
  }
}



?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Humannlibrary - Profil</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href='https://fonts.googleapis.com/css?family=Lora' rel='stylesheet'>
  <link href='https://fonts.googleapis.com/css?family=DM Serif Display' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    /* (dein bestehendes CSS unverändert) */
    /* ==================== */
    /* GLOBALE STYLES */
    /* ==================== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #f9f9f9;
      font-family: 'Poppins', sans-serif;
      color: #333;
      line-height: 1.5;
    }

    /* Schriftarten für spezifische Elemente */
    h1 {
      font-family: 'DM Serif Display';
    }

    /* ==================== */
    /* HEADER & BANNER */
    /* ==================== */
    header {
      background-color: #fff;
      padding: 15px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 70;
      height: var(--header-h);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Logo-Container + Bildgröße */
    header .brand {
      display: inline-flex;
      align-items: center;
      height: 100%;
    }

    header .brand img {
      height: var(--brand-img-h);
      width: auto;
      display: block;
    }

    /* Optional: damit die Sticky-Suchkarte nie unter den Header rutscht */
    .search-card {
      position: sticky;
      top: calc(var(--header-h) + 12px);
      z-index: 55;
      /* ... Rest bleibt ... */
    }

    /* Mobile etwas kompakter (optional) */
    @media (max-width: 720px) {
      :root {
        --header-h: 72px;
        --brand-img-h: 48px;
      }
    }

    header h1 {
      font-size: 1.2rem;
      color: #6a743a;
    }

    .banner {
      width: 100%;
      height: 150px;
      background-color: #6a743a;
      position: relative;
      border-bottom-left-radius: 80px;
      border-bottom-right-radius: 80px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);

    }


    /* ==================== */
    /* FRAGEN-SIDEBAR */
    /* ==================== */
    .questions-card {
      background: rgba(255, 255, 255, .86);

      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      padding: 16px;
      margin-bottom: 16px;
      
      border: 1px solid #e5e7eb;
      max-height: 70vh;
      /* begrenzt die Gesamthöhe in der Sidebar */
      display: flex;
      flex-direction: column;
      gap: 8px;
    
    }

    .questions-card h3 {
      margin-bottom: 10px;
      padding-bottom: 6px;
      color: #6a743a;
      flex: 0 0 auto;
      border-bottom: 1px solid #e5e7eb;
      /* Trennlinie unter der Überschrift */
    }


    .q-scroll {
      flex: 1 1 auto;
      overflow-y: auto;
      padding-right: 6px;
      /* Platz für Scrollbar */
      max-height: 60vh;
      /* eigentliche Scrollflächengröße */
    }

    /* hübschere Scrollbar */
    .q-scroll::-webkit-scrollbar {
      width: 8px;
    }

    .q-scroll::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    .q-scroll::-webkit-scrollbar-thumb {
      background: #6a743a;
      border-radius: 4px;
    }

    .questions-card form textarea {
      width: 80%;
      padding: 10px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      resize: vertical;
    }

    .questions-card form button {
      margin-top: 8px;
      padding: 10px 12px;
      border: none;
      border-radius: 8px;
      background: #111827;
      color: #fff;
      cursor: pointer;
    }

    .qa-item {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 10px;
      margin-top: 10px;
      background: #fff;
    }

    .qa-item .meta {
      font-size: 12px;
      color: #6b7280;
    }

    .qa-item .q {
      font-weight: 600;
      margin-top: 6px;
    }

    .qa-item .a {
      margin-top: 6px;
    }

    .flash-ok {
      color: #065f46;
      margin-bottom: 6px;
      flex: 0 0 auto;
    }

    .flash-err {
      color: #b91c1c;
      margin-bottom: 6px;
      flex: 0 0 auto;
    }

    /* ==================== */
    /* HAUPTLAYOUT */
    /* ==================== */
    .main-content-wrapper {
      display: flex;
      gap: 30px;
      padding: 0 20px;
      max-width: 1600px;
      /* Erhöhte Maximalbreite */
      margin: 0 auto;
      flex-direction: row;
      border: #6a743a;
      border-top-right-radius: 50px;

    }

    /* Seitencontainer */
    .side-container {
      flex: 1;
      min-width: 250px;
      max-width: 350px;
      position: sticky;
      top: 200px;
      height: fit-content;
      color: black;
      /* color: #4b573e; */
    }

    /* Hauptprofilcontainer */
    .profile-container {
      flex: 0 0 800px;
      margin: -95px auto 40px;
      padding: 0 0;
    }

    /* ==================== */
    /* PROFILKARTE */
    /* ==================== */
    .profile-card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px #580F41;
      position: relative;
    }

    .profile-card-top {
      margin: 0px 20px 10px 20px;
    }

    /* Profilbild */
    .profile-img-wrapper {
      position: relative;
      top: 0px;
      left: 0px;
      width: 150px;
      height: 150px;
      z-index: 2;
    }

    .arround-profile-img-wrapper {
      height: 175px;
    }

    .profile-img-wrapper img,
    .profile-img-wrapper .profile-initials {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      /* border: 8px solid rgb(153, 194, 107); */
      border: 8px solid #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      font-weight: 800;
      color: #fff;
      background: #580F41;

    }

    /* Flex-Layout für Profilheader */
    .header-flex-one {
      display: flex;
    }

    .header-flex-two-left {
      width: 45%;
    }

    /* ===== Stats-Leiste ===== */
    .header-flex-two-right {
      width: 55%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 16px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
    }

    .stat-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 14px 12px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: transform .15s ease, box-shadow .15s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }

    .stat-value {
      font-weight: 800;
      font-size: 1.4rem;
      color: #111827;
      line-height: 1.1;
    }

    .stat-label {
      margin-top: 4px;
      font-size: .85rem;
      color: #6b7280;
      letter-spacing: .2px;
    }

    /* ===== Folgen/Entfolgen Button ===== */
    .follow-form {
      margin-top: 4px;
    }

    .follow-btn {
      appearance: none;
      border: 0;
      cursor: pointer;
      border-radius: 999px;
      padding: 10px 16px;
      font-size: 0.95rem;
      font-weight: 700;
      color: #ffffff;
      background: linear-gradient(135deg, #6ea173, #6a743a);
      box-shadow: 0 6px 16px rgba(75, 87, 62, 0.25);
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: transform .08s ease, box-shadow .2s ease, filter .2s ease;
    }

    .follow-btn:hover {
      filter: brightness(1.05);
      box-shadow: 0 10px 22px rgba(75, 87, 62, .28);
    }

    .follow-btn:active {
      transform: translateY(1px);
    }

    .follow-btn.is-active {
      background: linear-gradient(135deg, #9ca3af, #6b7280);
      box-shadow: 0 6px 16px rgba(107, 114, 128, 0.25);
    }

    /* kleines Status-Icon links im Button */
    .follow-dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: #d1fae5;
      /* mint-grün */
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5) inset;
    }

    .follow-btn.is-active .follow-dot {
      background: #fee2e2;
      /* zart-rot für "Entfolgen" aktiv */
    }

    /* Responsive Tweaks */
    @media (max-width: 768px) {
      .header-flex-two-right {
        width: 100%;
      }

      .stats-grid {
        gap: 10px;
      }

      .stat-value {
        font-size: 1.2rem;
      }

      .follow-btn {
        width: 100%;
        justify-content: center;
      }
    }


    .header-flex-three {
      display: flex;
      flex-direction: column;
      text-align: center;
      align-items: center;
      padding-bottom: 10px;
      padding-top: 180px;
    }

    .header-flex-three span {
      font-size: 0.9rem;
      color: #777;
    }

    .header-flex-three-s {
      display: flex;
      flex-direction: column;
      text-align: center;
      padding-bottom: 20px;
      align-items: center;
      padding-top: 55px;

    }

    .einstellung {}

    /* Profilinformationen */
    .profile-header {
      margin-top: 0px;
      margin-bottom: 45px;
    }

    .profile-header .profile-info {
      text-align: left;
      padding-top: 20px;
    }

    .profile-title {
      padding: 5px;
      font-size: 1.5rem;
      font-weight: 700;
      color: white;
      background-color: rgba(221, 108, 108, 1);
      border-radius: 50px;
      width: 165px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .profile-username {
      font-size: 1.1rem;
      color: #777;
      margin: 5px 0;
    }

    .profile-tagline {
      font-size: 1rem;
      font-style: italic;
      margin: 10px 0;
      color: #555;
    }

    .profile-hashtags span {
      display: inline-block;
      background-color: #e0e0e0;
      color: #333;
      padding: 5px 10px;
      border-radius: 20px;
      margin: 0 5px 5px 0;
      font-size: 0.9rem;
    }

    .profile-meta p {
      margin: 3px 0;
      font-size: 0.9rem;
      color: #555;
    }

    /* ==================== */
    /* BUTTONS */
    /* ==================== */
    .button-33 {
      border: none;
      border-radius: 10em;
      background: rgb(183, 181, 183);
      color: #ffffff;
      font-family: inherit;
      font-weight: 500;
      font-size: 20px;
      margin-top: 10%;
      margin-bottom: 9%;
      width: 125px;
      height: 40px;
    }

    .button-33:hover {
      box-shadow: 0 0 5px #6a743a#,
        0 0 25px #6a743a,
        0 0 50px #6a743a,
        0 0 100px #6a743a;
    }

    .button-34 {
      border: none;
      border-radius: 10em;
      background: rgb(128, 151, 103);
      color: #ffffff;
      font-family: inherit;
      font-weight: 700;
      font-size: 20px;
      margin-top: 30%;
      margin-bottom: 15%;
      width: 120px;
      height: 40px;
      position: sticky;
    }

    .button-34:active {
      background: rgb(119, 139, 96);
    }


    /* ==================== */
    /* KATEGORIEN */
    /* ==================== */
    .category {
      display: flex;
      justify-content: center;
      gap: 15px;
      overflow: visible;
      position: relative;
      position: sticky;
      top: 40px;
      z-index: 50;
    }

    /* ==================== */
    /* BEITRÄGE */
    /* ==================== */
    /* ==================== */
    /* POST CARD STYLES */
    /* ==================== */
    .post-card {
      width: 100%;
      background: #fff;
      border-radius: 42px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      padding: 20px;
      margin-bottom: 24px;
      box-sizing: border-box;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .post-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    }

    .post-header {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }

    .post-header-img {
      width: 48px;
      height: 48px;
      margin-right: 12px;
    }

    .post-header-img img,
    .post-header-initials {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .post-header-info {
      display: flex;
      flex-direction: column;
    }

    .post-header-info .post-author {
      font-weight: 600;
      color: #580F41;
      font-size: 0.95rem;
    }

    .post-header-info .post-date {
      font-size: 0.8rem;
      color: #888;
      margin-top: 2px;
    }

    .post-title {
      font-size: 1.4rem;
      color: #333;
      margin-bottom: 12px;
      font-family: 'DM Serif Display';
      line-height: 1.4;
    }

    .post-content-wrapper {
      margin-bottom: 16px;
    }

    .post-image {
      width: 100%;
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .post-image img {
      width: 100%;
      height: auto;
      object-fit: cover;
      display: block;
    }

    .post-content {
      font-size: 1.05rem;
      color: #444;
      line-height: 1.6;
      margin-bottom: 0;
      font-family: 'Lora';
      white-space: normal;

    }

    /* Abstand zwischen Absätzen im Post-Text */
    .post-content p {
      margin: 0 0 1.1em;
      /* eine freie Zeile */
    }

    /* "mehr lesen" / "weniger anzeigen" jeweils als eigene Zeile */
    .post-readmore,
    .post-readless {
      display: block;
      margin-top: 6px;
    }


    .more-link {
      color: rgb(77, 77, 77);
      text-decoration: none;
      font-weight: 500;
      display: inline-block;
      margin-top: 8px;
      font-size: 0.9rem;
      transition: color 0.2s;
    }

    .more-link:hover {
      color: rgb(105, 104, 104);
      text-decoration: underline;
    }

    .more-content {
      display: none;
    }

    /* ==================== */
    /* POST ACTIONS */
    /* ==================== */
    .post-actions {
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee;
      padding-top: 16px;
      margin-top: 16px;
    }

    .action-button {
      background: none;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      border-radius: 8px;
      transition: all 0.2s ease;
      color: #666;
    }

    .action-button:hover {
      background: #f5f5f5;
      color: #580F41;
    }

    .action-icon {
      width: 20px;
      height: 20px;
      fill: currentColor;
    }

    .action-count {
      font-weight: 600;
      font-size: 0.9rem;
    }

    .action-label {
      font-size: 0.9rem;
    }

    /* Like Button */
    .like-button.liked {
      color: #e74c3c;
    }

    .like-button.liked:hover {
      background: rgba(231, 76, 60, 0.1);
    }

    /* Share Button */
    .share-button:hover {
      color: #3498db;
    }

    /* Donate Button */
    .donate-button:hover {
      color: #2ecc71;
    }

    /* ==================== */
    /* RESPONSIVE ADJUSTMENTS */
    /* ==================== */
    @media (max-width: 768px) {
      .post-card {
        border-radius: 0;
        box-shadow: none;
        margin-bottom: 16px;
        border-bottom: 1px solid #eee;
      }

      .post-actions {
        justify-content: space-around;
        gap: 4px;
      }

      .action-button {
        flex-direction: column;
        gap: 4px;
        padding: 8px;
        font-size: 0.8rem;
      }

      .action-label {
        display: none;
      }

      .action-icon {
        width: 18px;
        height: 18px;
      }
    }


    .nav-button {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background: #6a743a#;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      text-align: center;
    }

    /* ==================== MODAL – modern & sauber (v2) ==================== */
    :root {
      --brand: #6a743a#;
      --modal-bg: rgba(255, 255, 255, .88);
      --modal-overlay: rgba(17, 24, 39, .55);
      --modal-shadow: 0 30px 80px rgba(0, 0, 0, .25);
      --modal-radius: 20px;
      --modal-pad: 22px;

      --header-h: 84px;
      /* Headerhöhe (größer) */
      --brand-img-h: 56px;
      /* Bildhöhe im Header */
    }

    body.no-scroll {
      overflow: hidden;
    }

    /* Overlay */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: var(--modal-overlay);
      backdrop-filter: saturate(120%) blur(3px);
      z-index: 999;
      opacity: 0;
      display: none;
      transition: opacity .20s ease;
    }

    /* Dialog */
    .modal {
      position: fixed;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -46%) scale(.985);
      width: min(92vw, 680px);
      background: var(--modal-bg);
      color: #111827;
      border-radius: var(--modal-radius);
      box-shadow: var(--modal-shadow);
      padding: 0;
      z-index: 1000;
      opacity: 0;
      display: none;
      transition: opacity .20s ease, transform .20s ease;
      backdrop-filter: saturate(140%) blur(8px);
      border: 1px solid rgba(255, 255, 255, .6);
      overflow: hidden;
    }

    /* Open state */
    .modal-open .modal-overlay {
      display: block;
      opacity: 1;
    }

    .modal-open .modal {
      display: block;
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }

    /* Header */
    .modal__header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 20px;
      background: linear-gradient(180deg, rgba(255, 255, 255, .65), rgba(255, 255, 255, .35));
      border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .modal__title {
      font-size: 1.35rem;
      font-weight: 800;
      color: #1f2937;
      letter-spacing: .2px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .modal__title::before {
      content: "";
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: var(--brand);
      box-shadow: 0 0 0 4px rgba(75, 87, 62, .15);
    }

    .modal__close {
      appearance: none;
      border: 0;
      background: transparent;
      cursor: pointer;
      padding: 8px;
      border-radius: 10px;
      color: #6b7280;
    }

    .modal__close:hover {
      background: #f3f4f6;
      color: #111827;
    }

    /* Body */
    .modal__body {
      padding: 18px 20px 20px;
    }

    .settings-grid {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 18px;
    }

    @media(max-width:760px) {
      .settings-grid {
        grid-template-columns: 1fr;
      }
    }

    /* Avatar card */
    .avatar-card {
      background: rgba(255, 255, 255, .7);
      border: 1px solid rgba(0, 0, 0, .06);
      border-radius: 16px;
      padding: 14px;
      text-align: center;
    }

    .avatar-preview {
      width: 160px;
      height: 160px;
      margin: 8px auto 10px auto;
      border-radius: 50%;
      overflow: hidden;
      background: #f7f8f5;
      border: 6px solid rgba(255, 255, 255, .9);
      outline: 2px solid #dbe2cf;
      display: grid;
      place-items: center;
      font-weight: 800;
      color: var(--brand);
      box-shadow: 0 10px 24px rgba(0, 0, 0, .06);
    }

    .avatar-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .upload-hint {
      font-size: .88rem;
      color: #6b7280;
    }

    /* Drag&Drop */
    .dropzone {
      margin-top: 10px;
      padding: 12px;
      border: 1.5px dashed #cbd5e1;
      border-radius: 12px;
      background: #fbfbfb;
      color: #64748b;
      font-size: .9rem;
    }

    .dropzone.drag {
      background: #f1f5f9;
      border-color: #94a3b8;
    }

    /* Fields */
    .form-field {
      margin-bottom: 14px;
    }

    .modal__body label {
      display: block;
      margin: 8px 0 6px;
      font-weight: 700;
      color: #374151;
      font-size: .95rem;
    }

    .modal__body textarea {
      width: 100%;
      padding: 12px 14px;
      border: 1.5px solid #e5e7eb;
      border-radius: 12px;
      background: #fafafa;
      transition: border-color .2s, background .2s, box-shadow .2s;
      min-height: 120px;
      resize: vertical;

      /* NEU: Schrift-Anpassungen */
      font-size: 1.05rem;
      /* macht den Text größer (Standard: ca. 16px → jetzt ca. 17px) */
      line-height: 1.6;
      /* bessere Lesbarkeit */
      font-family: 'Lora', serif;
      /* elegante Schrift, wirkt hochwertiger */
      color: #333;
      /* dunkler, besser lesbar */
    }

    .modal__body textarea:focus {
      border-color: var(--brand);
      background: #fff;
      outline: none;
      box-shadow: 0 0 0 4px rgba(75, 87, 62, .10);
    }

    /* Bio counter */
    .bio-meta {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 8px;
      font-size: .85rem;
      color: #6b7280;
    }

    .bio-progress {
      width: 100%;
      height: 8px;
      border-radius: 999px;
      background: #ffffffff;
      overflow: hidden;
      margin-top: 8px;
    }

    .bio-progress>span {
      display: block;
      height: 100%;
      width: 0%;
      background: linear-gradient(90deg, #a3b18a, var(--brand));
      transition: width .25s ease;
    }

    /* Buttons */
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 16px;
      justify-content: flex-end;
      flex-wrap: wrap;
    }

    .save-btn,
    .close-btn {
      padding: 12px 16px;
      border: 0;
      border-radius: 12px;
      font-weight: 800;
      cursor: pointer;
      transition: transform .08s ease, filter .2s ease, box-shadow .2s ease;
    }

    .save-btn {
      background: var(--brand);
      color: #fff;
      box-shadow: 0 8px 18px rgba(75, 87, 62, .22);
    }

    .save-btn:hover {
      filter: brightness(1.05);
    }

    .save-btn:active {
      transform: translateY(1px);
    }

    .close-btn {
      background: #eef2e6;
      color: #374151;
    }

    .close-btn:hover {
      filter: brightness(1.02);
    }

    /* Footer hint */
    .modal__footer {
      padding: 14px 20px;
      background: linear-gradient(180deg, rgba(255, 255, 255, .25), rgba(255, 255, 255, .5));
      border-top: 1px solid rgba(0, 0, 0, .06);
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }


    /* ==================== */
    /* TEILEN-CONTAINER */
    /* ==================== */
    .share-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      border: 1px solid #e6ebe1;
      padding: 16px;
      margin-top: 16px;
      position: static;
      top: auto;
      width: min(100%, 338px);
      box-sizing: border-box;
    }

    .share-container h3 {
      color: #2f3a2a;
      margin-bottom: 12px;
      font-size: 1.05rem;
      font-weight: 900;
    }

    .share-link-container {
      display: flex;
      gap: 10px;
      align-items: stretch;
      flex-wrap: wrap;
    }

    .share-link-container input {
      flex: 1 1 220px;
      min-width: 0;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 0.9rem;
    }

    .share-link-container button {
      flex: 0 0 auto;
      padding: 10px 15px;
      background: #6a743a;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.3s;
      font-weight: 600;
    }

    .share-link-container button:hover {
      background: rgb(143, 173, 112);
    }

    .copy-confirmation {
      display: none;
      color: #2b6b2f;
      margin-top: 8px;
      font-weight: 700;
      font-size: .88rem;
    }

    @media (max-width: 1100px) {
      .share-link-container {
        flex-direction: column;
      }

      .share-link-container button {
        width: 100%;
      }
    }

    .leckdoch {
      border: none;
      border-radius: 10em;
      background: rgb(114, 156, 94);
      color: #ffffff;
      font-family: inherit;
      font-weight: 500;
      font-size: 15px;
      margin-top: 74px;
      margin-bottom: 21px;
      width: 95px;
      height: 30px;
    }

    /* ==================== */
    /* RESPONSIVE DESIGN */
    /* ==================== */
    @media (max-width: 768px) {

      /* Mobile Header-Anpassungen */
      .header-flex-one {
        flex-direction: column;
      }

      .header-flex-two-left {
        width: 100%;
      }

      .header-flex-two-right {
        width: 100%;
      }

      .header-flex-three {
        padding-top: 0;
      }

      .header-flex-three-s {
        padding-top: 0;
      }

      /* Mobile Button-Anpassungen */
      .button-33 {
        height: 30px;
        width: 70px;
        margin: 0;
        font-size: 15px;
        font-weight: 1000;
        margin-bottom: 30px;
      }

      .button-34 {
        font-size: 18px;
        width: 110px;
      }

      /* Mobile Frage-Anpassungen */
      .question {
        width: 100%;
        margin: 10px 0;
      }

      .all-center {
        flex-direction: column;
      }

      /* Mobile Beitrags-Anpassungen */
      .image-content {
        width: 100%;
      }

      .post-actions {
        flex-direction: row;
        justify-content: space-around;
        width: 100%;
        padding-top: 20px;
      }

      .comments-button {
        display: inline-block !important;
      }

      .comments-section {
        display: none !important;
      }

      /* Versteckte Elemente auf Mobile */
      .leckdoch {
        display: none;
      }

      .einstellung {
        margin-top: 1px;
      }

      /* Layout-Anpassungen */
      .main-content-wrapper {
        padding: 0;
        flex-direction: column;
      }

      .post-card {
        border-radius: 0;
        box-shadow: none;
        margin-bottom: 3px;
      }

      .side-container {
        display: none;
      }

      .profile-container {
        flex: 1 0 100%;
        max-width: 100%;
        padding: 0 15px;
      }

      .profile-card-top {
        margin: 0 15px 50px 15px;
      }

      .banner {
        height: 120px;
      }
    }

    /* Tablet-Optimierung */
    @media (min-width: 769px) and (max-width: 1200px) {
      .side-container {
        min-width: 200px;
      }

      .profile-container {
        flex: 0 0 500px;
      }
    }

    /* Toast */
    #toast.toast {
      position: fixed;
      left: 50%;
      bottom: 80px;
      transform: translateX(-50%);
      background: #111827;
      color: #fff;
      padding: 10px 14px;
      border-radius: 10px;
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s ease;
      z-index: 2000;
      font-size: .95rem;
    }

    #toast.toast--show {
      opacity: 1;
    }

    /* Visuelles Highlight für geteilte Karte */
    .shared-highlight {
      outline: 3px solid #4b573e33;
      box-shadow: 0 0 0 4px #4b573e22, 0 6px 18px rgba(0, 0, 0, .12);
      animation: pulseHL 1.2s ease 1;
    }

    /* kleine Ergänzung: Keyword-Badges */
    .profile-keywords span {
      display: inline-block;
      background: #eef2e6;
      color: #374151;
      padding: 6px 10px;
      border-radius: 999px;
      margin: 4px 6px 0 0;
      font-size: .85rem;
      font-weight: 600;
      border: 1px solid #dbe2cf;
    }

    /* Kategorie-Chips oberhalb der Posts */
    .category-bar {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin: 10px 0 18px 0;
    }

    .post-catline {
      font-size: .85rem;
      color: #6b7280;
      margin-top: 6px;
    }

    /* Kategorie-Pill wie in explore.php */
    .cat-pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-right: 6px;
      padding: 3px 9px;
      border-radius: 999px;
      background: #9da76bff;
      border: 1px solid #dce3d7;
      color: white
        /* #6b7280;*/
      ;
      font-weight: 600;
      font-size: .78rem;
      white-space: nowrap;
    }

    .cat-chip {
      display: inline-block;
      padding: 8px 12px;
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      background: #fff;
      cursor: pointer;
      text-decoration: none;
      font-weight: 600;
      font-size: .9rem;
      color: #374151;
    }

    .cat-chip.active {
      background: #6a743a;
      color: #fff;
      border-color: #6a743a;
    }

    .post-catline {
      font-size: .85rem;
      color: #6b7280;
      margin-top: 6px;
    }

    /* === COMMENTS: neues Styling === */
    .comments-section {
      margin-top: 14px;
      border-top: 1px solid #edf0ec;
      padding-top: 14px;
      overflow: hidden;
      max-height: 0;
      /* für Slide-Animation */
      transition: max-height .3s ease, padding-top .3s ease, border-top-color .3s ease;
    }

    .comments-section.open {
      max-height: 1000px;
      /* groß genug für "Slide" */
      padding-top: 14px;
      border-top-color: #edf0ec;
    }

    .comments-empty {
      padding: 14px;
      font-size: .95rem;
      color: #6b7280;
      background: #f6f8f4;
      border: 1px dashed #e3e8dc;
      border-radius: 10px;
    }

    .comment {
      display: grid;
      grid-template-columns: 40px 1fr;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #f2f4f0;
    }

    .comment:last-child {
      border-bottom: 0;
    }

    .comment-avatar {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      overflow: hidden;
      background: #e8efe0;
      display: grid;
      place-items: center;
      font-weight: 800;
      color: #6a743a;
    }

    .comment-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .comment-bubble {
      background: #ffffff;
      border: 1px solid #e7ebdf;
      border-radius: 14px;
      padding: 10px 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .04);
    }

    .comment-header {
      display: flex;
      align-items: baseline;
      gap: 8px;
      margin-bottom: 6px;
    }

    .comment-user {
      font-weight: 700;
      color: #374151;
      font-size: .95rem;
    }

    .comment-time {
      font-size: .78rem;
      color: #9aa089;
    }

    .comment-text {
      color: #374151;
      line-height: 1.5;
      font-size: .95rem;
      word-wrap: break-word;
    }

    /* Formular */
    .comment-form {
      display: grid;
      grid-template-columns: 40px 1fr;
      gap: 10px;
      margin-top: 12px;
    }

    .comment-form .me-avatar {
      width: 40px;
      height: 40px;
      border-radius: 999px;
      overflow: hidden;
      background: #e8efe0;
      display: grid;
      place-items: center;
      font-weight: 800;
      color: #6a743a;
    }

    .comment-input {
      background: #fff;
      border: 1px solid #dfe6d7;
      border-radius: 12px;
      padding: 8px 10px;
    }

    .comment-input textarea {
      width: 100%;
      border: 0;
      outline: 0;
      resize: none;
      min-height: 44px;
      font-family: inherit;
      font-size: .95rem;
      color: #374151;
    }

    .comment-actions {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-top: 8px;
    }

    .btn-send {
      appearance: none;
      border: 0;
      cursor: pointer;
      border-radius: 10px;
      padding: 8px 12px;
      font-weight: 700;
      background: #6a743a;
      color: #fff;
      transition: transform .05s ease, filter .2s ease;
    }

    .btn-send:hover {
      filter: brightness(1.05);
    }

    .btn-send:active {
      transform: translateY(1px);
    }

    @media (max-width: 768px) {
      .comment {
        grid-template-columns: 34px 1fr;
      }

      .comment-avatar,
      .comment-form .me-avatar {
        width: 34px;
        height: 34px;
      }
    }

    /* === Post-Kebab-Menu (3 Punkte) === */
    .post-header {
      position: relative;
    }

    .post-menu {
      margin-left: auto;
      margin-right: -6px;
      position: relative;
    }

    .menu-trigger {
      appearance: none;
      border: 0;
      background: transparent;
      cursor: pointer;
      padding: 6px 8px;
      border-radius: 8px;
      display: grid;
      place-items: center;
      color: #6b7280;
    }

    .menu-trigger:hover {
      background: #f3f4f6;
      color: #111827;
    }

    .menu-trigger svg {
      width: 20px;
      height: 20px;
    }

    .menu-dropdown {
      position: absolute;
      top: 34px;
      right: 0;
      min-width: 160px;
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      box-shadow: 0 10px 24px rgba(0, 0, 0, .08);
      padding: 6px;
      display: none;
      z-index: 20;
    }

    .menu-dropdown.open {
      display: block;
    }

    .menu-item {
      width: 100%;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 10px;
      border-radius: 8px;
      color: #374151;
      text-decoration: none;
      background: transparent;
      border: 0;
      cursor: pointer;
      font-weight: 600;
      font-size: .92rem;
    }

    .menu-item:hover {
      background: #f3f4f6;
    }

    .menu-item.danger {
      color: #b91c1c;
    }

    .menu-item.danger:hover {
      background: #fee2e2;
    }

    .edit-profile-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
      padding: 10px 14px;
      border: 0;
      border-radius: 12px;
      cursor: pointer;
      font-weight: 700;
      font-size: .95rem;
      background: #6a743a;
      color: #fff;
      box-shadow: 0 6px 16px rgba(75, 87, 62, .18);
      transition: transform .08s ease, filter .2s ease, box-shadow .2s ease;
    }

    .edit-profile-btn:hover {
      filter: brightness(1.05);
      box-shadow: 0 10px 22px rgba(75, 87, 62, .24);
    }

    .edit-profile-btn:active {
      transform: translateY(1px);
    }

    @media (max-width:768px) {
      .edit-profile-btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* === Paywall === */
    .locked .post-image img,
    .locked .post-content {
      filter: blur(12px);
      pointer-events: none;
      user-select: none;
    }

    .lock-banner {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      margin: 10px 0 14px;
      background: #fff6f0;
      border: 1px solid #f3d1b8;
      border-radius: 12px;
      font-weight: 800;
      color: #7a4a1c;
    }

    .lock-price {
      margin-left: auto;
      background: #fff;
      border: 1px solid #f3d1b8;
      border-radius: 8px;
      padding: 4px 8px;
      font-weight: 900;
      color: #7a4a1c;
    }

    .lock-icon {
      width: 18px;
      height: 18px;
      display: inline-block;
    }

    /* Kompaktere Kommentar-Sektion */
    .comments-section {
      padding-top: 6px;
      margin-top: 6px;
    }

    .comment {
      gap: 8px;
      padding: 6px 0;
    }

    .comment-bubble {
      padding: 6px 8px;
      border-radius: 10px;
    }

    .comment-header {
      margin-bottom: 4px;
    }

    .comment-user {
      font-size: 0.88rem;
    }

    .comment-time {
      font-size: 0.75rem;
    }

    .comment-text {
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .comments-empty {
      padding: 8px;
      font-size: 0.9rem;
    }

    .comment-form {
      gap: 8px;
      margin-top: 6px;
    }

    .comment-input {
      padding: 6px 8px;
    }

    .comment-input textarea {
      min-height: 36px;
      font-size: 0.9rem;
    }

    .comment-actions {
      margin-top: 4px;
    }

    .btn-send {
      padding: 6px 10px;
      font-size: 0.88rem;
    }

    /* .bio {
      padding: 20px;
      background-color: #fee2e2;
      border-radius: 15px;
    } */


    /* Weniger Weißraum in Karten/Typografie */
    .post-card {
      padding: 14px 16px;
      margin-bottom: 16px;
      border-radius: 22px;
    }

    .post-title {
      margin: 6px 0 8px;
      font-size: 1.25rem;
    }

    .post-content-wrapper {
      margin-bottom: 10px;
    }

    .post-content p {
      margin: 0 0 .6em;
    }

    /* kleinere Absatzabstände */

    /* Read-More/-Less inline */
    .post-readmore,
    .post-readless {
      display: inline;
      /* nicht mehr block */
      margin: 0 0 0 .5em;
      /* kleiner Abstand links */
    }

    /* Optional: Kategoriezeile dezenter + weniger Luft */
    .post-catline {
      margin-top: 4px;
    }

    /* Kommentare-Bereich kompakter */
    .comments-section {
      padding-top: 6px;
      margin-top: 6px;
    }


    /* Immer-farbige Action-Buttons */
    .action-button {
      color: #555;
      /* Grundschrift */
      background: transparent;
      border: none;
    }

    .action-button .action-icon {
      fill: currentColor;
    }

    /* spezifische Farben je Button – dauerhaft */
    .like-button {
      color: #e74c3c;
    }

    .comments-button {
      color: #580F41;
    }

    /* dein Violett */
    .share-button {
      color: #3498db;
    }

    /* Hover-Effekte nur minimal (kein Farbwechsel nötig) */
    .action-button:hover {
      background: #f6f6f6;
      filter: brightness(1.02);
    }

    /* Bereits-geliked darf gern kräftiger sein */
    .like-button.liked {
      color: #c0392b;
      font-weight: 700;
    }

    /* Mehr Platz vor dem Link + nicht umbrechen */
    .post-content a.more-link {
      display: inline-block;
      margin-left: .4em;
      /* <-- Abstand */
      white-space: nowrap;
      text-decoration: none;
    }

    .post-content a.more-link:hover {
      text-decoration: underline;
    }


    /* Rechte Spalte: wird zur vertikalen Sidebar in der Card */
.header-flex-two-right{
  width: 45%;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 12px;
  padding-top: 250px;
  padding-left: 50px;
}

/* Stats untereinander statt Grid */
.stats-grid{
  display: flex;
  flex-direction: column;
  gap: 10px;
}

/* Stat-Karte: modern, kompakt, “Row”-Look */
.stat-card{
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 12px 14px;
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
  transition: transform .12s ease, box-shadow .12s ease;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* optional: dezenter Hover */
.stat-card:hover{
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(0,0,0,.08);
}

/* Value rechts groß, Label links */
.stat-value{
  font-weight: 900;
  font-size: 1.25rem;
  color: #111827;
  line-height: 1;
}

.stat-label{
  font-size: .95rem;
  font-weight: 700;
  color: #374151;
  letter-spacing: .2px;
}

/* Follow-Button als eigene “Card” darunter */
.follow-form{
  margin-top: 2px;
}

.follow-btn{
  width: 100%;
  justify-content: center;
  border-radius: 16px;
  padding: 12px 16px;
  font-size: 1rem;
  font-weight: 800;
  background: linear-gradient(135deg, #6ea173, #6a743a);
  box-shadow: 0 10px 22px rgba(75, 87, 62, 0.22);
}

.follow-btn.is-active{
  background: linear-gradient(135deg, #9ca3af, #6b7280);
  box-shadow: 0 10px 22px rgba(107,114,128, 0.22);
}

.follow-dot{
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: rgba(255,255,255,.95);
  box-shadow: 0 0 0 4px rgba(255,255,255,.25);
}

/* Mobile: rechte Spalte unter die linke setzen */
@media (max-width: 768px){
  .header-flex-one{
    flex-direction: column;
    gap: 12px;
  }
  .header-flex-two-left,
  .header-flex-two-right{
    width: 100%;
  }
  .header-flex-two-right{
    padding-top: 0;
  }
}

/* ===========================
   STATS + FOLLOW (clean final)
   =========================== */

.header-flex-two-right{
  width: 55%;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  gap: 14px;
  padding-top: 195px;     
  padding-left: 42px;
  padding-bottom: 50px;

}

/* ❌ KEIN äusserer Rahmen – endgültig aus */
.stats-panel{
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;
}


.stats-grid{
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
}

.stat-card{
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;

  background: #fff;
  border: 1px solid rgba(229,231,235,.9);
  border-radius: 18px;              /* <- gleichmäßige Ecken */
  padding: 12px 12px;

  box-shadow: 0 6px 16px rgba(0,0,0,.05);
}

.stat-left{
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.stat-icon{
  width: 38px;
  height: 38px;
  border-radius: 14px;              /* <- rund aber nicht "blob" */
  display: grid;
  place-items: center;
  background: rgba(106,116,58,.10);
  border: 1px solid rgba(106,116,58,.18);
}

.stat-icon svg{
  width: 20px;
  height: 20px;
  fill: #6a743a;
}

.stat-text{
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stat-label{
  font-size: .95rem;
  font-weight: 800;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-sub{
  margin-top: 2px;
  font-size: .78rem;
  font-weight: 600;
  color: #6b7280;
}

.stat-value{
  font-weight: 900;
  font-size: 1.25rem;
  color: #111827;
  line-height: 1;

  padding: 8px 12px;
  border-radius: 14px;              /* <- statt "999px pill" */
  background: rgba(17,24,39,.04);
  border: 1px solid rgba(17,24,39,.06);
}

.follow-form{
  margin-top: 12px;
}

.follow-btn{
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;

  border: 0;
  cursor: pointer;
  border-radius: 18px;              /* <- gleiche "Ecken-Sprache" */
  padding: 12px 14px;

  font-size: 1rem;
  font-weight: 900;
  color: #fff;

  background: linear-gradient(135deg, #6ea173, #6a743a);
  box-shadow: 0 14px 26px rgba(75,87,62,.20);
}

.follow-btn.is-active{
  background: linear-gradient(135deg, #9ca3af, #6b7280);
  box-shadow: 0 14px 26px rgba(107,114,128,.20);
}

.follow-btn .btn-icon{
  width: 22px;
  height: 22px;
  border-radius: 10px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.22);
}

@media (max-width: 768px){
  .header-flex-one{
    flex-direction: column;
    gap: 12px;
  }
  .header-flex-two-left,
  .header-flex-two-right{
    width: 100%;
  }
  .header-flex-two-right{
    padding-top: 10px;
    padding-left: 0;
  }
}


:root{
  --sidebar-start: 130px;   /* wie weit UNTER dem Banner starten */
  --sidebar-stick: 130px;   /* wo sie beim Scrollen kleben */
}

/* beide Sidebars gleich */
.side-container{
  margin-top: var(--sidebar-start);  /* <- DAS ist “am Anfang weiter unten” */
  position: sticky;
  top: var(--sidebar-stick);
}

/* WICHTIG: das killt dir sonst alles */
.right-container{
  margin-top: var(--sidebar-start) !important;
  position: sticky !important;
  top: var(--sidebar-stick) !important;
  max-height: calc(100vh - var(--sidebar-stick) - 10px);
  overflow-y: auto;
  padding-right: 2px;
  scrollbar-gutter: stable;
}

/* ===========================
   Q&A -> im Stil der Stats (clean final)
   =========================== */

/* die ganze Fragen-Box wie ein "Panel" */
.questions-card{
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  padding: 0 !important;
  margin: 0 !important;

  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Überschrift wie "Label" (wie Stat-Label) */
.questions-card h3{
  margin: 0;
  padding: 0 2px;
  font-size: 1rem;
  font-weight: 900;
  color: #111827;
}

/* Flash-Messages wie "Value Badge" */
.flash-ok, .flash-err{
  margin: 0;
  padding: 10px 12px;
  border-radius: 14px;
  font-size: .9rem;
  font-weight: 800;

  background: rgba(17,24,39,.04);
  border: 1px solid rgba(17,24,39,.06);
  color: #111827;
}

/* Scrollbereich: ohne “eigene Box”, clean */
.q-scroll{
  max-height: 70vh;
  overflow-y: auto;
  padding-right: 6px;
}

/* jede Frage wie eine Stat-Card */
.qa-item{
  background: #fff;
  border: 1px solid rgba(229,231,235,.9);
  border-radius: 18px;
  padding: 12px 12px;
  box-shadow: 0 6px 16px rgba(0,0,0,.05);
  margin: 0;
}

.post-content a[id^="less-link"]{
  display: block;
  margin-top: 10px;
}

/* ===========================
   Final UI Tweaks (Kategorien / Q&A / iPad Bio)
   =========================== */

.cat-chip {
  border-width: 1.5px;
  box-shadow: 0 1px 0 rgba(0, 0, 0, .02);
}

.cat-chip.active {
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, .08);
  filter: saturate(1.05);
}

.questions-card {
  background: #fff !important;
  border: 1px solid #e4e8e1 !important;
  border-radius: 18px !important;
  box-shadow: 0 14px 28px rgba(19, 27, 16, .09) !important;
  padding: 14px !important;
}

.questions-card h3 {
  font-size: 1.05rem;
  color: #25301f;
}

.q-scroll {
  max-height: 72vh;
  padding-right: 4px;
}

.qa-item {
  margin-bottom: 10px;
}

.qa-empty {
  border: 1px dashed #cfd8c8;
  border-radius: 14px;
  background: linear-gradient(180deg, #fcfdfb, #f6f9f3);
  padding: 14px 12px;
  text-align: center;
}

.qa-empty-icon {
  font-size: 1.35rem;
  line-height: 1;
  margin-bottom: 6px;
}

.qa-empty-title {
  font-size: .92rem;
  font-weight: 800;
  color: #2e3928;
}

.qa-empty-sub {
  margin-top: 4px;
  font-size: .82rem;
  color: #667160;
  line-height: 1.35;
}

.profile-info .bio {
  margin-top: 10px;
  font-size: 1rem;
  line-height: 1.65;
  color: #2f3a2c;
  background: #f8faf6;
  border: 1px solid #e6ece1;
  border-radius: 14px;
  padding: 12px 14px;
  max-width: 62ch;
}

@media (min-width: 768px) and (max-width: 1366px) {
  .header-flex-one {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 14px !important;
  }

  .header-flex-two-left,
  .header-flex-two-right {
    width: 100% !important;
  }

  .header-flex-two-right {
    padding-top: 0 !important;
    padding-left: 0 !important;
    padding-bottom: 0 !important;
  }

  .stats-grid {
    gap: 14px !important;
  }

  .profile-header .profile-info {
    max-width: 100%;
    padding-top: 8px;
  }

  .profile-info .bio {
    width: 100%;
    max-width: 100%;
    font-size: 1.02rem;
    line-height: 1.65;
  }
}

@media (min-width: 1367px) {
  .header-flex-two-right {
    padding-top: 255px !important;
  }
}

/* Seitenleisten mittig zwischen Feed und Rand (wie in platform.php) */
@media (min-width: 769px) {
  .main-content-wrapper {
    display: grid !important;
    grid-template-columns: minmax(240px, 1fr) minmax(0, 900px) minmax(240px, 1fr);
    gap: 22px;
    align-items: start;
    max-width: 1880px;
    padding-left: 10px;
    padding-right: 10px;
  }

  .left-container,
  .right-container {
    justify-self: center;
    width: 100%;
    max-width: 350px;
  }

  .side-container {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .profile-container {
    width: 100%;
    max-width: 900px;
    margin: -95px auto 40px;
    flex: none;
  }
}

/* =======================================
   PROFILE REDESIGN (final visual override)
   ======================================= */
:root {
  --hp-bg: #f4f6f2;
  --hp-surface: #ffffff;
  --hp-border: #e1e8d9;
  --hp-ink: #1f2a1a;
  --hp-muted: #677161;
  --hp-brand: #6a743a;
  --hp-brand-soft: #eaf0df;
}

body {
  background: radial-gradient(circle at 10% -10%, #eef5e6 0%, var(--hp-bg) 55%) !important;
  color: var(--hp-ink);
}

.banner {
  background: linear-gradient(135deg, #6a743a 0%, #7f8a4f 100%) !important;
  border-bottom-left-radius: 42px !important;
  border-bottom-right-radius: 42px !important;
  height: 100px !important;
  box-shadow: 0 14px 30px rgba(63, 79, 45, .22) !important;
}

.profile-container {
  margin-top: -44px !important;
  position: relative;
  z-index: 10;
}

.profile-card-spacer {
  height: 0;
}

.profile-card-top {
  margin: 0 !important;
  padding: 24px 30px !important;
  border: 1px solid var(--hp-border) !important;
  border-radius: 22px !important;
  background: linear-gradient(180deg, #ffffff 0%, #fbfcf9 100%) !important;
  box-shadow: 0 18px 36px rgba(25, 35, 19, .10) !important;
  position: relative;
  z-index: 6;
  display: block !important;
  transition: padding .2s ease, max-width .2s ease, border-radius .2s ease, box-shadow .2s ease;
}

@media (min-width: 769px) {
  .profile-card-top {
    position: sticky !important;
    top: calc(var(--header-h) + 12px);
    z-index: 58;
    box-shadow: 0 20px 42px rgba(25, 35, 19, .16) !important;
    border-color: #d9e2cf !important;
  }

  .profile-card-top.is-compact {
    max-width: 760px;
    margin-left: auto !important;
    margin-right: auto !important;
    padding: 14px 18px !important;
    border-radius: 16px !important;
    box-shadow: 0 14px 28px rgba(25, 35, 19, .18) !important;
  }

  .profile-card-top.is-compact .profile-content-grid {
    grid-template-columns: minmax(0, 1fr) minmax(260px, 1fr);
    gap: 16px;
  }

  .profile-card-top.is-compact .profile-img-wrapper {
    width: 78px !important;
    height: 78px !important;
  }

  .profile-card-top.is-compact .profile-primary {
    gap: 6px;
  }

  .profile-card-top.is-compact .profile-title {
    padding: 5px 12px !important;
    font-size: .95rem !important;
  }

  .profile-card-top.is-compact .profile-username {
    font-size: 1.05rem;
  }

  .profile-card-top.is-compact .profile-primary .profile-keywords,
  .profile-card-top.is-compact .profile-info .profile-keywords-inline,
  .profile-card-top.is-compact .profile-tagline,
  .profile-card-top.is-compact .profile-info .bio,
  .profile-card-top.is-compact .edit-profile-btn {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-header .profile-info {
    border-left: 0;
    padding-left: 0 !important;
  }

  .profile-card-top.is-compact .profile-meta {
    margin-top: 0;
    gap: 4px;
  }

  .profile-card-top.is-compact .profile-meta-row {
    align-items: center;
  }
}

.header-flex-one {
  display: grid !important;
  grid-template-columns: 1fr !important;
  gap: 0 !important;
  align-items: start !important;
}

.header-flex-two-left,
.header-flex-two-right {
  width: auto !important;
}

.header-flex-two-left {
  flex: none !important;
  display: block !important;
  align-items: start;
}

.header-flex-two-right {
  flex: none !important;
  padding: 0 !important;
}

.profile-header {
  margin: 4px 0 0 0 !important;
}

.profile-content-grid {
  display: grid;
  grid-template-columns: minmax(320px, 420px) minmax(0, 1fr);
  gap: 28px;
  align-items: start;
}

.profile-identity {
  display: flex;
  align-items: flex-start;
  gap: 24px;
}

.profile-primary {
  min-width: 0;
  display: grid;
  gap: 12px;
}

.profile-header .profile-info {
  padding-top: 0 !important;
  display: grid;
  gap: 8px;
  border-left: 1px solid #e6ebdf;
  padding-left: 24px !important;
}

.arround-profile-img-wrapper {
  height: auto !important;
  margin: 0 !important;
}

.profile-img-wrapper {
  width: 122px !important;
  height: 122px !important;
}

.profile-img-wrapper img,
.profile-img-wrapper .profile-initials {
  border: 5px solid #fff !important;
  box-shadow: 0 10px 24px rgba(0, 0, 0, .16) !important;
  background: linear-gradient(140deg, #7f8a4f, #6a743a) !important;
}

.profile-title {
  width: auto !important;
  display: inline-block !important;
  padding: 7px 14px !important;
  border-radius: 999px !important;
  font-size: 1.08rem !important;
  color: #fff !important;
  background: linear-gradient(135deg, #758248, #5f6a35) !important;
  box-shadow: 0 10px 20px rgba(85, 100, 54, .24) !important;
}

.profile-username {
  color: #2e3a28 !important;
  font-weight: 700;
  margin: 0 !important;
}

.profile-tagline:empty {
  display: none !important;
}

.profile-keywords {
  margin: 0 !important;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.profile-primary .profile-keywords {
  margin: 6px 0 30px 0 !important;
}

.profile-info .profile-keywords-inline {
  margin: 0 0 10px 0 !important;
}

.profile-meta {
  margin-top: 2px;
  display: grid;
  gap: 10px;
}

.profile-meta p {
  color: #556150 !important;
  font-size: .93rem !important;
  margin: 0 !important;
}

.profile-meta-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 18px;
}

.profile-meta-text {
  display: grid;
  gap: 8px;
}

.profile-info .bio {
  background: #f6f9f1 !important;
  border: 1px solid #e1e8d8 !important;
  border-radius: 14px !important;
  max-width: 100% !important;
  margin: 0 !important;
  padding: 14px 18px !important;
  line-height: 1.55 !important;
}

.stats-panel {
  display: block !important;
  border: 1px solid var(--hp-border);
  border-radius: 18px;
  padding: 10px;
  background: linear-gradient(180deg, #fbfcf9 0%, #f5f8ef 100%);
}

.stats-rail .stats-panel {
  border: none !important;
  background: transparent !important;
  padding: 0 !important;
}

.stats-rail {
  overflow: hidden !important;
}

.stats-rail .stats-grid {
  width: 100%;
  min-width: 0;
}

.stats-rail .stat-card {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  margin: 0 !important;
}

.stats-rail .stat-label {
  white-space: normal !important;
}

.stats-rail .stat-sub {
  white-space: normal;
}

.header-flex-two-right {
  justify-content: flex-start !important;
  padding: 0 !important;
}

.stats-grid {
  display: grid !important;
  grid-template-columns: 1fr !important;
  gap: 8px !important;
}

.stat-card {
  display: flex !important;
  flex-direction: row !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 10px !important;
  min-height: 0;
  padding: 10px 12px !important;
  border-radius: 14px !important;
  border: 1px solid #dfe7d4 !important;
  box-shadow: 0 8px 16px rgba(25, 35, 19, .08) !important;
}

.stat-left {
  width: auto;
  flex: 1;
  align-items: center !important;
}

.stat-text {
  min-width: 0;
}

.stat-label {
  font-size: .95rem !important;
  white-space: nowrap !important;
  line-height: 1.2;
}

.stat-sub {
  margin-top: 2px;
  font-size: .75rem !important;
  line-height: 1.25;
}

.stat-value {
  align-self: center;
  font-size: 1.15rem !important;
  border-radius: 10px !important;
  padding: 6px 10px !important;
  color: #24301e !important;
}

.edit-profile-btn {
  margin-top: 4px !important;
  width: auto !important;
  min-width: 240px;
  align-self: start;
  padding-left: 18px !important;
  padding-right: 18px !important;
}

.follow-btn {
  border-radius: 14px !important;
}

.arround-post {
  margin-top: 16px;
}

.posts-container {
  display: block !important;
  column-count: 2;
  column-gap: 14px;
}

.post-card {
  display: inline-block !important;
  width: 100%;
  margin: 0 0 14px 0 !important;
  border-radius: 18px !important;
  border: 1px solid #e3eadb !important;
  box-shadow: 0 12px 24px rgba(20, 28, 15, .08) !important;
  background: #fff !important;
  break-inside: avoid;
  page-break-inside: avoid;
}

.post-header {
  border-bottom: 1px solid #eff3ea;
  padding-bottom: 10px;
}

.post-title {
  color: #283322 !important;
}

.post-content p {
  color: #2f3b29 !important;
}

.post-actions {
  border-top: 1px solid #eff3ea;
  padding-top: 12px !important;
}

.share-container,
.questions-card {
  border-radius: 18px !important;
  border: 1px solid var(--hp-border) !important;
  box-shadow: 0 12px 24px rgba(20, 28, 15, .08) !important;
  background: #fff !important;
}

@media (max-width: 1024px) {
  .header-flex-one {
    grid-template-columns: 1fr !important;
    gap: 12px !important;
  }

  .posts-container {
    column-count: 1;
    column-gap: 0;
  }

  .stats-grid {
    grid-template-columns: 1fr !important;
  }

  .profile-content-grid {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .profile-header .profile-info {
    border-left: 0;
    padding-left: 0 !important;
  }
}

@media (min-width: 1025px) {
  .posts-container {
    column-count: 2 !important;
    column-gap: 14px !important;
  }
}

/* iPad Air / iPad Portrait: keine Verschiebungen, klare Reihenfolge */
@media (min-width: 768px) and (max-width: 1024px) {
  .main-content-wrapper {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) !important;
    gap: 14px !important;
    max-width: 980px !important;
    padding-left: 14px !important;
    padding-right: 14px !important;
  }

  .profile-container {
    order: 1;
    max-width: 100% !important;
    margin-top: -26px !important;
  }

  .left-container {
    order: 2;
    max-width: 100% !important;
    width: 100% !important;
    position: static !important;
    top: auto !important;
    margin-top: 0 !important;
  }

  .right-container {
    order: 3;
    max-width: 100% !important;
    width: 100% !important;
    position: static !important;
    top: auto !important;
    max-height: none !important;
    overflow: visible !important;
    margin-top: 0 !important;
  }

  .side-container {
    align-items: stretch !important;
  }

  .share-container,
  .questions-card {
    width: 100% !important;
    max-width: 100% !important;
  }

  .profile-card-top {
    position: static !important;
  }

  .profile-card-spacer {
    display: none !important;
  }
}

/* iPad Pro Landscape / große Tablets: 3 Spalten, aber mit genug Platz */
@media (min-width: 1025px) and (max-width: 1366px) {
  .main-content-wrapper {
    grid-template-columns: minmax(220px, 1fr) minmax(0, 900px) minmax(220px, 1fr) !important;
    gap: 18px !important;
    max-width: 1320px !important;
    padding-left: 12px !important;
    padding-right: 12px !important;
  }

  .left-container,
  .right-container {
    max-width: 320px !important;
  }

  .profile-container {
    margin-top: -34px !important;
    max-width: 900px !important;
  }
}

@media (min-width: 769px) {
  .main-content-wrapper {
    grid-template-columns: minmax(230px, 1fr) minmax(0, 980px) minmax(230px, 1fr) !important;
    gap: 26px !important;
  }

  .profile-container {
    max-width: 980px !important;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .main-content-wrapper {
    grid-template-columns: minmax(230px, 1fr) minmax(0, 860px) minmax(230px, 1fr) !important;
    gap: 16px !important;
  }

  .left-container,
  .right-container {
    max-width: 300px !important;
  }
}

@media (max-width: 768px) {
  .profile-container {
    margin-top: -28px !important;
    padding: 0 12px !important;
  }

  .profile-card-top {
    position: static !important;
    padding: 14px 16px !important;
    border-radius: 16px !important;
  }

  .header-flex-one {
    grid-template-columns: 1fr !important;
  }

  .header-flex-two-left {
    display: block !important;
  }

  .profile-identity {
    flex-direction: column;
    gap: 10px;
  }

  .profile-content-grid {
    gap: 10px;
  }

  .profile-meta-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .profile-img-wrapper {
    width: 104px !important;
    height: 104px !important;
  }

  .profile-title {
    font-size: .98rem !important;
  }

  .stats-grid {
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }

  .posts-container {
    column-count: 1;
    column-gap: 0;
  }

  .post-card {
    border-radius: 14px !important;
  }
}

/* Final fix: weniger horizontal gestreckt + Action-Texte ausblenden */
@media (min-width: 1025px) {
  .main-content-wrapper {
    max-width: 1360px !important;
    grid-template-columns: minmax(210px, 260px) minmax(0, 820px) minmax(210px, 260px) !important;
    gap: 14px !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
  }

  .profile-container {
    max-width: 820px !important;
  }
}

.action-label {
  display: none !important;
}

.post-actions {
  justify-content: space-around !important;
}

.action-button {
  gap: 6px !important;
}

/* Hotfix: Header darf nicht mehr in die Sidebar auslaufen */
@media (min-width: 1025px) {
  .profile-card-top {
    overflow: hidden !important;
  }

  .profile-content-grid {
    grid-template-columns: 1fr !important;
    gap: 14px !important;
  }

  .profile-header .profile-info {
    border-left: 0 !important;
    padding-left: 0 !important;
  }

  .profile-meta-row {
    flex-wrap: wrap !important;
    align-items: flex-start !important;
  }

  .edit-profile-btn {
    min-width: 0 !important;
    max-width: 100% !important;
    width: auto !important;
  }

  /* Scroll-Compact: Ort/Sprache nach rechts ziehen, damit rechts nicht leer bleibt */
  .profile-card-top.is-compact .profile-content-grid {
    grid-template-columns: minmax(0, 1fr) 230px !important;
    gap: 14px !important;
    align-items: start !important;
  }

  .profile-card-top.is-compact .profile-meta-row {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 6px !important;
    justify-items: start !important;
    margin-top: 0 !important;
  }

  .profile-card-top.is-compact .edit-profile-btn {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-meta-text {
    justify-self: end !important;
    text-align: left !important;
    width: 100% !important;
    max-width: 230px !important;
    background: #f7faF3 !important;
    border: 1px solid #e2e9d9 !important;
    border-radius: 12px !important;
    padding: 10px 12px !important;
  }

  .profile-card-top.is-compact .profile-meta-text p {
    margin: 0 0 6px 0 !important;
    line-height: 1.35 !important;
    font-size: .88rem !important;
  }

  .profile-card-top.is-compact .profile-meta-text p:last-child {
    margin-bottom: 0 !important;
  }
}




  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <a href="platform.php" class="brand" aria-label="Humplore - Startseite">
      <img src="/pic/humplore-logo.png" alt="humplore Logo">
    </a>
  </header>

  <div class="banner"></div>

  <div class="main-content-wrapper">
    <!-- Linke Seitenleiste -->
    <div class="side-container left-container">
      <div class="questions-card">
        <?php if ($is_own_profile): ?>
          <h3>Fragen an dich</h3>
          <?php if (!empty($answer_success)): ?>
            <div class="flash-ok"><?= htmlspecialchars($answer_success) ?></div>
          <?php endif; ?>
          <div class="q-scroll">
            <?php if (empty($questions)): ?>
              <div class="qa-empty">
                <div class="qa-empty-icon" aria-hidden="true">&#10067;</div>
                <div class="qa-empty-title">Noch keine Fragen</div>
                <div class="qa-empty-sub">Sobald dir jemand schreibt, erscheinen die Fragen hier.</div>
              </div>
            <?php else:
              foreach ($questions as $q): ?>
                <div class="qa-item">
                  <div class="meta">
                    Von <strong>@<?= htmlspecialchars($q['author_name']) ?></strong>
                    &middot; <?= htmlspecialchars(date('d.m.Y H:i', strtotime($q['created_at']))) ?>
                    &middot; <?= (int) $q['like_count'] ?>
                  </div>
                  <div class="q">Q: <?= htmlspecialchars($q['question_text']) ?></div>
                  <?php if (!empty($q['answer_text'])): ?>
                    <div class="a">A: <?= nl2br(htmlspecialchars($q['answer_text'])) ?></div>
                    <?php if (!empty($q['answered_at'])): ?>
                      <div class="meta">beantwortet am <?= htmlspecialchars(date('d.m.Y H:i', strtotime($q['answered_at']))) ?>
                      </div>
                    <?php endif; ?>
                  <?php else: ?>
                    <form method="post" style="margin-top:8px;">
                      <input type="hidden" name="action" value="answer_question">
                      <input type="hidden" name="question_id" value="<?= (int) $q['id'] ?>">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                      <textarea name="answer_text" rows="3" placeholder="Antwort eingeben ..." required></textarea>
                      <button type="submit">Antwort senden</button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; endif; ?>
          </div>

        <?php else: ?>
          <h3 style="color: black;">Frage an <?= htmlspecialchars($user['username']) ?></h3>
          <?php if (!empty($ask_error)): ?>
            <div class="flash-err"><?= htmlspecialchars($ask_error) ?></div><?php endif; ?>
          <?php if (!empty($ask_success)): ?>
            <div class="flash-ok"><?= htmlspecialchars($ask_success) ?></div><?php endif; ?>

          <form method="post" action="profile.php?user_id=<?= (int) $profile_user_id ?>">
            <input type="hidden" name="action" value="ask_question">
            <input type="hidden" name="creator_id" value="<?= (int) $profile_user_id ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <textarea name="question_text" rows="4" placeholder="Stell deine Frage ..." required></textarea>
            <button type="submit">Absenden</button>
          </form>

          <?php
          $answered_preview = array_values(array_filter($questions, function ($q) {
            return !empty($q['answer_text']);
          }));
          if (!empty($answered_preview)):
            $preview_slice = array_slice($answered_preview, 0, 5); ?>
            <div class="q-scroll" style="margin-top:8px;">
              <div style="font-weight:600; margin-bottom:6px;">Kürzlich beantwortet</div>
              <?php foreach ($preview_slice as $q): ?>
                <div class="qa-item">
                  <div class="q">Q: <?= htmlspecialchars($q['question_text']) ?></div>
                  <div class="a">A: <?= nl2br(htmlspecialchars($q['answer_text'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="qa-empty" style="margin-top:10px;">
              <div class="qa-empty-icon" aria-hidden="true">&#128172;</div>
              <div class="qa-empty-title">Noch keine beantworteten Fragen</div>
              <div class="qa-empty-sub">Stell die erste Frage und starte den Austausch.</div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hauptprofilbereich -->
    <div class="profile-container">
      <div class="profile-card-top">
        <div class="header-flex-one">
          <!-- Linke Spalte -->
          <div class="header-flex-two-left">
            <?php if ($isCreator): ?>
              <div class="profile-header">
                <div class="profile-content-grid">
                  <div class="profile-identity">
                    <div class="arround-profile-img-wrapper">
                      <div class="profile-img-wrapper">
                        <?php if (!empty($user['has_profile_image'])): ?>
                          <img src="<?= htmlspecialchars(profile_img_src((int) $profile_user_id), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilbild">
                        <?php else: ?>
                          <div class="profile-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="profile-primary">
                    <h3 style="padding-bottom:0;color:rgb(141,141,141)">Thema:</h3>
                    <h2 class="profile-title"><?= htmlspecialchars($profileTitle) ?></h2>
                    <p class="profile-username"><?= htmlspecialchars($profileUsername) ?></p>
                    </div>
                  </div>

                  <div class="profile-info">
                    <p class="profile-tagline"><?= htmlspecialchars($profileTagline) ?></p>

                    <?php if (!empty($profileKeywords)): ?>
                      <div class="profile-keywords profile-keywords-inline">
                        <?php foreach ($profileKeywords as $kw): ?>
                          <span><?= htmlspecialchars($kw['keyword']) ?></span>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>

                    <div class="profile-meta">
                      <p class="bio"><?= htmlspecialchars($profileBio) ?></p>
                      <div class="profile-meta-row">
                        <div class="profile-meta-text">
                          <p><strong>Ort:</strong> <?= htmlspecialchars($profileLocation) ?></p>
                          <p><strong>Sprache:</strong> <?= htmlspecialchars($profileLanguages) ?></p>
                        </div>
                        <?php if ($is_own_profile): ?>
                          <button type="button" class="edit-profile-btn" onclick="openModal()" aria-haspopup="dialog"
                            aria-controls="settingsModal" aria-label="Profil bearbeiten">
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                              <path fill="currentColor"
                                d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm8.94-2.88-1.7-.98c.08-.5.08-1.05 0-1.55l1.7-.98a.75.75 0 0 0 .27-1.02l-1.6-2.77a.75.75 0 0 0-.95-.33l-1.9.77a6.9 6.9 0 0 0-1.35-.78l-.29-2.05A.75.75 0 0 0 12.5 1h-3a.75.75 0 0 0-.74.64l-.29 2.05c-.47.2-.92.46-1.35.78l-1.9-.77a.75.75 0 0 0-.95.33L2.38 6.8a.75.75 0 0 0 .27 1.02l1.7.98c-.08.5-.08 1.05 0 1.55l-1.7.98a.75.75 0 0 0-.27 1.02l1.6 2.77c.2.35.62.5.98.35l1.9-.77c.43.32.88.58 1.35.78l.29 2.05c.06.37.37.64.74.64h3c.37 0 .68-.27.74-.64l.29-2.05c.47-.2.92-.46 1.35-.78l1.9.77c.36.15.78 0 .98-.35l1.6-2.77a.75.75 0 0 0-.27-1.02Z" />
                            </svg>
                            <span>Profil bearbeiten</span>
                          </button>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            <?php endif; ?>
          </div>

        </div>
      </div>
      <div class="profile-card-spacer" id="profileCardSpacer"></div>

        <div class="arround-post">
          <!-- Kategorien-Filterbar -->
          <!-- <?php if (!empty($allCategories)): ?>
            <div class="category-bar">
              <a class="cat-chip <?= $activeCatSlug === '' ? 'active' : '' ?>"
                href="profile.php?user_id=<?= (int) $profile_user_id ?>">Alle</a>
              <?php foreach ($allCategories as $cat): ?>
                <a class="cat-chip <?= ($activeCatSlug === $cat['slug']) ? 'active' : '' ?>"
                  style="<?= htmlspecialchars(category_chip_style((string) $cat['slug']), ENT_QUOTES, 'UTF-8') ?>"
                  href="profile.php?user_id=<?= (int) $profile_user_id ?>&cat=<?= urlencode($cat['slug']) ?>">
                  <?= htmlspecialchars($cat['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?> -->

          <div class="posts-container">
            <?php
            if ($posts) {
              foreach ($posts as $post) {

                $viewerId = $viewerUserId;
                $unlocked = post_has_access($post, $viewerId);
                $cardClass = $unlocked ? '' : ' locked';
                $priceLabel = isset($post['price_cents']) ? formatEuroCents($post['price_cents']) : '';

                // Like-Infos (bulk vorberechnet)
                $postId = (int) $post['id'];
                $likeCount = (int) ($likeCountsByPost[$postId] ?? 0);
                $hasLiked = !empty($likedByViewer[$postId]);

                // Kommentare (bulk vorberechnet)
                $comments = $commentsByPost[$postId] ?? [];
                $commentCount = count($comments);

                // Inhalt splitten
                // 2) Text normalisieren (Absatz-Logik vorbereiten)
                $raw = (string) ($post['content'] ?? '');
                $raw = str_replace(["\r\n", "\r"], "\n", $raw);   // Zeilenenden normalisieren
                $raw = preg_replace("/[ \t]+$/m", "", $raw);      // Leerzeichen am Zeilenende kappen
                $raw = preg_replace("/\n{3,}/", "\n\n", trim($raw)); // 3+ Leerzeilen -> genau 1 Leerzeile (= Absatz)
          
                // Vorschau schneiden
                $limit = 220;
[$pv, $rs] = smart_split($raw, $limit);

                $pid = (int) $post['id'];

                ?>
                <div class="post-card<?= $cardClass ?>" id="post-<?= (int) $post['id'] ?>">

                  <div class="post-header">

                    <div class="post-header-img">
                      <?php if (!empty($user['has_profile_image'])): ?>
                        <img src="<?= htmlspecialchars(profile_img_src((int) $profile_user_id), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilbild">
                      <?php else: ?>
                        <div class="profile-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                      <?php endif; ?>
                    </div>

                    <div class="post-header-info">
                      <span class="post-author">@<?= htmlspecialchars($post['username']) ?></span>
                      <span class="post-date"><?= date("d.m.Y H:i", strtotime($post['created_at'])) ?></span>
                    </div>

                    <?php if ($is_own_profile && (int) $post['creator_id'] === $viewerUserId): ?>
                      <div class="post-menu">
                        <button class="menu-trigger" type="button" aria-haspopup="true" aria-expanded="false"
                          aria-controls="menu-<?= (int) $post['id'] ?>"
                          onclick="togglePostMenu(event,'<?= (int) $post['id'] ?>')" title="Menü">
                          <!-- 3 Punkte Icon -->
                          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <circle cx="5" cy="12" r="2"></circle>
                            <circle cx="12" cy="12" r="2"></circle>
                            <circle cx="19" cy="12" r="2"></circle>
                          </svg>
                        </button>

                        <div class="menu-dropdown" id="menu-<?= (int) $post['id'] ?>" role="menu">
                          <!-- (optional) Weitere Aktionen
        <button class="menu-item" type="button" onclick="/* TODO: editPost(...) */">
          ✏️ <span>Bearbeiten</span>
        </button>
        -->
                          <form method="post" onsubmit="return confirmDelete(<?= (int) $post['id'] ?>)" style="margin:0;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="delete_post" value="1">
                            <input type="hidden" name="delete_post_id" value="<?= (int) $post['id'] ?>">
                            <button type="submit" class="menu-item danger" role="menuitem">
                              <span>Löschen</span>
                            </button>
                          </form>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($post['cat_list'])): ?>
                    <div class="post-catline">
                      <?php
                      $cats = preg_split('/\s+[•·|]\s+/u', (string) $post['cat_list']) ?: [(string) $post['cat_list']];
                      foreach ($cats as $name):
                        $name = trim($name);
                        if ($name === '')
                          continue;
                        ?>
                        <span class="cat-pill"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>


                  <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>



                  <?php if (!$unlocked && (int) $post['is_paid'] === 1): ?>
                    <div class="lock-banner" role="note" aria-label="Beitrag ist kostenpflichtig">
                      <svg class="lock-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="currentColor"
                          d="M12 2a5 5 0 00-5 5v3H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 8V7a3 3 0 116 0v3H9z" />
                      </svg>
                      <span>Gesperrter Inhalt</span>
                      <?php if ($priceLabel): ?>
                        <span class="lock-price"><?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?></span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>

                  <div class="post-content-wrapper">
                    <?php if (!empty($post['has_media_image'])): ?>
                      <div class="post-image">
                        <img src="<?= htmlspecialchars(post_img_src((int) $post['id']), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Beitragsbild">
                      </div>
                    <?php endif; ?>

                    <?php /*if (!empty($post['cat_list'])): ?>
                              <div class="post-catline">Kategorien: <?= htmlspecialchars($post['cat_list']) ?></div>
                            <?php endif; */ ?>

                    <?php
                    // ... oberhalb hast du bereits $raw normalisiert und $pv/$rs/$pid gebaut
                    $hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1); // Leerzeilen vorhanden?
              
                    // Absätze nur bei Leerzeile; einfache Zeilenumbrüche -> Leerzeichen
                    $renderParagraphs = function (string $txt) {
                      $txt = str_replace(["\r\n", "\r"], "\n", $txt);
                      $blocks = preg_split("/\n\s*\n/", $txt);
                      foreach ($blocks as $p) {
                        $p = trim(preg_replace("/\n+/", " ", $p)); // Einzel-\n zu Leerzeichen
                        if ($p === '')
                          continue;
                        echo '<p>' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</p>';
                      }
                    };
                    ?>

                    <div class="post-content" id="post-content-<?= $pid ?>">
                      <?php if (!$hasParagraphs): ?>
                        <!-- KEINE Leerzeilen: ein einziger Absatz -->
                        <p>
                          <?= htmlspecialchars(preg_replace("/[\r\n]+/", " ", $pv), ENT_QUOTES, 'UTF-8') ?>
                          <?php if (txt_len($raw) > $limit): ?>
                            <span class="more-content" id="more-<?= $pid ?>" style="display:none">
                              <?= htmlspecialchars(preg_replace("/[\r\n]+/", " ", $rs), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                          <?php endif; ?>
                        </p>
                      <?php else: ?>
                        <!-- MIT Leerzeilen: echte Absätze -->
                        <?php $renderParagraphs($pv); ?>
                        <?php if (txt_len($raw) > $limit): ?>
                          <div class="more-content" id="more-<?= $pid ?>" style="display:none">
                            <?php $renderParagraphs($rs); ?>
                          </div>
                        <?php endif; ?>
                      <?php endif; ?>

                      <?php if (txt_len($raw) > $limit): ?>
                        <div class="post-readmore" id="more-row-<?= $pid ?>">
  ... <a href="#" id="more-link-<?= $pid ?>" class="more-link" onclick="toggleMore('<?= $pid ?>', event)">mehr lesen</a>
</div>
<div class="post-readless" id="less-row-<?= $pid ?>" style="display:none">
  <a href="#" id="less-link-<?= $pid ?>" class="more-link" onclick="toggleMore('<?= $pid ?>', event)">weniger anzeigen</a>
</div>

                      <?php endif; ?>
                    </div>



                  </div>

                  <div class="post-actions">
                    <form method="post" class="post-action">
                      <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                      <button type="button" class="action-button like-button <?= $hasLiked ? 'liked' : '' ?>"
                        data-post-id="<?= (int) $post['id'] ?>" onclick="toggleLike(this)">
                        <svg class="action-icon" viewBox="0 0 24 24">
                          <path
                            d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                        <span class="action-count like-count"><?= (int) $likeCount ?></span>
                        <span class="action-label">Wissenswert</span>
                      </button>
                    </form>

                    <button class="action-button comments-button" onclick="toggleComments(<?= (int) $post['id'] ?>)">
                      <svg class="action-icon" viewBox="0 0 24 24">
                        <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
                      </svg>
                      <span class="action-count comment-count"><?= (int) $commentCount ?></span>
                      <span class="action-label">Kommentar</span>
                    </button>

                    <button class="action-button share-button" onclick="sharePost(<?= (int) $post['id'] ?>)">
                      <svg class="action-icon" viewBox="0 0 24 24">
                        <path
                          d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92z" />
                      </svg>
                      <span class="action-label">Teilen</span>
                    </button>
                  </div>

                  <div class="comments-section" id="comments-<?= (int) $post['id'] ?>">
                    <?php if (empty($comments)): ?>
                      <div class="comments-empty">Noch keine Kommentare - sei die/der Erste</div>
                    <?php else: ?>
                      <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                          <div class="comment-avatar">
                            <?php
                            // Wenn du Avatare in Users hast, hier laden:
                            // echo '<img src="data:image/jpeg;base64,' . base64_encode($comment['profile_image']) . '" alt="Avatar">';
                            // Fallback: Initiale
                            echo strtoupper(substr($comment['username'], 0, 1));
                            ?>
                          </div>
                          <div class="comment-bubble">
                            <div class="comment-header">
                              <span class="comment-user">@<?= htmlspecialchars($comment['username']) ?></span>
                              <span class="comment-time"><?= date("d.m.Y H:i", strtotime($comment['created_at'])) ?></span>
                            </div>
                            <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($viewerUserId > 0): ?>
                      <form method="post" class="comment-form">
                        <div class="me-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                        <div class="comment-input">
                          <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                          <textarea name="comment_text" placeholder="Schreibe einen freundlichen Kommentar ..."
                            required></textarea>
                          <div class="comment-actions">
                            <button type="submit" class="btn-send">Senden</button>
                          </div>
                        </div>
                      </form>
                    <?php endif; ?>
                  </div>

                </div>
                <?php
              } // foreach posts
            } else {
              echo "<p>Noch keine Beiträge vorhanden.</p>";
            }
            ?>

            <?php if ($postsTotalPages > 1): ?>
              <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin:14px 0 6px;">
                <?php
                $baseParams = ['user_id' => (int) $profile_user_id];
                if ($activeCatSlug !== '') {
                  $baseParams['cat'] = $activeCatSlug;
                }
                if ($postsPage > 1):
                  $prevParams = $baseParams;
                  $prevParams['page'] = $postsPage - 1;
                  ?>
                  <a class="cat-chip" href="profile.php?<?= htmlspecialchars(http_build_query($prevParams), ENT_QUOTES, 'UTF-8') ?>">Zurück</a>
                <?php endif; ?>

                <span class="cat-chip active" style="cursor:default;">Seite <?= (int) $postsPage ?> / <?= (int) $postsTotalPages ?></span>

                <?php if ($postsPage < $postsTotalPages):
                  $nextParams = $baseParams;
                  $nextParams['page'] = $postsPage + 1;
                  ?>
                  <a class="cat-chip" href="profile.php?<?= htmlspecialchars(http_build_query($nextParams), ENT_QUOTES, 'UTF-8') ?>">Weiter</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
    </div>

    <!-- Rechte Seitenleiste -->
    <div class="side-container right-container">
      <div class="share-container stats-rail">
        <h3 style="color: black;">Profilzahlen</h3>
        <div class="stats-panel">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-left">
                <span class="stat-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5S14.34 11 16 11zm-8 0c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45v2h7v-2c0-2.66-5.33-4-8-4z"/></svg>
                </span>
                <div class="stat-text">
                  <div class="stat-label">Follower</div>
                  <div class="stat-sub">Personen folgen</div>
                </div>
              </div>
              <div class="stat-value"><?= (int) $followerCount ?></div>
            </div>

            <div class="stat-card">
              <div class="stat-left">
                <span class="stat-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                </span>
                <div class="stat-text">
                  <div class="stat-label">Abonnenten</div>
                  <div class="stat-sub">Aktive Abos</div>
                </div>
              </div>
              <div class="stat-value"><?= (int) $subscriberCount ?></div>
            </div>

            <div class="stat-card">
              <div class="stat-left">
                <span class="stat-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM8 12h8v2H8v-2zm0 4h8v2H8v-2zm6-10 4 4h-4V6z"/></svg>
                </span>
                <div class="stat-text">
                  <div class="stat-label">Beiträge</div>
                  <div class="stat-sub">Veröffentlicht</div>
                </div>
              </div>
              <div class="stat-value"><?= (int) $postsCount ?></div>
            </div>
          </div>

          <?php if ($viewerUserId > 0 && !$is_own_profile): ?>
            <form method="post" class="follow-form">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
              <?php if ($isFollowing): ?>
                <button type="submit" name="follow_action" value="unfollow" class="follow-btn is-active" aria-label="Entfolgen">
                  <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM7 10H1v2h6v-2zm8 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </span>
                  <span>Entfolgen</span>
                </button>
              <?php else: ?>
                <button type="submit" name="follow_action" value="follow" class="follow-btn" aria-label="Folgen">
                  <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zM7 10H5V8H3v2H1v2h2v2h2v-2h2v-2zm8 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                  </span>
                  <span>Folgen</span>
                </button>
              <?php endif; ?>
            </form>
          <?php endif; ?>
        </div>
      </div>

      <div class="share-container">
        <h3 style="color: black;">Teile dieses Profil</h3>
        <div class="share-link-container">
          <input type="text" id="profileLinkInput" value="<?= htmlspecialchars($profileLink) ?>" readonly>
          <button onclick="copyProfileLink()">Kopieren</button>
        </div>
        <p id="copyConfirmation" class="copy-confirmation">&#10003; Link kopiert!</p>
      </div>

      <!-- Kategorien-Filterbar -->
      <?php if (!empty($allCategories)): ?>
        <div class="share-container">
          <h3 style="color: black;">Kategorien</h3>
          <div class="category-bar" style="flex-wrap:wrap;">
            <a class="cat-chip <?= $activeCatSlug === '' ? 'active' : '' ?>"
              href="profile.php?user_id=<?= (int) $profile_user_id ?>">Alle</a>
            <?php foreach ($allCategories as $cat): ?>
              <a class="cat-chip <?= ($activeCatSlug === $cat['slug']) ? 'active' : '' ?>"
                style="<?= htmlspecialchars(category_chip_style((string) $cat['slug']), ENT_QUOTES, 'UTF-8') ?>"
                href="profile.php?user_id=<?= (int) $profile_user_id ?>&cat=<?= urlencode($cat['slug']) ?>">
                <?= htmlspecialchars($cat['name']) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>




    <?php if ($is_own_profile): ?>
      <!-- Overlay + Modal (sauberer Markup) -->
      <div class="modal-overlay" id="overlay" aria-hidden="true"></div>

      <div class="modal" id="settingsModal" role="dialog" aria-modal="true" aria-labelledby="settingsTitle">
        <div class="modal__header">
          <div class="modal__title" id="settingsTitle"> Profil bearbeiten</div>
          <button type="button" class="modal__close" id="modalCloseBtn" aria-label="Schließen">
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor">
              <path
                d="M18.3 5.7a1 1 0 0 0-1.4-1.4L12 9.17 7.1 4.3A1 1 0 1 0 5.7 5.7L10.6 10.6 5.7 15.5a1 1 0 1 0 1.4 1.4L12 12.03l4.9 4.87a1 1 0 0 0 1.4-1.42l-4.88-4.88 4.88-4.9Z" />
            </svg>
          </button>
        </div>

        <div class="modal__body">
          <form method="post" action="profile.php?user_id=<?= (int) $profile_user_id ?>" enctype="multipart/form-data"
            id="profileForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="settings-grid">
              <!-- Linke Spalte: Avatar -->
              <div class="avatar-card">
                <div class="avatar-preview" id="avatarPreview">
                  <?php if (!empty($user['has_profile_image'])): ?>
                    <img src="<?= htmlspecialchars(profile_img_src((int) $profile_user_id), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilvorschau">
                  <?php else: ?>
                    <div class="avatar-initials" style="font-size:2.2rem;">
                      <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                  <?php endif; ?>
                </div>

                <label for="imageUpload">Profilbild</label>
                <input type="file" id="imageUpload" name="profile_image" accept="image/*" style="width:100%;">

                <div class="dropzone" id="dropzone">Bild hierher ziehen oder klicken</div>
                <div class="upload-hint">Unterstützt: JPG/PNG/WebP/GIF &middot; max. 5&nbsp;MB</div>
              </div>

              <!-- Rechte Spalte: Bio -->
              <div>
                <div class="form-field">
                  <label for="bioInput">Bio</label>
                  <textarea id="bioInput" name="bio" rows="6" maxlength="300"
                    placeholder="Erzähl etwas über dich..."><?= htmlspecialchars($profileBio) ?></textarea>
                  <div class="bio-meta">
                    <span>Max. 300 Zeichen</span>
                    <span id="bioCount">0/300</span>
                  </div>
                  <div class="bio-progress"><span id="bioProgressBar"></span></div>
                </div>

                <!-- (Optional) weitere Felder? Ort/Sprache etc. könnten hier später rein -->
              </div>
            </div>

            <div class="modal__footer">
              <button type="button" class="close-btn" id="modalCloseBtn2">Schließen</button>
              <button type="submit" name="save_profile" class="save-btn">Speichern</button>
            </div>
          </form>
        </div>

      </div>
    <?php endif; ?>



    <script>

      const csrfToken = "<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>";

      // Profillink kopieren
      function copyProfileLink() {
        const copyText = document.getElementById("profileLinkInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        const confirmation = document.getElementById("copyConfirmation");
        confirmation.style.display = "block";
        setTimeout(() => { confirmation.style.display = "none"; }, 2000);
      }

      function toggleComments(postId) {
        const el = document.getElementById(`comments-${postId}`);
        const isOpen = el.classList.contains('open');
        if (isOpen) {
          // schließen
          el.style.maxHeight = el.scrollHeight + 'px'; // Startwert setzen
          requestAnimationFrame(() => {
            el.classList.remove('open');
            el.style.maxHeight = '0px';
          });
        } else {
          // öffnen
          el.classList.add('open');
          el.style.maxHeight = el.scrollHeight + 'px';
          // nach der Animation Höhe wieder "auto" lassen
          setTimeout(() => { el.style.maxHeight = '1000px'; }, 310);
        }
      }

      // Auto-Resize für Kommentar-Textareas (optional, nice UX)
      function autosizeTextarea(ta) {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
      }
      document.addEventListener('input', e => {
        if (e.target.matches('.comment-input textarea')) autosizeTextarea(e.target);
      });
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.comment-input textarea').forEach(autosizeTextarea);
      });

      // ===== Modal: öffnen/schließen + Fokus-Management =====
      let lastFocusedElement = null;
      const modal = document.getElementById("settingsModal");
      const overlayEl = document.getElementById("overlay");
      const closeBtn = document.getElementById("modalCloseBtn");
      const closeBtn2 = document.getElementById("modalCloseBtn2");

      function trapFocus(e) {
        if (!document.body.classList.contains('modal-open')) return;
        const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.key === 'Tab') {
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        } else if (e.key === 'Escape') {
          closeModal();
        }
      }

      function openModal() {
        lastFocusedElement = document.activeElement;
        document.body.classList.add('no-scroll', 'modal-open');
        overlayEl.setAttribute('aria-hidden', 'false');
        // Fokus auf Titel
        const title = modal.querySelector('#settingsTitle');
        if (title) { title.setAttribute('tabindex', '-1'); title.focus(); }
        document.addEventListener('keydown', trapFocus);
      }

      function closeModal() {
        document.body.classList.remove('no-scroll', 'modal-open');
        overlayEl.setAttribute('aria-hidden', 'true');
        document.removeEventListener('keydown', trapFocus);
        if (lastFocusedElement) lastFocusedElement.focus();
      }

      // Buttons/Overlay
      overlayEl?.addEventListener('click', (e) => { if (e.target === overlayEl) closeModal(); });
      closeBtn?.addEventListener('click', closeModal);
      closeBtn2?.addEventListener('click', closeModal);


      // Overlay-Klick schließt Modal
      if (overlayEl) {
        overlayEl.addEventListener('click', (e) => {
          if (e.target === overlayEl) closeModal();
        });
      }

      // ===== Live-Preview für Profilbild + Validierung =====
      function previewImage(input) {
        const file = input.files && input.files[0];
        if (!file) return;

        const maxBytes = 5 * 1024 * 1024; // 5 MB
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!allowed.includes(file.type)) {
          alert('Bitte ein Bild im Format JPG/PNG/WebP/GIF wählen.');
          input.value = '';
          return;
        }
        if (file.size > maxBytes) {
          alert('Die Datei ist zu groß (max. 5 MB).');
          input.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
          const preview = document.getElementById('avatarPreview');
          // vorhandenes Bild/Initialen entfernen
          preview.innerHTML = '';
          const img = document.createElement('img');
          img.src = e.target.result;
          img.alt = 'Profilbild Vorschau';
          preview.appendChild(img);
        };
        reader.readAsDataURL(file);
      }

      // ===== Bio-Zeichenzähler =====
      function updateBioCount() {
        const ta = document.getElementById('bioInput');
        const count = document.getElementById('bioCount');
        if (!ta || !count) return;
        count.textContent = ta.value.length.toString();
      }
      document.addEventListener('DOMContentLoaded', updateBioCount);

      document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.post-content').forEach((box) => {
    const pid = box.id.replace('post-content-', '');

    const moreRow   = document.getElementById(`more-row-${pid}`);
    const lessRow   = document.getElementById(`less-row-${pid}`);
    const moreLink  = document.getElementById(`more-link-${pid}`);
    const lessLink  = document.getElementById(`less-link-${pid}`);
    const moreBlock = document.getElementById(`more-${pid}`);

    if (!moreRow || !lessRow || !moreLink || !lessLink || !moreBlock) return;

    // ✅ Nur sichtbare <p> (nicht innerhalb .more-content)
    const visibleParas = Array.from(box.querySelectorAll('p'))
      .filter(p => !p.closest('.more-content'));

    const lastPara = visibleParas.length ? visibleParas[visibleParas.length - 1] : null;
    if (!lastPara) return;

    // Ellipse + Links in den letzten sichtbaren Absatz
    const ell = document.createElement('span');
    ell.id = `ellipsis-${pid}`;
    ell.textContent = ' ... ';
    lastPara.appendChild(ell);

    lastPara.appendChild(moreLink);
    box.appendChild(lessLink);

    // Wrapper-Zeilen weg
    moreRow.remove();
    lessRow.remove();

    // Startzustand
    moreLink.style.display = '';
    lessLink.style.display = 'none';
  });
});



      // toggleMore anpassen: steuert jetzt direkt die verschobenen Links
     function toggleMore(id, ev) {
  ev.preventDefault();
  const more = document.getElementById('more-' + id);
  const moreLink = document.getElementById('more-link-' + id);
  const lessLink = document.getElementById('less-link-' + id);

  if (!more || !moreLink || !lessLink) return;

  const isHidden = (more.style.display === '' || more.style.display === 'none');

  if (isHidden) {
    more.style.display = (more.tagName === 'SPAN') ? 'inline' : 'block';
    moreLink.style.display = 'none';
    lessLink.style.display = '';
  } else {
    more.style.display = 'none';
    moreLink.style.display = '';
    lessLink.style.display = 'none';
  }
}





      // Like per AJAX (dein like_handler.php)
      function toggleLike(button) {
        const postId = button.getAttribute('data-post-id');

        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('csrf_token', csrfToken);  // WICHTIG

        fetch('like_handler.php', {
          method: 'POST',
          body: formData
          // alternativ oder zusätzlich:
          // headers: { 'X-CSRF-Token': csrfToken }
        })
          .then(r => r.json())
          .then(data => {
            if (!data.success) {
              console.error('Like-Fehler:', data.error);
              return;
            }
            const likeCountEl = button.querySelector('.like-count');
            if (likeCountEl) {
              likeCountEl.textContent = data.likeCount;
            }

            if (data.liked) {
              button.classList.add('liked');
            } else {
              button.classList.remove('liked');
            }
          })
          .catch(err => {
            console.error('Fetch-Error:', err);
          });
      }


      // Teilen
      function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
          navigator.clipboard.writeText(text);
        } else {
          const ta = document.createElement('textarea');
          ta.value = text; document.body.appendChild(ta); ta.select();
          try { document.execCommand('copy'); } catch (e) { }
          document.body.removeChild(ta);
        }
      }
      function showToast(message) {
        let toast = document.getElementById('toast');
        if (!toast) { toast = document.createElement('div'); toast.id = 'toast'; document.body.appendChild(toast); }
        toast.textContent = message; toast.className = 'toast toast--show';
        setTimeout(() => toast.className = 'toast', 1800);
      }
      function sharePost(postId) {
        const url = `${location.origin}/profile.php?user_id=<?= (int) $profile_user_id ?>&post_id=${postId}`;
        if (navigator.share) {
          navigator.share({ title: 'Beitrag ansehen', text: 'Schau dir diesen Beitrag auf humplore an.', url })
            .catch(() => { });
          return;
        }
        copyToClipboard(url);
        showToast('Link zum Beitrag kopiert!');
      }

      // Scroll zu geteiltem Post
      document.addEventListener('DOMContentLoaded', () => {
        const pid = new URLSearchParams(location.search).get('post_id');
        if (pid) {
          const el = document.getElementById(`post-${pid}`);
          if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            el.classList.add('shared-highlight');
            const moreLink = el.querySelector('.more-link');
            if (moreLink && el.querySelector('.more-content')?.style.display !== 'inline') moreLink.click();
            setTimeout(() => el.classList.remove('shared-highlight'), 2200);
          }
        }
      });

      // Kebab-Menü öffnen/schließen
      let openMenuId = null;

      function togglePostMenu(e, postId) {
        e.stopPropagation();
        const menu = document.getElementById(`menu-${postId}`);
        const trigger = e.currentTarget;

        // Schließe anderes offenes Menü
        if (openMenuId && openMenuId !== postId) {
          const prev = document.getElementById(`menu-${openMenuId}`);
          if (prev) prev.classList.remove('open');
        }

        const willOpen = !menu.classList.contains('open');
        menu.classList.toggle('open', willOpen);
        trigger.setAttribute('aria-expanded', String(willOpen));
        openMenuId = willOpen ? postId : null;
      }

      // Klicke außerhalb -> Menüs schließen
      document.addEventListener('click', () => {
        if (!openMenuId) return;
        const menu = document.getElementById(`menu-${openMenuId}`);
        if (menu) menu.classList.remove('open');
        openMenuId = null;
      });

      // Esc schließt offenes Menü
      document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape' && openMenuId) {
          const menu = document.getElementById(`menu-${openMenuId}`);
          if (menu) menu.classList.remove('open');
          openMenuId = null;
        }
      });

      // Bestätigungsdialog fürs Löschen
      function confirmDelete(postId) {
        return confirm('Diesen Beitrag wirklich löschen?');
      }

      // === Live-Preview + Validierung (ersetzt/erweitert deine previewImage) ===
      function previewImageFromFile(file) {
        const maxBytes = 5 * 1024 * 1024;
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!file) return;
        if (!allowed.includes(file.type)) { alert('Bitte JPG/PNG/WebP/GIF wählen.'); return; }
        if (file.size > maxBytes) { alert('Die Datei ist zu groß (max. 5 MB).'); return; }

        const reader = new FileReader();
        reader.onload = function (e) {
          const preview = document.getElementById('avatarPreview');
          preview.innerHTML = '';
          const img = document.createElement('img');
          img.src = e.target.result;
          img.alt = 'Profilbild Vorschau';
          preview.appendChild(img);
        };
        reader.readAsDataURL(file);
      }

      const imageUploadEl = document.getElementById('imageUpload');
      imageUploadEl?.addEventListener('change', (ev) => {
        const file = ev.target.files && ev.target.files[0];
        previewImageFromFile(file);
      });

      // Drag&Drop
      const dropzone = document.getElementById('dropzone');
      if (dropzone) {
        dropzone.addEventListener('click', () => imageUploadEl?.click());
        ['dragenter', 'dragover'].forEach(evt => dropzone.addEventListener(evt, e => {
          e.preventDefault(); e.stopPropagation(); dropzone.classList.add('drag');
        }));
        ['dragleave', 'drop'].forEach(evt => dropzone.addEventListener(evt, e => {
          e.preventDefault(); e.stopPropagation(); dropzone.classList.remove('drag');
        }));
        dropzone.addEventListener('drop', (e) => {
          const file = e.dataTransfer.files && e.dataTransfer.files[0];
          if (file) { previewImageFromFile(file); if (imageUploadEl) imageUploadEl.files = e.dataTransfer.files; }
        });
      }

      // Bio Counter + Progress
      function updateBioUI() {
        const ta = document.getElementById('bioInput');
        const counter = document.getElementById('bioCount');
        const bar = document.getElementById('bioProgressBar');
        if (!ta || !counter || !bar) return;
        const max = ta.getAttribute('maxlength') ? parseInt(ta.getAttribute('maxlength')) : 300;
        const len = ta.value.length;
        counter.textContent = `${len}/${max}`;
        const pct = Math.min(100, Math.round((len / max) * 100));
        bar.style.width = pct + '%';
      }
      document.addEventListener('input', e => {
        if (e.target && e.target.id === 'bioInput') { updateBioUI(); }
      });
      document.addEventListener('DOMContentLoaded', updateBioUI);

      // Kompaktmodus für sticky Profilkarte beim Scrollen (Desktop/Tablet)
      function initProfileHeaderCompact() {
        const profileCard = document.querySelector('.profile-card-top');
        const spacer = document.getElementById('profileCardSpacer');
        if (!profileCard) return;
        const desktopMq = window.matchMedia('(min-width: 769px)');
        let compactDiff = 0;

        const recalcCompactDiff = () => {
          if (!desktopMq.matches) {
            compactDiff = 0;
            if (spacer) spacer.style.height = '0px';
            return;
          }
          profileCard.classList.remove('is-compact');
          const expandedH = profileCard.offsetHeight;
          profileCard.classList.add('is-compact');
          const compactH = profileCard.offsetHeight;
          compactDiff = Math.max(0, expandedH - compactH);
          profileCard.classList.remove('is-compact');
        };

        const applyCompactState = () => {
          if (!desktopMq.matches) {
            profileCard.classList.remove('is-compact');
            if (spacer) spacer.style.height = '0px';
            return;
          }
          const triggerY = 220;
          const isCompact = window.scrollY > triggerY;
          profileCard.classList.toggle('is-compact', isCompact);
          if (spacer) spacer.style.height = isCompact ? `${compactDiff}px` : '0px';
        };

        recalcCompactDiff();
        applyCompactState();
        window.addEventListener('scroll', applyCompactState, { passive: true });
        window.addEventListener('resize', () => {
          recalcCompactDiff();
          applyCompactState();
        });
      }
      document.addEventListener('DOMContentLoaded', initProfileHeaderCompact);


    </script>
<?php
$bottomNavPath = __DIR__ . '/inc/buttomnav.php';
if (file_exists($bottomNavPath)) {
  require $bottomNavPath;
}
?>

</body>

</html>
