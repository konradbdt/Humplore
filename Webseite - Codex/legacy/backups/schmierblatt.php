<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

require __DIR__ . "/config/database.php";

/* ===========================
   Security: CSRF Token
   =========================== */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function require_csrf(): void
{
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(400);
    die("Ungültiges CSRF-Token.");
  }
}

/* ===========================
   Eingangsparameter
   =========================== */
$profile_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $_SESSION['user_id'];
$is_own_profile = ($profile_user_id === (int) $_SESSION['user_id']);

/* ===========================
   Benutzerdaten
   =========================== */
$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
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
if (!$is_own_profile) {
  $stmtFollow = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ? AND followed_id = ?");
  $stmtFollow->execute([$_SESSION['user_id'], $profile_user_id]);
  $isFollowing = $stmtFollow->fetchColumn() > 0;
}

/* ===========================
   Statistiken
   =========================== */
$stmtPostsCount = $pdo->prepare("SELECT COUNT(*) FROM Posts WHERE creator_id = ?");
$stmtPostsCount->execute([$profile_user_id]);
$postsCount = (int) $stmtPostsCount->fetchColumn();

$stmtFollower = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE followed_id = ?");
$stmtFollower->execute([$profile_user_id]);
$followerCount = (int) $stmtFollower->fetchColumn();

$stmtFollowing = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ?");
$stmtFollowing->execute([$profile_user_id]);
$followingCount = (int) $stmtFollowing->fetchColumn();

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

  /* Folgen/Entfolgen (alle Profile) */
  if (isset($_POST['follow_action'])) {
    require_csrf();
    $action = $_POST['follow_action'];
    if ($action === 'follow') {
      $stmt = $pdo->prepare("INSERT OR IGNORE INTO Follows (follower_id, followed_id) VALUES (?, ?)");
      $stmt->execute([$_SESSION['user_id'], $profile_user_id]);
    } elseif ($action === 'unfollow') {
      $stmt = $pdo->prepare("DELETE FROM Follows WHERE follower_id = ? AND followed_id = ?");
      $stmt->execute([$_SESSION['user_id'], $profile_user_id]);
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
  }

  /* Kommentar schreiben (ALLE eingeloggten Nutzer) */
  if (isset($_POST['comment_text'], $_POST['post_id'])) {
    require_csrf();
    $post_id = (int) $_POST['post_id'];
    $user_id = (int) $_SESSION['user_id'];
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
      $author_id = (int) $_SESSION['user_id'];
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
      $stmtDelete->execute([$delete_post_id, $_SESSION['user_id']]);
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
          $stmtUpdate->execute([$imageData, $_SESSION['user_id']]);
        }
        $stmtCheck = $pdo->prepare("SELECT id FROM CreatorDetails WHERE user_id = ?");
        $stmtCheck->execute([$_SESSION['user_id']]);
        $exists = $stmtCheck->fetchColumn();

        if ($exists) {
          $stmt = $pdo->prepare("UPDATE CreatorDetails SET bio = ? WHERE user_id = ?");
          $stmt->execute([$bio, $_SESSION['user_id']]);
        } else {
          $stmt = $pdo->prepare("INSERT INTO CreatorDetails (user_id, main_topic, bio) VALUES (?, 'Standardthema', ?)");
          $stmt->execute([$_SESSION['user_id'], $bio]);
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
if ($isCreator) {
  if ($activeCatSlug === '') {
    // ohne Filter
    $stmtPosts = $pdo->prepare("
      SELECT p.*, u.username,
             (SELECT GROUP_CONCAT(c.name, ' · ')
              FROM PostCategories pc
              JOIN Categories c ON c.id = pc.category_id
              WHERE pc.post_id = p.id
             ) AS cat_list
      FROM Posts p
      JOIN Users u ON p.creator_id = u.id
      WHERE p.creator_id = ?
      ORDER BY p.created_at DESC
    ");
    $stmtPosts->execute([$profile_user_id]);
  } else {
    // mit Kategorie-Filter (per slug)
    $stmtPosts = $pdo->prepare("
      SELECT p.*, u.username,
             (SELECT GROUP_CONCAT(c.name, ' · ')
              FROM PostCategories pc
              JOIN Categories c ON c.id = pc.category_id
              WHERE pc.post_id = p.id
             ) AS cat_list
      FROM Posts p
      JOIN Users u ON p.creator_id = u.id
      WHERE p.creator_id = ?
        AND EXISTS (
          SELECT 1
          FROM PostCategories pc
          JOIN Categories c ON c.id = pc.category_id
          WHERE pc.post_id = p.id AND c.slug = ?
        )
      ORDER BY p.created_at DESC
    ");
    $stmtPosts->execute([$profile_user_id, $activeCatSlug]);
  }
  $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
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
      height: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    header h1 {
      font-size: 1.2rem;
      color: #4b573e;
    }

    .banner {
      width: 100%;
      height: 150px;
      background-color: #4b573e;
      position: relative;
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
      margin-top: 40px;
      border: 1px solid #e5e7eb;
      max-height: 70vh;
      /* begrenzt die Gesamthöhe in der Sidebar */
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .questions-card h3 {
      margin-bottom: 2px;
      color: #4b573e;
      flex: 0 0 auto;
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
      background: #4b573e;
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
    }

    /* Seitencontainer */
    .side-container {
      flex: 1;
      min-width: 250px;
      max-width: 350px;
      position: sticky;
      top: 100px;
      height: fit-content;
      color: #4b573e;
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
      background: linear-gradient(135deg, #6ea173, #4b573e);
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
      background-color: rgb(153, 194, 107);
      border-radius: 50px;
      width: 165px;
      text-align: center;
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
      box-shadow: 0 0 5px #4b573e,
        0 0 25px #4b573e,
        0 0 50px #4b573e,
        0 0 100px #4b573e;
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
      border-radius: 12px;
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

    /* ==================== */
    /* NAVIGATION */
    /* ==================== */
    .bottom-nav {
      position: fixed;
      bottom: 10px;
      left: 50%;
      transform: translateX(-50%);
      width: calc(100% - 40px);
      max-width: 450px;
      height: 35px;
      background: linear-gradient(90deg, #4b573e, #4b573e);
      border-radius: 25px;
      display: flex;
      justify-content: space-evenly;
      align-items: center;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .bottom-nav a {
      color: #fff;
      text-decoration: none;
      font-size: 1rem;
      font-weight: 900;
      transition: transform 0.2s ease;
    }

    .bottom-nav a:hover {
      transform: scale(1.1);
    }

    .nav-button {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background: #4b573e;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
      text-align: center;
    }

    /* ==================== */
    /* MODAL */
    /* ==================== */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-width: 500px;
      background: #fff;
      padding: 30px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      font-family: 'Poppins', sans-serif;
    }

    .modal h2 {
      color: #580F41;
      font-size: 1.8rem;
      margin-bottom: 25px;
      text-align: center;
      font-weight: 700;
    }

    .modal label {
      display: block;
      margin: 15px 0 8px;
      color: #5a5a5a;
      font-weight: 500;
    }

    .modal input[type="text"],
    .modal textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #e0e0e0;
      border-radius: 12px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: #f8f8f8;
    }

    .modal input[type="text"]:focus,
    .modal textarea:focus {
      border-color: #580F41;
      background: #fff;
      outline: none;
    }

    .modal .button-group {
      display: flex;
      gap: 15px;
      margin-top: 25px;
      flex-wrap: wrap;
    }

    .modal button {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .modal .save-btn {
      background: #580F41;
      color: white;
    }

    .modal .save-btn:hover {
      background: #580F41;
      transform: translateY(-2px);
    }

    .modal .close-btn {
      background: #ff4d4d;
      color: white;
    }

    .modal .close-btn:hover {
      background: #ff3333;
      transform: translateY(-2px);
    }

    .avatar-preview {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      margin: 15px auto;
      border: 4px solid rgb(253, 207, 139);
      background: #f0f0f0;
      overflow: hidden;
    }

    .avatar-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* ==================== */
    /* TEILEN-CONTAINER */
    /* ==================== */
    .share-container {
      background: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-top: 35px;
      position: sticky;
      top: 100px;
    }

    .share-container h3 {
      color: #4b573e;
      margin-bottom: 15px;
      font-size: 1.2rem;
    }

    .share-link-container {
      display: flex;
      gap: 10px;
    }

    .share-link-container input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 0.9rem;
    }

    .share-link-container button {
      padding: 10px 15px;
      background: #4b573e;
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
      color: green;
      margin-top: 5px;
      font-weight: 500;
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
      background: #4b573e;
      color: #fff;
      border-color: #4b573e;
    }

    .post-catline {
      font-size: .85rem;
      color: #6b7280;
      margin-top: 6px;
    }
  </style>
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <h1>humplore</h1>
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
              <p style="color:#6b7280;">Noch keine Fragen.</p>
            <?php else:
              foreach ($questions as $q): ?>
                <div class="qa-item">
                  <div class="meta">
                    Von <strong>@<?= htmlspecialchars($q['author_name']) ?></strong>
                    · <?= htmlspecialchars(date('d.m.Y H:i', strtotime($q['created_at']))) ?>
                    · ❤️ <?= (int) $q['like_count'] ?>
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
          <h3>Frage an <?= htmlspecialchars($user['username']) ?></h3>
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
          $answered_preview = array_values(array_filter($questions, fn($q) => !empty($q['answer_text'])));
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
            <div class="arround-profile-img-wrapper">
              <div class="profile-img-wrapper">
                <?php if (!empty($user['profile_image'])): ?>
                  <img src="data:image/jpeg;base64,<?= base64_encode($user['profile_image']) ?>" alt="Profilbild">
                <?php else: ?>
                  <div class="profile-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <?php if ($isCreator): ?>
              <div class="profile-header">
                <div class="profile-info">
                  <h3 style="padding-bottom:0;color:rgb(141,141,141)">Thema:</h3>
                  <h2 class="profile-title"><?= htmlspecialchars($profileTitle) ?></h2>
                  <p class="profile-username"><?= htmlspecialchars($profileUsername) ?></p>

                  <!-- Profil-Schlagwörter (max 3) -->
                  <?php if (!empty($profileKeywords)): ?>
                    <div class="profile-keywords" style="margin:6px 0 10px 0;">
                      <?php foreach ($profileKeywords as $kw): ?>
                        <span><?= htmlspecialchars($kw['keyword']) ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>

                  <p class="profile-tagline"><?= htmlspecialchars($profileTagline) ?></p>

                  <div class="profile-hashtags">
                    <?php foreach ($profileHashtags as $hashtag):
                      if (!empty($hashtag)): ?>
                        <span><?= htmlspecialchars($hashtag) ?></span>
                      <?php endif; endforeach; ?>
                  </div>

                  <div class="profile-meta">
                    <p><?= htmlspecialchars($profileBio) ?></p>
                    <p><strong>Ort:</strong> <?= htmlspecialchars($profileLocation) ?></p>
                    <p><strong>Sprache:</strong> <?= htmlspecialchars($profileLanguages) ?></p>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Rechte Spalte: Stats + Follow -->
          <div class="header-flex-two-right">
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value"><?= (int) $followerCount ?></div>
                <div class="stat-label">Follower</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?= (int) $subscriberCount ?></div>
                <div class="stat-label">Abonnenten</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?= (int) $postsCount ?></div>
                <div class="stat-label">Beiträge</div>
              </div>
            </div>

            <?php if (!$is_own_profile): ?>
              <form method="post" class="follow-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <?php if ($isFollowing): ?>
                  <button type="submit" name="follow_action" value="unfollow" class="follow-btn is-active"
                    aria-label="Entfolgen">
                    <span class="follow-dot" aria-hidden="true"></span>Entfolgen
                  </button>
                <?php else: ?>
                  <button type="submit" name="follow_action" value="follow" class="follow-btn" aria-label="Folgen">
                    <span class="follow-dot" aria-hidden="true"></span>Folgen
                  </button>
                <?php endif; ?>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($isCreator): ?>
        <div class="arround-post">
          <!-- Kategorien-Filterbar -->
          <?php if (!empty($allCategories)): ?>
            <div class="category-bar">
              <a class="cat-chip <?= $activeCatSlug === '' ? 'active' : '' ?>"
                href="profile.php?user_id=<?= (int) $profile_user_id ?>">Alle</a>
              <?php foreach ($allCategories as $cat): ?>
                <a class="cat-chip <?= ($activeCatSlug === $cat['slug']) ? 'active' : '' ?>"
                  href="profile.php?user_id=<?= (int) $profile_user_id ?>&cat=<?= urlencode($cat['slug']) ?>">
                  <?= htmlspecialchars($cat['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="posts-container">
            <?php
            if ($posts) {
              foreach ($posts as $post) {
                // Like-Infos
                $stmtLikes = $pdo->prepare("SELECT COUNT(*) as count FROM Likes WHERE post_id = ?");
                $stmtLikes->execute([$post['id']]);
                $likeCount = (int) $stmtLikes->fetch()['count'];

                $stmtUserLike = $pdo->prepare("SELECT COUNT(*) as count FROM Likes WHERE post_id = ? AND user_id = ?");
                $stmtUserLike->execute([$post['id'], $_SESSION['user_id']]);
                $hasLiked = $stmtUserLike->fetch()['count'] > 0;

                // Kommentare
                $stmtComments = $pdo->prepare("
                  SELECT Comments.*, Users.username
                  FROM Comments
                  JOIN Users ON Comments.user_id = Users.id
                  WHERE post_id = ?
                  ORDER BY created_at DESC
                ");
                $stmtComments->execute([$post['id']]);
                $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
                $commentCount = count($comments);

                // Inhalt splitten
                $content = htmlspecialchars($post['content']);
                $words = preg_split('/\s+/', $content);
                $first_part = implode(' ', array_slice($words, 0, 20));
                $second_part = implode(' ', array_slice($words, 20));
                ?>
                <div class="post-card" id="post-<?= (int) $post['id'] ?>">
                  <div class="post-header">
                    <div class="post-header-img">
                      <?php if (!empty($user['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($user['profile_image']) ?>" alt="Profilbild">
                      <?php else: ?>
                        <div class="profile-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="post-header-info">
                      <span class="post-author">@<?= htmlspecialchars($post['username']) ?></span>
                      <span class="post-date"><?= date("d.m.Y H:i", strtotime($post['created_at'])) ?></span>
                    </div>
                  </div>

                  <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>

                  <div class="post-content-wrapper">
                    <?php if (!empty($post['media_image'])): ?>
                      <div class="post-image">
                        <img src="data:image/jpeg;base64,<?= base64_encode($post['media_image']) ?>" alt="Beitragsbild">
                      </div>
                    <?php endif; ?>

                    <?php if (!empty($post['cat_list'])): ?>
                      <div class="post-catline">Kategorien: <?= htmlspecialchars($post['cat_list']) ?></div>
                    <?php endif; ?>

                    <p class="post-content">
                      <?= $first_part ?>
                      <?php if (!empty($second_part)): ?>
                        <span class="more-content" id="more-<?= (int) $post['id'] ?>"><?= $second_part ?></span>
                        <a href="#" class="more-link" onclick="toggleMore(<?= (int) $post['id'] ?>, event)"> mehr lesen</a>
                      <?php endif; ?>
                    </p>
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

                  <div class="comments-section" id="comments-<?= (int) $post['id'] ?>" style="display:none;">
                    <?php foreach ($comments as $comment): ?>
                      <div class="comment">
                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                        <p><?= htmlspecialchars($comment['comment_text']) ?></p>
                        <small><?= date("d.m.Y H:i", strtotime($comment['created_at'])) ?></small>
                      </div>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                      <form method="post" class="comment-form">
                        <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <textarea name="comment_text" placeholder="Kommentar schreiben..." required></textarea>
                        <button type="submit">Kommentieren</button>
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
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Rechte Seitenleiste -->
    <div class="side-container right-container">
      <div class="share-container">
        <h3>Teile dieses Profil</h3>
        <div class="share-link-container">
          <input type="text" id="profileLinkInput" value="<?= htmlspecialchars($profileLink) ?>" readonly>
          <button onclick="copyProfileLink()">Kopieren</button>
        </div>
        <p id="copyConfirmation" class="copy-confirmation">✓ Link kopiert!</p>
      </div>
    </div>
  </div>

  <!-- Bottom-Nav -->
  <nav class="bottom-nav">
    <a href="platform.php">Home</a>
    <a href="search.php">Suche</a>
    <a href="posten.php">+</a>
    <a href="news.php">News</a>
    <a href="profile.php?user_id=<?= (int) $_SESSION['user_id'] ?>">Profil</a>
  </nav>

  <!-- Modal nur eigenes Profil -->
  <?php if ($is_own_profile): ?>
    <div class="modal-overlay" id="overlay"></div>
    <div class="modal" id="settingsModal">
      <h2>🛠 Profil bearbeiten</h2>
      <form method="post" action="profile.php?user_id=<?= (int) $profile_user_id ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <div class="avatar-preview" id="avatarPreview">
          <?php if (!empty($user['profile_image'])): ?>
            <img src="data:image/jpeg;base64,<?= base64_encode($user['profile_image']) ?>" alt="Profilvorschau">
          <?php else: ?>
            <div class="avatar-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <label for="bioInput">📝 Bio</label>
        <textarea id="bioInput" name="bio" rows="4"
          placeholder="Erzähl etwas über dich..."><?= htmlspecialchars($profileBio) ?></textarea>

        <label for="imageUpload">📷 Profilbild</label>
        <input type="file" id="imageUpload" name="profile_image" accept="image/*" onchange="previewImage(this)">

        <div class="button-group">
          <button type="submit" name="save_profile" class="save-btn">💾 Speichern</button>
          <button type="button" class="close-btn" onclick="closeModal()">✖ Schließen</button>
        </div>
      </form>
    </div>
  <?php endif; ?>

  <script>
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

    // Kommentare togglen
    function toggleComments(postId) {
      const el = document.getElementById(`comments-${postId}`);
      el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
    }

    // Modal
    function openModal() { document.getElementById("settingsModal").style.display = "block"; document.getElementById("overlay").style.display = "block"; }
    function closeModal() { document.getElementById("settingsModal").style.display = "none"; document.getElementById("overlay").style.display = "none"; }

    // Mehr lesen
    function toggleMore(postId, event) {
      event.preventDefault();
      const more = document.getElementById(`more-${postId}`);
      const link = event.currentTarget;
      if (more.style.display === 'none' || !more.style.display) {
        more.style.display = 'inline';
        link.textContent = 'weniger zeigen';
      } else {
        more.style.display = 'none';
        link.textContent = 'mehr lesen';
      }
    }

    // Like per AJAX (dein like_handler.php)
    function toggleLike(button) {
      const postId = button.getAttribute('data-post-id');
      const formData = new FormData();
      formData.append('post_id', postId);
      fetch('like_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const likeCount = button.querySelector('.like-count');
            likeCount.textContent = data.likeCount;
            if (data.liked) button.classList.add('liked'); else button.classList.remove('liked');
          }
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
  </script>
</body>

</html>