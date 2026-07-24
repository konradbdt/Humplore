<?php
// explore.php — Kombinierte Seite: Suche + Feed
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
require __DIR__ . "/config/database.php";

/* ===========================
   Einstellungen & Helper
   =========================== */
date_default_timezone_set('Europe/Berlin');
$userId  = (int) $_SESSION['user_id'];
$perPage = 12; // Karten pro Seite (Feed)
$page    = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset  = ($page - 1) * $perPage;

function e(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function htime(string $ts): string {
  $t = strtotime($ts);
  if ($t === false) return e($ts);
  $diff = time() - $t;
  if ($diff < 60) return "gerade eben";
  $units = [
    31536000 => "Jahr",
    2592000  => "Monat",
    604800   => "Woche",
    86400    => "Tag",
    3600     => "Stunde",
    60       => "Minute"
  ];
  foreach ($units as $sec => $label) {
    $v = floor($diff / $sec);
    if ($v >= 1) return $v . " " . $label . ($v === 1 ? "" : "n");
  }
  return date('d.m.Y H:i', $t);
}

/* ===========================
   Suche (wie in search.php)
   =========================== */
$searchQuery    = '';
$resultsProfiles = [];
$resultsPosts    = [];
$countProfiles   = 0;
$countPosts      = 0;
$totalFound      = 0;

if (isset($_GET['q'])) {
  $searchQuery = trim($_GET['q']);

  // Profile (nur Creator)
  $stmtProfiles = $pdo->prepare("
    SELECT u.id, u.username, u.email, u.profile_image, cd.main_topic,
           (SELECT COUNT(*) FROM Follows WHERE followed_id = u.id) AS follower_count
    FROM Users u
    JOIN CreatorDetails cd ON u.id = cd.user_id
    WHERE u.is_creator = 1
      AND (u.username LIKE :query OR cd.main_topic LIKE :query OR u.email LIKE :query)
    ORDER BY u.username ASC
  ");
  $stmtProfiles->execute(['query' => '%' . $searchQuery . '%']);
  $resultsProfiles = $stmtProfiles->fetchAll(PDO::FETCH_ASSOC);

  // Beiträge (global)
  $stmtPosts = $pdo->prepare("
    SELECT p.*, u.username, u.profile_image
    FROM Posts p
    JOIN Users u ON p.creator_id = u.id
    WHERE p.title LIKE :query OR p.content LIKE :query
    ORDER BY p.created_at DESC
  ");
  $stmtPosts->execute(['query' => '%' . $searchQuery . '%']);
  $resultsPosts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

  $countProfiles = count($resultsProfiles);
  $countPosts    = count($resultsPosts);
  $totalFound    = $countProfiles + $countPosts;
}

/* ===========================
   Feed (wie in feed.php)
   =========================== */
// Posts der Leute, denen ich folge ODER meine eigenen
$sql = "
  SELECT p.id, p.creator_id, p.title, p.content, p.media_type, p.media_image, p.category, p.created_at,
         u.username, u.profile_image
  FROM Posts p
  JOIN Users u ON p.creator_id = u.id
  WHERE p.creator_id = :me
     OR p.creator_id IN (SELECT followed_id FROM Follows WHERE follower_id = :me2)
  ORDER BY p.created_at DESC
  LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':me',  $userId, PDO::PARAM_INT);
$stmt->bindValue(':me2', $userId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$feedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total für Pagination
$sqlCount = "
  SELECT COUNT(*) 
  FROM Posts p
  WHERE p.creator_id = :me
     OR p.creator_id IN (SELECT followed_id FROM Follows WHERE follower_id = :me2)
";
$sc = $pdo->prepare($sqlCount);
$sc->bindValue(':me',  $userId, PDO::PARAM_INT);
$sc->bindValue(':me2', $userId, PDO::PARAM_INT);
$sc->execute();
$total      = (int) $sc->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>humplore – Explore (Suche + Feed)</title>

  <!-- Fonts & Basis -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand-prim: #4b573e; /* Grün */
      --brand-sec:  #580F41; /* Violett */
      --bg:         #f6f7f6;
      --text:       #2c2f2b;
      --muted:      #6f7a69;
      --card:       #fff;
      --border:     rgba(0,0,0,.08);
      --radius:     14px;
      --radius-lg:  18px;
      --shadow-sm:  0 2px 8px rgba(0,0,0,.08);
      --shadow-md:  0 6px 16px rgba(0,0,0,.10);
      --shadow-lg:  0 14px 34px rgba(0,0,0,.12);
      --focus:      0 0 0 3px rgba(88,15,65,.20);
      --container:  1120px;

      --header-h:   84px;
      --brand-img-h:56px;
    }
    @media (max-width:720px){
      :root { --header-h:72px; --brand-img-h:48px; }
    }

    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      background:var(--bg); color:var(--text);
      font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      line-height:1.6; padding-bottom:84px;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    }
    h1,h2,h3{font-family:'DM Serif Display',Georgia,serif}
    a{color:inherit;text-decoration:none}
    img{display:block;max-width:100%}

    /* Header */
    header{
      background:var(--card); position:sticky; top:0; z-index:60;
      height:var(--header-h); display:grid; place-items:center;
      border-bottom:1px solid var(--border); box-shadow:0 2px 4px rgba(0,0,0,.04);
    }
    header .brand{display:inline-flex;align-items:center;height:100%}
    header .brand img{height:var(--brand-img-h);width:auto;display:block}

    /* Banner */
    .banner{
      height:168px; display:grid; place-items:center; position:relative; overflow:hidden;
      background:linear-gradient(135deg,var(--brand-prim),var(--brand-prim));
    }
    .banner::after{
      content:""; position:absolute; inset:0;
      background:radial-gradient(1100px 300px at 50% -22%,rgba(255,255,255,.12),transparent 60%);
      z-index:0;
    }

    /* Container */
    .container{ max-width:var(--container); margin:-92px auto 32px; padding:0 18px; }
    @media (max-width:720px){ .container{ margin:-76px auto 24px; padding:0 14px; } }

    /* Sticky Search Card */
    .search-card{
      position:sticky; top:12px; z-index:55;
      background:rgba(255,255,255,.82); backdrop-filter:saturate(1.1) blur(10px);
      border:1px solid var(--border); border-radius:var(--radius-lg);
      box-shadow:var(--shadow-md); padding:16px;
    }
    @media (prefers-reduced-transparency: reduce){
      .search-card{ backdrop-filter:none; background:var(--card); }
    }
    .search-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
    .search-title{ font-size:1.36rem; color:#25301f; font-weight:800 }
    .search-sub{ color:var(--muted); font-weight:700; font-size:.95rem }
    .search-row{ display:grid; grid-template-columns:1fr auto; gap:10px; }
    .input-wrap{ position:relative }
    .input-wrap input[type="text"]{
      width:100%; padding:12px 14px 12px 44px; border:2px solid #e7e9e5; border-radius:12px; background:#f7f8f6;
      font-size:1rem; transition:border .2s, box-shadow .2s, background .2s;
      background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="%236f7a69" viewBox="0 0 24 24"><path d="M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12A6 6 0 0110 4z"/></svg>');
      background-repeat:no-repeat; background-position:12px center;
    }
    .input-wrap input[type="text"]:focus{ background:#fff; border-color:var(--brand-sec); box-shadow:var(--focus); outline:0; }
    .btn{
      appearance:none; border:0; cursor:pointer; font-weight:800;
      background:var(--brand-sec); color:#fff; border-radius:12px; padding:12px 16px;
      box-shadow:0 10px 22px rgba(88,15,65,.24); transition:transform .06s, background .2s;
    }
    .btn:hover{ background:#4b1541 } .btn:active{ transform:translateY(1px) }

    /* Badges / chips */
    .toolbar, .chipbar{ display:flex; align-items:center; gap:8px; flex-wrap:wrap }
    .toolbar{ justify-content:space-between; margin-top:12px }
    .badge, .chip{
      display:inline-flex; align-items:center; gap:8px; background:#eef3ea; color:#3e4736;
      border:1px solid #dbe6d6; padding:7px 10px; border-radius:999px; font-weight:700; font-size:.9rem;
    }
    .badge .count{ display:inline-grid; place-items:center; min-width:26px; height:22px; padding:0 6px;
      border-radius:999px; background:#e6efe2; color:#2e3727; font-size:.83rem; }
    .result-count{ color:#5f6c57; font-weight:700; font-size:.96rem }

    /* Section heads */
    .section-head{ display:flex; align-items:end; justify-content:space-between; margin:18px 4px 12px; }
    .section-head h3{ color:var(--brand-prim); font-size:1.28rem }
    .section-head small{ color:var(--muted); font-weight:700 }

    /* Profile Cards */
    .profile-result{
      display:flex; align-items:center; gap:14px; background:var(--card); border:1px solid var(--border);
      border-radius:14px; padding:14px 16px; margin-bottom:10px; box-shadow:var(--shadow-sm);
      transition:transform .12s ease, box-shadow .2s ease;
    }
    .profile-result:hover{ transform:translateY(-2px); box-shadow:var(--shadow-md); }
    .profile-avatar{ width:56px; height:56px; border-radius:50%; overflow:hidden; display:grid; place-items:center;
      color:#fff; font-weight:800; background:var(--brand-prim); border:3px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,.08); flex:0 0 56px; }
    .profile-avatar img{ width:100%; height:100%; object-fit:cover }
    .profile-info{ flex:1; min-width:0 }
    .profile-name{ font-weight:800; color:#283221; font-size:1.04rem }
    .profile-topic{ color:#6b7466; font-size:.93rem; margin-top:2px }
    .profile-follow{ color:#7e8779; font-size:.9rem; margin-top:2px }

    /* Posts (Masonry) */
    .posts-masonry{ column-count:1; column-gap:14px; }
    @media (min-width:860px){ .posts-masonry{ column-count:2; } }
    .post-card{
      background:transparent; border:0; box-shadow:none; border-radius:16px; padding:0;
      display:inline-block; width:100%; margin:0 0 12px;
      break-inside:avoid; -webkit-column-break-inside:avoid; -moz-column-break-inside:avoid;
      contain:paint; isolation:isolate;
    }
    .post-inner{
      padding:16px; border-radius:16px; border:1px solid var(--border); background:var(--card); box-shadow:var(--shadow-sm);
      transition:transform .12s, box-shadow .2s; transform:translateZ(0); will-change:transform;
    }
    .post-card .post-inner:hover, .post-card .post-inner:focus-within{ transform:translateY(-2px); box-shadow:var(--shadow-md); }
    .post-header{ display:flex; align-items:center; gap:12px; margin-bottom:10px }
    .post-avatar{
      width:46px; height:46px; border-radius:50%; overflow:hidden; border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,.08);
      display:grid; place-items:center; background:#eceeea; color:#6c6c6c; font-weight:800; flex:0 0 46px;
    }
    .post-title{ font-size:1.22rem; color:#1f241b; margin:6px 0 8px }
    .post-meta .author{ color:var(--brand-sec); font-weight:800; font-size:.95rem }
    .post-meta .date{ color:#879184; font-size:.85rem; margin-left:8px }
    .post-image{ border-radius:12px; overflow:hidden; border:1px solid var(--border); margin:10px 0 }
    .post-content{ font-family:'Lora', Georgia, serif; color:#42473e; font-size:1.02rem; line-height:1.68 }
    .post-content p{ margin:0 0 1.1em; }
    .more-link{ color:#4a4d47; font-weight:800; font-size:.92rem }
    .more-content{ display:none }
    .post-readmore, .post-readless{ display:block; margin-top:6px; }

    /* Empty + Pager */
    .empty{
      display:grid; place-items:center; text-align:center; background:#fbfdf9; border:1px dashed #dbe6d4;
      color:#6b7963; border-radius:16px; padding:30px; margin-top:8px;
    }
    .pager{ display:flex; align-items:center; justify-content:center; gap:10px; margin-top:18px; }
    .btn-ghost{ background:#eef3ea; color:#3e4736; border:1px solid #dbe6d6; box-shadow:none; padding:10px 14px; border-radius:12px; font-weight:800; }

    /* Bottom-Nav */
    .bottom-nav{
      position:fixed; left:50%; transform:translateX(-50%); bottom:12px;
      width:calc(100% - 40px); max-width:520px; height:44px;
      background:linear-gradient(90deg, var(--brand-prim), var(--brand-prim));
      border-radius:999px; display:flex; justify-content:space-evenly; align-items:center;
      box-shadow:var(--shadow-lg); z-index:1000; padding:0 8px; border:1px solid rgba(255,255,255,.14);
    }
    .bottom-nav a{
      color:#fff; font-weight:900; font-size:.95rem; padding:6px 12px; border-radius:10px;
      transition:background .2s, transform .06s, opacity .2s; opacity:.96; text-shadow:0 1px 2px rgba(0,0,0,.25);
    }
    .bottom-nav a:hover{ background:rgba(255,255,255,.1); transform:translateY(-1px); }
    .bottom-nav a.is-active{ background:rgba(255,255,255,.18); }

    @media (prefers-reduced-motion: reduce){
      .post-card, .bottom-nav a, .btn{ transition:none !important; }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
    <a href="platform.php" class="brand" aria-label="Humplore – Startseite">
      <img src="/pic/humplore-logo.png" alt="humplore Logo">
    </a>
  </header>

  <!-- Banner -->
  <div class="banner" role="img" aria-label="Explore-Banner"></div>

  <!-- Inhalt -->
  <main class="container">
    <!-- Sticky Suche -->
    <section class="search-card" aria-label="Suche">
      <div class="search-header">
        <h3 class="search-title">Suche</h3>
        <div class="search-sub">
          <?= $searchQuery !== '' ? e($totalFound . ' Treffer insgesamt') : 'Tippe einen Begriff ein oder scrolle zum Feed' ?>
        </div>
      </div>

      <form method="GET" action="" class="search-row" role="search" aria-label="Suchformular">
        <div class="input-wrap">
          <input type="text" name="q" placeholder="Suche nach Profilen oder Beiträgen…" value="<?= e($searchQuery) ?>" aria-label="Suchbegriff" autofocus>
        </div>
        <button class="btn" type="submit" aria-label="Suchen">Suchen</button>
      </form>

      <?php if ($searchQuery !== ''): ?>
        <div class="toolbar" aria-live="polite">
          <div class="badges">
            <span class="badge" title="Gefundene Profile">Profile <span class="count"><?= (int)$countProfiles ?></span></span>
            <span class="badge" title="Gefundene Beiträge">Beiträge <span class="count"><?= (int)$countPosts ?></span></span>
          </div>
          <div class="result-count"><?= (int)$totalFound ?> Ergebnis<?= $totalFound === 1 ? '' : 'se' ?></div>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($searchQuery !== ''): ?>
      <!-- Profile-Ergebnisse -->
      <section id="profiles" class="results" aria-labelledby="profiles-title">
        <div class="section-head">
          <h3 id="profiles-title">Profile</h3>
          <small><?= (int)$countProfiles ?> Treffer</small>
        </div>
        <?php if ($countProfiles === 0): ?>
          <div class="empty">Keine passenden Profile gefunden.</div>
        <?php else: ?>
          <?php foreach ($resultsProfiles as $p): ?>
            <a class="profile-result" href="profile.php?user_id=<?= (int)$p['id'] ?>">
              <div class="profile-avatar" aria-hidden="true">
                <?php if (!empty($p['profile_image']) && $p['profile_image'] !== 'default_profile.png'): ?>
                  <img src="data:image/jpeg;base64,<?= base64_encode($p['profile_image']) ?>" alt="Profilbild von @<?= e($p['username']) ?>">
                <?php else: ?>
                  <?= strtoupper(substr($p['username'], 0, 1)) ?>
                <?php endif; ?>
              </div>
              <div class="profile-info">
                <div class="profile-name">@<?= e($p['username']) ?></div>
                <div class="profile-topic"><?= e((string)$p['main_topic']) ?></div>
                <div class="profile-follow"><?= (int)$p['follower_count'] ?> Follower</div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <!-- Beitrags-Ergebnisse -->
      <section id="posts" class="results" aria-labelledby="posts-title" style="margin-top:14px">
        <div class="section-head">
          <h3 id="posts-title">Beiträge</h3>
          <small><?= (int)$countPosts ?> Treffer</small>
        </div>

        <?php if ($countPosts === 0): ?>
          <div class="empty">Keine Beiträge gefunden.</div>
        <?php else: ?>
          <div class="posts-masonry">
            <?php foreach ($resultsPosts as $post): ?>
              <article class="post-card">
                <div class="post-inner">
                  <header class="post-header">
                    <div class="post-avatar">
                      <?php if (!empty($post['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($post['profile_image']) ?>" alt="Profilbild von @<?= e($post['username']) ?>">
                      <?php else: ?>
                        <?= strtoupper(substr($post['username'], 0, 1)) ?>
                      <?php endif; ?>
                    </div>
                    <div class="post-meta">
                      <span class="author">@<?= e($post['username']) ?></span>
                      <span class="date"><?= e(date("d.m.Y H:i", strtotime($post['created_at']))) ?></span>
                    </div>
                  </header>

                  <h3 class="post-title"><?= e($post['title']) ?></h3>

                  <?php if (!empty($post['media_image'])): ?>
                    <div class="post-image">
                      <img src="data:image/jpeg;base64,<?= base64_encode($post['media_image']) ?>" alt="Beitragsbild">
                    </div>
                  <?php endif; ?>

                  <?php
                  $raw = (string) ($post['content'] ?? '');
                  $raw = str_replace(["\r\n", "\r"], "\n", $raw);
                  $raw = preg_replace("/[ \t]+$/m", "", $raw);
                  $raw = preg_replace("/\n{3,}/", "\n\n", trim($raw));

                  $limit = 120;
                  $pid   = 'res-' . (int)$post['id'];
                  $hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1);
                  $pv = mb_substr($raw, 0, $limit);
                  $rs = mb_substr($raw, $limit);

                  $renderParagraphs = function (string $txt): void {
                    $txt = str_replace(["\r\n", "\r"], "\n", $txt);
                    $blocks = preg_split("/\n\s*\n/", $txt);
                    foreach ($blocks as $p) {
                      $p = trim(preg_replace("/\n+/", " ", $p));
                      if ($p === '') continue;
                      echo '<p>' . e($p) . '</p>';
                    }
                  };
                  ?>

                  <div class="post-content">
                    <?php if (!$hasParagraphs): ?>
                      <p>
                        <?= e(str_replace(["\r\n", "\r", "\n"], " ", $pv)) ?>
                        <?php if (mb_strlen($raw) > $limit): ?>
                          <span class="more-content" id="more-<?= e($pid) ?>" style="display:none">
                            <?= e(str_replace(["\r\n", "\r", "\n"], " ", $rs)) ?>
                          </span>
                        <?php endif; ?>
                      </p>
                    <?php else: ?>
                      <?php $renderParagraphs($pv); ?>
                      <?php if (mb_strlen($raw) > $limit): ?>
                        <div class="more-content" id="more-<?= e($pid) ?>" style="display:none">
                          <?php $renderParagraphs($rs); ?>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>

                    <?php if (mb_strlen($raw) > $limit): ?>
                      <div class="post-readmore" id="more-row-<?= e($pid) ?>">
                        … <a href="#" class="more-link" onclick="toggleMore('<?= e($pid) ?>', event)">mehr lesen</a>
                      </div>
                      <div class="post-readless" id="less-row-<?= e($pid) ?>" style="display:none">
                        <a href="#" class="more-link" onclick="toggleMore('<?= e($pid) ?>', event)">weniger lesen</a>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <!-- FEED (immer sichtbar, unter der Suche bzw. Ergebnissen) -->
    <section class="feed-toolbar" aria-label="Feed-Info" style="margin-top:18px">
      <div>
        <div class="feed-title">Neueste Beiträge</div>
        <div class="feed-sub"><?= (int)$total ?> Beiträge im Feed</div>
      </div>
      <div class="chipbar">
        <span class="chip" title="Gefolgte + eigene">Folge ich • inkl. ich</span>
        <span class="chip" title="Sortierung">Sortiert: Neueste zuerst</span>
        <span class="chip">Seite <?= (int)$page ?>/<?= (int)$totalPages ?></span>
      </div>
    </section>

    <section class="results" style="margin-top:14px" aria-labelledby="feed-title">
      <div class="section-head" style="display:flex;align-items:end;justify-content:space-between;margin:18px 4px 12px">
        <h3 id="feed-title" style="color:var(--brand-prim);font-size:1.28rem">Beiträge der Accounts, denen du folgst</h3>
        <small style="color:var(--muted);font-weight:700"><?= count($feedPosts) ?> angezeigt</small>
      </div>

      <?php if (empty($feedPosts)): ?>
        <div class="empty">
          <div style="font-weight:800;margin-bottom:6px">Noch keine Beiträge im Feed.</div>
          <div>Folge Creator*innen oder erstelle selbst einen Beitrag.</div>
        </div>
      <?php else: ?>
        <div class="posts-masonry">
          <?php foreach ($feedPosts as $post): ?>
            <article class="post-card">
              <div class="post-inner">
                <div class="post-header">
                  <a class="post-avatar" href="profile.php?user_id=<?= (int)$post['creator_id'] ?>" aria-label="Zum Profil">
                    <?php if (!empty($post['profile_image']) && $post['profile_image'] !== 'default_profile.png'): ?>
                      <img src="data:image/jpeg;base64,<?= base64_encode($post['profile_image']) ?>" alt="Profilbild von @<?= e($post['username']) ?>">
                    <?php else: ?>
                      <?= strtoupper(substr($post['username'], 0, 1)) ?>
                    <?php endif; ?>
                  </a>
                  <div class="post-meta">
                    <a class="author" href="profile.php?user_id=<?= (int)$post['creator_id'] ?>">@<?= e($post['username']) ?></a>
                    <span class="date">• <?= e(htime($post['created_at'])) ?></span>
                    <?php if (!empty($post['category'])): ?>
                      <span class="date"> • <?= e($post['category']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>

                <?php if (!empty($post['title'])): ?>
                  <h3 class="post-title"><?= e($post['title']) ?></h3>
                <?php endif; ?>

                <?php if (!empty($post['media_type']) && $post['media_type'] === 'image' && !empty($post['media_image'])): ?>
                  <div class="post-image">
                    <img src="data:image/jpeg;base64,<?= base64_encode($post['media_image']) ?>" alt="Beitragsbild">
                  </div>
                <?php endif; ?>

                <?php
                $raw = (string) ($post['content'] ?? '');
                $raw = str_replace(["\r\n", "\r"], "\n", $raw);
                $raw = preg_replace("/[ \t]+$/m", "", $raw);
                $raw = trim($raw);

                $limit = 160;
                $pid   = (int)$post['id'];
                $hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1);
                $pv = mb_substr($raw, 0, $limit);
                $rs = mb_substr($raw, $limit);

                $renderParagraphs = function (string $txt): void {
                  $txt = str_replace(["\r\n", "\r"], "\n", $txt);
                  $blocks = preg_split("/\n\s*\n/", $txt);
                  foreach ($blocks as $p) {
                    $p = trim(preg_replace("/\n+/", " ", $p));
                    if ($p === '') continue;
                    echo '<p>' . e($p) . '</p>';
                  }
                };
                ?>

                <?php if ($raw !== ''): ?>
                  <div class="post-content">
                    <?php if (!$hasParagraphs): ?>
                      <p>
                        <?= e(str_replace(["\r\n", "\r", "\n"], " ", $pv)) ?>
                        <?php if (mb_strlen($raw) > $limit): ?>
                          <span class="more-content" id="more-<?= $pid ?>" style="display:none">
                            <?= e(str_replace(["\r\n", "\r", "\n"], " ", $rs)) ?>
                          </span>
                        <?php endif; ?>
                      </p>
                    <?php else: ?>
                      <?php $renderParagraphs($pv); ?>
                      <?php if (mb_strlen($raw) > $limit): ?>
                        <div class="more-content" id="more-<?= $pid ?>" style="display:none">
                          <?php $renderParagraphs($rs); ?>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>

                    <?php if (mb_strlen($raw) > $limit): ?>
                      <div class="post-readmore" id="more-row-<?= $pid ?>">
                        … <a href="#" class="more-link" onclick="toggleMore('<?= $pid ?>', event)">mehr lesen</a>
                      </div>
                      <div class="post-readless" id="less-row-<?= $pid ?>" style="display:none">
                        <a href="#" class="more-link" onclick="toggleMore('<?= $pid ?>', event)">weniger lesen</a>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pager" aria-label="Seitennavigation">
          <?php if ($page > 1): ?>
            <a class="btn-ghost" href="?<?= $searchQuery !== '' ? 'q=' . urlencode($searchQuery) . '&' : '' ?>page=<?= $page - 1 ?>">Zurück</a>
          <?php endif; ?>
          <span style="color:var(--muted);font-weight:800">Seite <?= (int)$page ?> von <?= (int)$totalPages ?></span>
          <?php if ($page < $totalPages): ?>
            <a class="btn" href="?<?= $searchQuery !== '' ? 'q=' . urlencode($searchQuery) . '&' : '' ?>page=<?= $page + 1 ?>">Weiter</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>

  <!-- Bottom-Navbar -->
  <nav class="bottom-nav" aria-label="Hauptnavigation">
    <a href="platform.php" id="nav-home">Home</a>
    <a href="explore.php" id="nav-explore">Explore</a>
    <a href="posten.php" id="nav-plus">+</a>
    <a href="news.php" id="nav-news">News</a>
    <a href="profile.php?user_id=<?= (int)$_SESSION['user_id'] ?>" id="nav-profile">Profil</a>
  </nav>

  <script>
    // Mehr/Weniger-Toggle (einheitlich für beide Bereiche)
    function toggleMore(id, ev){
      ev.preventDefault();
      const more    = document.getElementById('more-' + id);
      const moreRow = document.getElementById('more-row-' + id);
      const lessRow = document.getElementById('less-row-' + id);

      const isHidden = !more || more.style.display === '' || more.style.display === 'none';
      const isSpan   = more && more.tagName === 'SPAN';

      if (isHidden) {
        if (more)    more.style.display = isSpan ? 'inline' : 'block';
        if (moreRow) moreRow.style.display = 'none';
        if (lessRow) lessRow.style.display = 'block';
      } else {
        if (more)    more.style.display = 'none';
        if (moreRow) moreRow.style.display = 'block';
        if (lessRow) lessRow.style.display = 'none';
      }
    }

    // Nav-Active-Status: Explore immer aktiv; optional Home aktiv lassen, wenn keine Suche aktiv ist
    (function markActive(){
      document.getElementById('nav-explore')?.classList.add('is-active');
    })();

    // Keyboard: PgUp/PgDn für Pagination (wie im Feed)
    document.addEventListener('keydown', function (e) {
      const params = new URLSearchParams(window.location.search);
      const page = parseInt(params.get('page') || '1', 10);
      const totalPages = <?= (int)$totalPages ?>;
      if (e.key === 'PageDown') {
        e.preventDefault();
        const next = totalPages > page ? page + 1 : page;
        if (next !== page) {
          params.set('page', String(next));
          window.location.search = params.toString();
        }
      } else if (e.key === 'PageUp') {
        e.preventDefault();
        const prev = page > 1 ? page - 1 : page;
        if (prev !== page) {
          params.set('page', String(prev));
          window.location.search = params.toString();
        }
      }
    });
  </script>
</body>
</html>
