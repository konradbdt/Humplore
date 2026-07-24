<?php
session_start();
require __DIR__ . "/config/database.php";

/* ================
   Auth-Guard
   ================ */
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

/* ================
   CSRF Helper
   ================ */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

date_default_timezone_set('Europe/Berlin');

/* ================
   State
   ================ */
$ask_error = $ask_success = $answer_success = '';

/* ================
   Input / User
   ================ */
$profile_user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $_SESSION['user_id'];
$is_own_profile  = ($profile_user_id === (int)$_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { die("Benutzer nicht gefunden"); }
$isCreator = ((int)$user['is_creator'] === 1);

/* ================
   Counters / Follow
   ================ */
$isFollowing = false;
if (!$is_own_profile) {
  $stmtFollow = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ? AND followed_id = ?");
  $stmtFollow->execute([(int)$_SESSION['user_id'], $profile_user_id]);
  $isFollowing = $stmtFollow->fetchColumn() > 0;
}

$stmtPostsCount = $pdo->prepare("SELECT COUNT(*) FROM Posts WHERE creator_id = ?");
$stmtPostsCount->execute([$profile_user_id]);
$postsCount = (int)$stmtPostsCount->fetchColumn();

$stmtFollower = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE followed_id = ?");
$stmtFollower->execute([$profile_user_id]);
$followerCount = (int)$stmtFollower->fetchColumn();

$stmtFollowing = $pdo->prepare("SELECT COUNT(*) FROM Follows WHERE follower_id = ?");
$stmtFollowing->execute([$profile_user_id]);
$followingCount = (int)$stmtFollowing->fetchColumn();

$subscriberCount = 0;
try {
  $stmtSubs = $pdo->prepare("SELECT COUNT(*) FROM Subscriptions WHERE creator_id = ? AND status = 'active'");
  $stmtSubs->execute([$profile_user_id]);
  $subscriberCount = (int)$stmtSubs->fetchColumn();
} catch (PDOException $e) {
  $subscriberCount = 0;
}

/* ================
   CreatorDetails
   ================ */
$data = [];
if ($isCreator) {
  $stmt = $pdo->prepare("SELECT * FROM CreatorDetails WHERE user_id = ?");
  $stmt->execute([$profile_user_id]);
  $data = (array)$stmt->fetch(PDO::FETCH_ASSOC);
}

/* ================
   POST Handling
   ================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
      throw new Exception('Sicherheitsprüfung fehlgeschlagen. Bitte Seite neu laden.');
    }

    // Follow/Unfollow (für alle Profile)
    if (isset($_POST['follow_action'])) {
      if ($is_own_profile) { throw new Exception('Eigene Profile können nicht gefolgt werden.'); }
      $action = $_POST['follow_action'];
      if ($action === 'follow') {
        $stmt = $pdo->prepare("INSERT INTO Follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([(int)$_SESSION['user_id'], $profile_user_id]);
      } elseif ($action === 'unfollow') {
        $stmt = $pdo->prepare("DELETE FROM Follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([(int)$_SESSION['user_id'], $profile_user_id]);
      }
      header("Location: " . $_SERVER['REQUEST_URI']); exit;
    }

    // Nur eigenes Profil: Posts löschen / kommentieren / Profil speichern
    if ($is_own_profile) {
      if (isset($_POST['delete_post'], $_POST['delete_post_id'])) {
        $delete_post_id = (int)$_POST['delete_post_id'];
        $stmt = $pdo->prepare("DELETE FROM Posts WHERE id = ? AND creator_id = ?");
        $stmt->execute([$delete_post_id, (int)$_SESSION['user_id']]);
        header("Location: profile.php?user_id=".$profile_user_id); exit;
      }

      if (isset($_POST['comment_text'], $_POST['post_id'])) {
        $post_id = (int)$_POST['post_id'];
        $user_id = (int)$_SESSION['user_id'];
        $comment_text = (string)$_POST['comment_text']; // Escaping beim Rendern
        $stmt = $pdo->prepare("INSERT INTO Comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $comment_text]);
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "profile.php?user_id=".$profile_user_id)); exit;
      }

      if (isset($_POST['save_profile'])) {
        $bio          = $_POST['bio'] ?? '';
        $profileImage = $_FILES['profile_image']['tmp_name'] ?? null;
        $imageData    = null;

        if ($profileImage && is_uploaded_file($profileImage)) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $mime  = finfo_file($finfo, $profileImage);
          finfo_close($finfo);
          if (is_string($mime) && strpos($mime, 'image/') === 0) {
            if ((int)($_FILES['profile_image']['size'] ?? 0) > 5 * 1024 * 1024) {
              throw new Exception('Profilbild ist größer als 5 MB.');
            }
            $imageData = file_get_contents($profileImage);
          } else {
            throw new Exception('Nur Bilddateien sind erlaubt.');
          }
        }

        $pdo->beginTransaction();
        if ($imageData) {
          $stmt = $pdo->prepare("UPDATE Users SET profile_image = ? WHERE id = ?");
          $stmt->execute([$imageData, (int)$_SESSION['user_id']]);
        }

        $stmt = $pdo->prepare("SELECT id FROM CreatorDetails WHERE user_id = ?");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $exists = (bool)$stmt->fetchColumn();

        if ($exists) {
          $stmt = $pdo->prepare("UPDATE CreatorDetails SET bio = ? WHERE user_id = ?");
          $stmt->execute([$bio, (int)$_SESSION['user_id']]);
        } else {
          $stmt = $pdo->prepare("INSERT INTO CreatorDetails (user_id, main_topic, bio) VALUES (?, 'Standardthema', ?)");
          $stmt->execute([(int)$_SESSION['user_id'], $bio]);
        }

        $pdo->commit();
        header("Location: profile.php?user_id=".$profile_user_id); exit;
      }
    }

    // Fragen stellen/antworten (nur bei Creator-Profilen)
    if ($isCreator && isset($_POST['action'])) {
      if ($_POST['action'] === 'ask_question' && !$is_own_profile) {
        $creator_id    = (int)($_POST['creator_id'] ?? 0);
        $question_text = trim((string)($_POST['question_text'] ?? ''));
        if ($creator_id === $profile_user_id && $question_text !== '') {
          $stmt = $pdo->prepare("INSERT INTO Questions (creator_id, author_id, question_text) VALUES (?, ?, ?)");
          $stmt->execute([$creator_id, (int)$_SESSION['user_id'], $question_text]);
          $ask_success = 'Frage gesendet.';
        }
      }
      if ($_POST['action'] === 'answer_question' && $is_own_profile) {
        $qid         = (int)($_POST['question_id'] ?? 0);
        $answer_text = trim((string)($_POST['answer_text'] ?? ''));
        if ($qid && $answer_text !== '') {
          $stmt = $pdo->prepare("UPDATE Questions SET answer_text = ?, answered_at = NOW() WHERE id = ? AND creator_id = ?");
          $stmt->execute([$answer_text, $qid, (int)$_SESSION['user_id']]);
          $answer_success = 'Antwort gespeichert.';
        }
      }
    }

  } catch (Exception $e) {
    $ask_error = $e->getMessage();
  }
}

/* ================
   Profil-Link
   ================ */
$baseUrl     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$profileLink = $baseUrl . "/profile.php?user_id=" . $profile_user_id;

/* ================
   Fragen laden
   ================ */
$questions = [];
if ($isCreator) {
  $stmtQuestions = $pdo->prepare("
    SELECT q.*, COUNT(ql.id) AS like_count, u.username AS author_name
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

/* ================
   Profil-Defaults
   ================ */
$profileBio       = $isCreator && !empty($data['bio']) ? $data['bio'] : 'Noch keine Bio vorhanden';
$profileTitle     = $isCreator && !empty($data['main_topic']) ? $data['main_topic'] : 'Thema';
$profileUsername  = '@' . $user['username'];
$profileHashtags  = !empty($data['hashtags']) ? array_map('trim', explode(',', $data['hashtags'])) : [];
$profileLocation  = !empty($data['ort'])      ? $data['ort']      : 'Ort nicht angegeben';
$profileLanguages = !empty($data['sprache'])  ? $data['sprache']  : 'Sprache nicht angegeben';
$profileExchange  = !empty($data['austausch'])? $data['austausch'] : 'Austausch nicht angegeben';

?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>humplore – Profil</title>

  <!-- Fonts & Basis -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --brand-prim:#4b573e;  /* Grün */
      --brand-sec:#580F41;   /* Violett */
      --bg:#f6f7f6;
      --text:#2c2f2b;
      --muted:#6f7a69;
      --card:#fff;
      --border:rgba(0,0,0,.08);
      --radius:14px;
      --radius-lg:18px;
      --shadow-sm:0 2px 8px rgba(0,0,0,.08);
      --shadow-md:0 6px 16px rgba(0,0,0,.10);
      --shadow-lg:0 14px 34px rgba(0,0,0,.12);
      --focus:0 0 0 3px rgba(88,15,65,.20);
      --gutter: clamp(14px, 2.2vw, 28px);   /* flexible Außenränder */
      --rail: clamp(260px, 22vw, 360px);    /* elastische Sidebars */
      --content-g: clamp(16px, 2vw, 28px);  /* Spaltengap */
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      background:
        radial-gradient(900px 300px at 20% -10%, rgba(88,15,65,.06), transparent 60%),
        radial-gradient(900px 300px at 80% -8%, rgba(75,87,62,.06), transparent 60%),
        var(--bg);
      color:var(--text);
      font-family:'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      line-height:1.6; padding-bottom:84px;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    }
    h1,h2,h3{font-family:'DM Serif Display', Georgia, serif}
    a{color:inherit;text-decoration:none}
    img{display:block;max-width:100%}

    /* Header */
    header{
      background: var(--card);
      position: sticky; top:0; z-index: 60;
      height: 58px; display:grid; place-items:center;
      border-bottom: 1px solid var(--border);
      box-shadow: 0 2px 4px rgba(0,0,0,.04);
    }
    header h1{font-size:1.22rem; color: var(--brand-prim); letter-spacing:.2px; font-weight:800}

    /* Banner */
    .banner{
      height: clamp(148px, 14vh, 220px);
      display:grid; place-items:center; position:relative; overflow:hidden;
      background: linear-gradient(135deg, var(--brand-prim), var(--brand-prim));
      border-bottom:1px solid rgba(255,255,255,.22);
    }
    .banner::after{
      content:""; position:absolute; inset:0;
      background: radial-gradient(1100px 300px at 50% -22%, rgba(255,255,255,.12), transparent 60%);
    }
    .banner h2{ color:#fff; font-size:2rem; text-shadow:0 2px 4px rgba(0,0,0,.35); }

    /* VOLLBREITE LAYOUT */
    .main-content-wrapper{
      width: 100%;
      margin: -96px 0 28px;
      padding: 0 var(--gutter);
      display: grid;
      grid-template-columns: var(--rail) minmax(0,1fr) var(--rail);
      gap: var(--content-g);
    }
    @media (max-width: 1200px){
      .main-content-wrapper{ grid-template-columns: var(--rail) minmax(0,1fr); }
      .right-container{ display:none; }
    }
    @media (max-width: 840px){
      .main-content-wrapper{
        grid-template-columns: minmax(0,1fr);
        margin: -80px 0 24px;
      }
      .side-container{ position: static; }
    }

    /* Sidebars + Cards */
    .side-container{ position: sticky; top: 92px; height: fit-content; }
    .card{
      background: rgba(255,255,255,.86);
      backdrop-filter: saturate(1.05) blur(10px);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 16px;
    }
    @media (prefers-reduced-transparency: reduce){ .card{ backdrop-filter:none; background:var(--card); } }

    /* Profile Card (full width center) */
    .profile-card{ padding: 0; overflow: hidden; }
    .profile-top{
      background: linear-gradient(135deg, var(--brand-prim), #5f6f50);
      height: 120px; position: relative;
    }
    .profile-body{ padding: 18px; }
    .profile-head{ display:flex; gap:16px; margin-top:-64px; align-items:center; flex-wrap: wrap; }
    .avatar{
      width:120px; height:120px; border-radius:50%; border:5px solid #fff;
      box-shadow: var(--shadow-md); overflow:hidden; background:#e9eee6; display:grid; place-items:center; font-weight:900; color:#fff; background:#580F41;
    }
    .profile-meta{ display:flex; flex-direction:column; gap:6px; min-width: 240px; }
    .profile-title{
      display:inline-block; background:#99c26b; color:#fff; border-radius:999px; padding:6px 12px; font-weight:900; letter-spacing:.2px;
      font-family:'DM Serif Display'; font-size:1.1rem;
    }
    .profile-username{ color:#6f7a69; font-weight:700 }
    .profile-extra{ color:#5c6356; }
    .hashtags{ display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
    .hashtags span{ background:#eef3ea; border:1px solid #dbe6d6; color:#3e4736; border-radius:999px; padding:4px 10px; font-weight:700; font-size:.86rem; }

    .stats-grid{
      display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; margin-top:12px; max-width: 560px;
    }
    .stat{
      background:#fff; border:1px solid var(--border); border-radius:12px; text-align:center; padding:12px; box-shadow: var(--shadow-sm);
    }
    .stat .v{ font-weight:900; font-size:1.2rem; color:#25301f }
    .stat .l{ color:#6b7280; font-size:.9rem }

    .btn{
      appearance:none; border:0; cursor:pointer; font-weight:900; letter-spacing:.2px;
      background: linear-gradient(135deg, #6ea173, #4b573e);
      color:#fff; border-radius: 999px; padding: 10px 16px; 
      box-shadow: 0 10px 22px rgba(75, 87, 62, .25);
      transition: transform .06s, background .2s, box-shadow .2s, filter .2s;
    }
    .btn:hover{ filter: brightness(1.05); box-shadow: 0 14px 28px rgba(75,87,62,.32) }
    .btn:active{ transform: translateY(1px) }
    .btn.muted{ background: linear-gradient(135deg, #9ca3af, #6b7280) }

    /* Questions */
    .questions-card h3{ color: var(--brand-prim); margin-bottom:8px; }
    .q-scroll{ max-height: 60vh; overflow:auto; padding-right: 6px }
    .qa-item{ border:1px solid var(--border); border-radius:12px; padding:10px; background:#fff; margin-top:10px }
    .qa-item .meta{ font-size:.8rem; color:#6b7280 }
    .qa-item .q{ font-weight:700; margin-top:6px }
    .qa-item .a{ margin-top:6px }
    .flash-ok{ color:#245a33; font-weight:700 }
    .flash-err{ color:#7a1e1e; font-weight:700 }
    .questions-card form textarea{
      width:100%; padding:10px; border:2px solid #e7e9e5; border-radius:12px; resize:vertical; background:#f7f8f6;
    }
    .questions-card form textarea:focus{ background:#fff; border-color: var(--brand-sec); box-shadow: var(--focus); outline:0; }
    .questions-card form button{ margin-top:8px }

    /* POSTS: Full-width responsive grid */
    .posts-grid{
      display:grid;
      grid-template-columns: repeat(auto-fit, minmax(clamp(320px, 34vw, 520px), 1fr));
      gap: var(--content-g);
    }
    .post-card{ background:#fff; border:1px solid var(--border); border-radius:12px; box-shadow: var(--shadow-sm); padding:18px; transition: transform .2s, box-shadow .2s; height:100% }
    .post-card:hover{ transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.12) }
    .post-header{ display:flex; align-items:center; gap:12px; margin-bottom:10px }
    .post-header-img{ width:48px; height:48px }
    .post-header-img img{ width:100%; height:100%; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow: var(--shadow-sm) }
    .post-author{ font-weight:800; color: var(--brand-sec); font-size:.95rem }
    .post-date{ font-size:.82rem; color:#888 }
    .post-title{ font-size:1.3rem; margin:8px 0 6px; font-family:'DM Serif Display' }
    .post-image{ border-radius:10px; overflow:hidden; margin: 8px 0 12px; border:1px solid var(--border) }
    .post-image img{ width:100%; height:auto; image-rendering:auto }
    .post-content{ font-family:'Lora', Georgia, serif; font-size:1.02rem; color:#3b4138; max-width: 68ch; }
    .more-link{ color:#4d4d4d; text-decoration:none; font-weight:700; font-size:.9rem }
    .more-link:hover{ text-decoration:underline }

    .post-actions{ display:flex; justify-content:space-between; border-top:1px solid #eee; padding-top:12px; margin-top:12px; gap:8px; flex-wrap:wrap }
    .action-button{ background:none; border:0; cursor:pointer; display:flex; align-items:center; gap:6px; padding:8px 12px; border-radius:10px; color:#666; transition:background .2s, color .2s }
    .action-button:hover{ background:#f5f5f5; color: var(--brand-sec) }
    .action-icon{ width:20px; height:20px; fill: currentColor }
    .like-button.liked{ color:#e74c3c }
    .like-button.liked:hover{ background: rgba(231,76,60,.1) }

    .comments-section .comment{ border-top:1px dashed #e6e6e6; padding-top:8px; margin-top:8px }

    /* Share card */
    .share-container h3{ color: var(--brand-prim); margin-bottom: 10px }
    .share-link-container{ display:flex; gap:8px }
    .share-link-container input{ flex:1; padding:10px; border:2px solid #e7e9e5; border-radius:12px; background:#f7f8f6 }
    .share-link-container input:focus{ background:#fff; border-color: var(--brand-sec); box-shadow: var(--focus); outline:0 }
    .copy-confirmation{ display:none; color:#245a33; font-weight:700; margin-top:6px }

    /* Bottom nav */
    .bottom-nav{
      position: fixed; left:50%; transform: translateX(-50%);
      bottom: 12px; width: calc(100% - 40px); max-width: 520px; height: 44px;
      background: linear-gradient(90deg, var(--brand-prim), var(--brand-prim));
      border-radius: 999px; display:flex; justify-content:space-evenly; align-items:center;
      box-shadow: var(--shadow-lg); z-index: 1000; padding: 0 8px; border:1px solid rgba(255,255,255,.14);
    }
    .bottom-nav a{
      color:#fff; font-weight:900; font-size:.95rem; padding:6px 12px; border-radius: 10px;
      transition: background .2s, transform .06s, opacity .2s; opacity:.96;
      text-shadow: 0 1px 2px rgba(0,0,0,.25);
    }
    .bottom-nav a:hover{ background: rgba(255,255,255,.1); transform: translateY(-1px); }

    /* Mobile Tweaks */
    @media (max-width: 720px){
      .post-actions{ justify-content: space-around }
      .post-card{ border-radius: 0; box-shadow:none; border-bottom:1px solid #eee }
      .stats-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header><h1>humplore</h1></header>

  <!-- Banner -->
  <div class="banner" role="img" aria-label="Profil-Banner">
    <h2>Profil</h2>
  </div>

  <div class="main-content-wrapper">
    <!-- LEFT: Fragen/Ask -->
    <aside class="side-container left-container">
      <div class="card questions-card">
        <?php if ($is_own_profile): ?>
          <h3>Fragen an dich</h3>
          <?php if (!empty($answer_success)): ?><div class="flash-ok"><?= e($answer_success) ?></div><?php endif; ?>
          <?php if (!empty($ask_error)): ?><div class="flash-err"><?= e($ask_error) ?></div><?php endif; ?>

          <div class="q-scroll">
            <?php if (empty($questions)): ?>
              <p style="color:#6b7280;">Noch keine Fragen.</p>
            <?php else: ?>
              <?php foreach ($questions as $q): ?>
                <div class="qa-item">
                  <div class="meta">
                    Von <strong>@<?= e($q['author_name']) ?></strong>
                    · <?= e(date('d.m.Y H:i', strtotime($q['created_at']))) ?>
                    · ❤️ <?= (int)$q['like_count'] ?>
                  </div>
                  <div class="q">Q: <?= e($q['question_text']) ?></div>

                  <?php if (!empty($q['answer_text'])): ?>
                    <div class="a">A: <?= nl2br(e($q['answer_text'])) ?></div>
                    <?php if (!empty($q['answered_at'])): ?>
                      <div class="meta">beantwortet am <?= e(date('d.m.Y H:i', strtotime($q['answered_at']))) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <form method="post" style="margin-top:8px;">
                      <input type="hidden" name="action" value="answer_question">
                      <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                      <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                      <textarea name="answer_text" rows="3" placeholder="Antwort eingeben ..." required></textarea>
                      <button type="submit" class="btn" aria-label="Antwort senden">Antwort senden</button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <?php else: ?>
          <h3>Frage an <?= e($user['username']) ?></h3>
          <?php if (!empty($ask_error)): ?><div class="flash-err"><?= e($ask_error) ?></div><?php endif; ?>
          <?php if (!empty($ask_success)): ?><div class="flash-ok"><?= e($ask_success) ?></div><?php endif; ?>

          <form method="post" action="profile.php?user_id=<?= (int)$profile_user_id ?>">
            <input type="hidden" name="action" value="ask_question">
            <input type="hidden" name="creator_id" value="<?= (int)$profile_user_id ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <textarea name="question_text" rows="4" placeholder="Stell deine Frage ..." required></textarea>
            <button type="submit" class="btn" aria-label="Frage absenden">Absenden</button>
          </form>

          <?php
          $answered_preview = array_values(array_filter($questions, fn($q) => !empty($q['answer_text'])));
          if (!empty($answered_preview)):
            $preview_slice = array_slice($answered_preview, 0, 5);
          ?>
            <div class="q-scroll" style="margin-top:8px;">
              <div style="font-weight:700; margin-bottom:6px;">Kürzlich beantwortet</div>
              <?php foreach ($preview_slice as $q): ?>
                <div class="qa-item">
                  <div class="q">Q: <?= e($q['question_text']) ?></div>
                  <div class="a">A: <?= nl2br(e($q['answer_text'])) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </aside>

    <!-- CENTER: Profil + Posts -->
    <main class="profile-container">
      <!-- Profilkarte -->
      <section class="card profile-card" aria-label="Profilkarte">
        <div class="profile-top"></div>
        <div class="profile-body">
          <div class="profile-head">
            <div class="avatar" aria-label="Profilbild">
              <?php if (!empty($user['profile_image'])): ?>
                <img src="data:image/jpeg;base64,<?= base64_encode($user['profile_image']) ?>" alt="Profilbild">
              <?php else: ?>
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
              <?php endif; ?>
            </div>

            <div class="profile-meta">
              <span class="profile-title"><?= e($profileTitle) ?></span>
              <span class="profile-username"><?= e($profileUsername) ?></span>
              <span class="profile-extra"><?= e($profileBio) ?></span>
              <span class="profile-extra"><strong>Ort:</strong> <?= e($profileLocation) ?> · <strong>Sprache:</strong> <?= e($profileLanguages) ?></span>
              <?php if (!empty($profileHashtags)): ?>
                <div class="hashtags">
                  <?php foreach ($profileHashtags as $tag): if ($tag==='') continue; ?>
                    <span>#<?= e($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="stats-grid" role="group" aria-label="Profilstatistiken">
                <div class="stat"><div class="v"><?= (int)$followerCount ?></div><div class="l">Follower</div></div>
                <div class="stat"><div class="v"><?= (int)$subscriberCount ?></div><div class="l">Abonnenten</div></div>
                <div class="stat"><div class="v"><?= (int)$postsCount ?></div><div class="l">Beiträge</div></div>
              </div>

              <?php if (!$is_own_profile): ?>
                <form method="post" class="follow-form" style="margin-top:8px">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                  <?php if ($isFollowing): ?>
                    <button type="submit" name="follow_action" value="unfollow" class="btn muted" aria-label="Entfolgen">Entfolgen</button>
                  <?php else: ?>
                    <button type="submit" name="follow_action" value="follow" class="btn" aria-label="Folgen">Folgen</button>
                  <?php endif; ?>
                </form>
              <?php endif; ?>

              <?php if ($is_own_profile): ?>
                <!-- Profil bearbeiten (Modal Trigger könnte hinzugefügt werden) -->
                <form method="post" enctype="multipart/form-data" style="margin-top:10px">
                  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                  <label style="display:block; font-weight:800; color:#3a4532; margin-bottom:6px">📝 Bio</label>
                  <textarea name="bio" rows="3" style="width:100%;padding:10px;border:2px solid #e7e9e5;border-radius:12px;background:#f7f8f6" placeholder="Erzähl etwas über dich..."><?= e($profileBio) ?></textarea>
                  <label style="display:block; font-weight:800; color:#3a4532; margin:8px 0 6px">📷 Profilbild (max. 5MB)</label>
                  <input type="file" name="profile_image" accept="image/*">
                  <div style="margin-top:8px">
                    <button type="submit" name="save_profile" class="btn">Speichern</button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </section>

      <!-- Beiträge -->
      <?php if ($isCreator): ?>
        <section aria-label="Beiträge" style="margin-top:18px">
          <div class="posts-grid">
          <?php
            $stmtPosts = $pdo->prepare("SELECT Posts.*, Users.username 
                                        FROM Posts 
                                        JOIN Users ON Posts.creator_id = Users.id 
                                        WHERE Posts.creator_id = ? 
                                        ORDER BY Posts.created_at DESC");
            $stmtPosts->execute([$profile_user_id]);
            $posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

            if ($posts):
              foreach ($posts as $post):
                // Likes
                $stmtLikes = $pdo->prepare("SELECT COUNT(*) as count FROM Likes WHERE post_id = ?");
                $stmtLikes->execute([$post['id']]);
                $likeCount = (int)($stmtLikes->fetch()['count'] ?? 0);

                $stmtUserLike = $pdo->prepare("SELECT COUNT(*) as count FROM Likes WHERE post_id = ? AND user_id = ?");
                $stmtUserLike->execute([$post['id'], (int)$_SESSION['user_id']]);
                $hasLiked = ($stmtUserLike->fetch()['count'] ?? 0) > 0;

                // Kommentare
                $stmtComments = $pdo->prepare("SELECT Comments.*, Users.username 
                                               FROM Comments 
                                               JOIN Users ON Comments.user_id = Users.id 
                                               WHERE post_id = ? 
                                               ORDER BY created_at DESC");
                $stmtComments->execute([$post['id']]);
                $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
                $commentCount = count($comments);

                // Content Split
                $content     = e($post['content']);
                $words       = preg_split('/\s+/', $content);
                $first_part  = implode(' ', array_slice($words, 0, 20));
                $second_part = implode(' ', array_slice($words, 20));
          ?>
            <article class="post-card">
              <div class="post-header">
                <div class="post-header-img">
                  <?php if (!empty($user['profile_image'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($user['profile_image']) ?>" alt="Profilbild">
                  <?php else: ?>
                    <div class="avatar" style="width:48px;height:48px;border-width:2px"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                  <?php endif; ?>
                </div>
                <div>
                  <div class="post-author">@<?= e($post['username']) ?></div>
                  <div class="post-date"><?= e(date("d.m.Y H:i", strtotime($post['created_at']))) ?></div>
                </div>
              </div>

              <h3 class="post-title"><?= e($post['title']) ?></h3>

              <div class="post-content-wrapper">
                <?php if (!empty($post['media_image'])): ?>
                  <div class="post-image"><img src="data:image/jpeg;base64,<?= base64_encode($post['media_image']) ?>" alt="Beitragsbild"></div>
                <?php endif; ?>

                <p class="post-content">
                  <?= $first_part ?>
                  <?php if (!empty($second_part)): ?>
                    <span class="more-content" id="more-<?= (int)$post['id'] ?>" style="display:none"><?= $second_part ?></span>
                    <a href="#" class="more-link" onclick="toggleMore(<?= (int)$post['id'] ?>, event)">mehr lesen</a>
                  <?php endif; ?>
                </p>
              </div>

              <div class="post-actions">
                <button class="action-button like-button <?= $hasLiked ? 'liked' : '' ?>" data-post-id="<?= (int)$post['id'] ?>" onclick="toggleLike(this)" aria-label="Wissenswert markieren">
                  <svg class="action-icon" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                  <span class="action-count like-count"><?= (int)$likeCount ?></span>
                  <span class="action-label">Wissenswert</span>
                </button>

                <button class="action-button comments-button" onclick="toggleComments(<?= (int)$post['id'] ?>)" aria-label="Kommentare anzeigen">
                  <svg class="action-icon" viewBox="0 0 24 24"><path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z"/></svg>
                  <span class="action-count comment-count"><?= (int)$commentCount ?></span>
                  <span class="action-label">Kommentar</span>
                </button>

                <button class="action-button share-button" onclick="sharePost(<?= (int)$post['id'] ?>)" aria-label="Beitrag teilen">
                  <svg class="action-icon" viewBox="0 0 24 24"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>
                  <span class="action-label">Teilen</span>
                </button>

                <button class="action-button donate-button" onclick="donateToPost(<?= (int)$post['id'] ?>)" aria-label="Beitrag unterstützen">
                  <svg class="action-icon" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 11.41L16 10l-1.41-1.41L11 12.17l-2.09-2.09L7.5 11.5l3.5 3.5 4.41-4.59z"/></svg>
                  <span class="action-label">Unterstützen</span>
                </button>
              </div>

              <div class="comments-section" id="comments-<?= (int)$post['id'] ?>" style="display:none">
                <?php foreach ($comments as $comment): ?>
                  <div class="comment">
                    <strong><?= e($comment['username']) ?>:</strong>
                    <p><?= e($comment['comment_text']) ?></p>
                    <small><?= e(date("d.m.Y H:i", strtotime($comment['created_at']))) ?></small>
                  </div>
                <?php endforeach; ?>

                <?php if ($is_own_profile): ?>
                  <form method="post" class="comment-form" style="margin-top:8px">
                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                    <textarea name="comment_text" placeholder="Kommentar schreiben..." required style="width:100%;padding:10px;border:2px solid #e7e9e5;border-radius:12px;background:#f7f8f6"></textarea>
                    <button type="submit" class="btn" style="margin-top:8px">Kommentieren</button>
                  </form>
                <?php endif; ?>
              </div>
            </article>
          <?php
              endforeach;
            else:
              echo '<p class="card" style="text-align:center">Noch keine Beiträge vorhanden.</p>';
            endif;
          ?>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <!-- RIGHT: Share -->
    <aside class="side-container right-container">
      <div class="card share-container">
        <h3>Teile dieses Profil</h3>
        <div class="share-link-container">
          <input type="text" id="profileLinkInput" value="<?= e($profileLink) ?>" readonly aria-label="Profillink">
          <button class="btn" onclick="copyProfileLink()">Kopieren</button>
        </div>
        <p id="copyConfirmation" class="copy-confirmation">✓ Link kopiert!</p>
      </div>
    </aside>
  </div>

  <!-- Bottom-Nav -->
  <nav class="bottom-nav" aria-label="Hauptnavigation">
    <a href="platform.php">Home</a>
    <a href="search.php">Suche</a>
    <a href="posten.php">+</a>
    <a href="news.php">News</a>
    <a href="profile.php?user_id=<?= (int)$_SESSION['user_id'] ?>">Profil</a>
  </nav>

  <script>
    // Profillink kopieren
    function copyProfileLink() {
      const copyText = document.getElementById("profileLinkInput");
      copyText.select(); copyText.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyText.value);
      const c = document.getElementById("copyConfirmation");
      c.style.display = "block"; setTimeout(()=> c.style.display = "none", 2000);
    }

    // Kommentare togglen
    function toggleComments(id){
      const el = document.getElementById('comments-'+id);
      el.style.display = (el.style.display === 'none' || !el.style.display) ? 'block' : 'none';
    }

    // Mehr lesen togglen
    function toggleMore(id, ev){
      ev.preventDefault();
      const more = document.getElementById('more-'+id);
      const link = ev.currentTarget;
      const isHidden = (more.style.display === 'none' || !more.style.display);
      more.style.display = isHidden ? 'inline' : 'none';
      link.textContent = isHidden ? 'weniger zeigen' : 'mehr lesen';
    }

    // Likes (AJAX)
    function toggleLike(button){
      const postId = button.getAttribute('data-post-id');
      const formData = new FormData();
      formData.append('post_id', postId);
      fetch('like_handler.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (!data || !('success' in data)) return;
          const likeCount = button.querySelector('.like-count');
          likeCount.textContent = data.likeCount;
          if (data.liked) { button.classList.add('liked'); }
          else { button.classList.remove('liked'); }
        })
        .catch(console.error);
    }
  </script>
</body>
</html>
