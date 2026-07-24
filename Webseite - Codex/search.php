<?php
require_once __DIR__ . '/app/bootstrap.php';
humplore_require_login();

$pdo = humplore_db();

/* ===========================
   Suche (Logik unverändert)
   =========================== */
$searchQuery = '';
$resultsProfiles = [];
$resultsPosts = [];
$suggestions = [];
$relatedTerms = [];
$usedFuzzy = false;
$countProfiles = 0;
$countPosts = 0;
$totalFound = 0;

if (isset($_GET['q'])) {
  $searchQuery = trim($_GET['q']);

  // Beiträge suchen
  $searchData = humplore_search_discovery($pdo, $searchQuery);
  $resultsProfiles = $searchData['resultsProfiles'];
  $resultsPosts = $searchData['resultsPosts'];
  $suggestions = $searchData['suggestions'];
  $relatedTerms = $searchData['relatedTerms'];
  $usedFuzzy = (bool) $searchData['usedFuzzy'];
  $countProfiles = (int) $searchData['countProfiles'];
  $countPosts = (int) $searchData['countPosts'];
  $totalFound = (int) $searchData['totalFound'];
}

// Neueste Beiträge (Startansicht)
$stmtLatest = $pdo->prepare("
  SELECT p.*, u.username, u.profile_image
  FROM Posts p
  JOIN Users u ON p.creator_id = u.id
  ORDER BY p.created_at DESC
  LIMIT 6
");
$stmtLatest->execute();
$latestPosts = $stmtLatest->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Humannlibrary – Suche</title>

  <!-- Fonts & Basis -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <style>
    :root {
      /* Beibehaltung deines Farbschemas */
      --brand-prim: #4b573e;
      /* Grün */
      --brand-sec: #580F41;
      /* Violett */
      --bg: #f6f7f6;
      --text: #2c2f2b;
      --muted: #6f7a69;
      --card: #fff;
      --border: rgba(0, 0, 0, .08);
      --radius: 14px;
      --radius-lg: 18px;
      --shadow-sm: 0 2px 8px rgba(0, 0, 0, .08);
      --shadow-md: 0 6px 16px rgba(0, 0, 0, .10);
      --shadow-lg: 0 14px 34px rgba(0, 0, 0, .12);
      --focus: 0 0 0 3px rgba(88, 15, 65, .20);
      --container: 1120px;

      --header-h: 84px;
      /* Headerhöhe (größer) */
      --brand-img-h: 56px;
      /* Bildhöhe im Header */
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    html,
    body {
      height: 100%
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      line-height: 1.6;
      padding-bottom: 84px;
      /* Platz für Bottom-Nav */
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    h1,
    h2,
    h3 {
      font-family: 'DM Serif Display', Georgia, serif
    }

    a {
      color: inherit;
      text-decoration: none
    }

    img {
      display: block;
      max-width: 100%
    }

    /* Header */
    header {
      background: var(--card);
      position: sticky;
      top: 0;
      z-index: 60;
      height: var(--header-h);
      display: grid;
      place-items: center;
      border-bottom: 1px solid var(--border);
      box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
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
      font-size: 1.22rem;
      color: var(--brand-prim);
      letter-spacing: .2px;
      font-weight: 800
    }

    /* Banner */
    .banner {
      height: 168px;
      display: grid;
      place-items: center;
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, var(--brand-prim), var(--brand-prim));
    }

    .banner::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(1100px 300px at 50% -22%, rgba(255, 255, 255, .12), transparent 60%);
      z-index: 0;
      /* Overlay hinter den Text */
    }

    .banner h2 {
      color: #fff;
      font-size: 2rem;
      text-align: center;
      z-index: 1;
      position: relative;
      text-shadow: 0 2px 4px rgba(0, 0, 0, .35);
      /* bessere Lesbarkeit */
    }

    /* Container */
    .container {
      max-width: var(--container);
      margin: -92px auto 32px;
      padding: 0 18px;
    }

    /* Sticky Search (mit Glas-Effekt) */
    .search-card {
      position: sticky;
      top: 12px;
      z-index: 55;
      background: rgba(255, 255, 255, .82);
      backdrop-filter: saturate(1.1) blur(10px);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      padding: 16px;
    }

    @media (prefers-reduced-transparency: reduce) {
      .search-card {
        backdrop-filter: none;
        background: var(--card);
      }
    }

    .search-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 12px;
    }

    .search-title {
      font-size: 1.36rem;
      color: #25301f;
    }

    .search-sub {
      color: var(--muted);
      font-weight: 600;
      font-size: .95rem
    }

    .search-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 10px;
    }

    .input-wrap {
      position: relative
    }

    .input-wrap input[type="text"] {
      width: 100%;
      padding: 12px 14px 12px 44px;
      border: 2px solid #e7e9e5;
      border-radius: 12px;
      background: #f7f8f6;
      font-size: 1rem;
      transition: border .2s, box-shadow .2s, background .2s;
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="%236f7a69" viewBox="0 0 24 24"><path d="M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12A6 6 0 0110 4z"/></svg>');
      background-repeat: no-repeat;
      background-position: 12px center;
    }

    .input-wrap input[type="text"]:focus {
      background: #fff;
      border-color: var(--brand-sec);
      box-shadow: var(--focus);
      outline: 0;
    }

    .btn {
      appearance: none;
      border: 0;
      cursor: pointer;
      font-weight: 800;
      background: var(--brand-sec);
      color: #fff;
      border-radius: 12px;
      padding: 12px 16px;
      box-shadow: 0 10px 22px rgba(88, 15, 65, .24);
      transition: transform .06s, background .2s;
    }

    .btn:hover {
      background: #4b1541
    }

    .btn:active {
      transform: translateY(1px)
    }

    /* Toolbar mit Badges */
    .toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    .badges {
      display: flex;
      gap: 8px;
      flex-wrap: wrap
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #eef3ea;
      color: #3e4736;
      border: 1px solid #dbe6d6;
      padding: 7px 10px;
      border-radius: 999px;
      font-weight: 700;
      font-size: .9rem;
    }

    .badge .count {
      display: inline-grid;
      place-items: center;
      min-width: 26px;
      height: 22px;
      padding: 0 6px;
      border-radius: 999px;
      background: #e6efe2;
      color: #2e3727;
      font-size: .83rem;
    }

    .result-count {
      color: #5f6c57;
      font-weight: 700;
      font-size: .96rem
    }

    .suggestion-panel {
      margin-top: 12px;
      padding: 12px;
      border: 1px solid #dbe6d6;
      border-radius: 12px;
      background: #fbfdf9;
      color: #465241;
    }

    .suggestion-panel strong {
      display: block;
      color: #2f3729;
      font-size: .94rem;
      margin-bottom: 8px;
    }

    .suggestion-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .suggestion-chip {
      display: inline-flex;
      align-items: center;
      border: 1px solid #d8e3d1;
      border-radius: 999px;
      padding: 7px 11px;
      background: #eef6e9;
      color: #3e5630;
      font-size: .88rem;
      font-weight: 800;
    }

    /* Abschnitt-Kopf */
    .section-head {
      display: flex;
      align-items: end;
      justify-content: space-between;
      margin: 18px 4px 12px;
    }

    .section-head h3 {
      color: var(--brand-prim);
      font-size: 1.28rem
    }

    .section-head small {
      color: var(--muted);
      font-weight: 700
    }

    /* Profile Card */
    .profile-result {
      display: flex;
      align-items: center;
      gap: 14px;
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 14px 16px;
      margin-bottom: 10px;
      box-shadow: var(--shadow-sm);
      transition: transform .12s ease, box-shadow .2s ease;
    }

    .profile-result:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .profile-avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      overflow: hidden;
      display: grid;
      place-items: center;
      color: #fff;
      font-weight: 800;
      background: var(--brand-prim);
      border: 3px solid #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, .08);
      flex: 0 0 56px;
    }

    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover
    }

    .profile-info {
      flex: 1;
      min-width: 0
    }

    .profile-name {
      font-weight: 800;
      color: #283221;
      font-size: 1.04rem
    }

    .profile-topic {
      color: #6b7466;
      font-size: .93rem;
      margin-top: 2px
    }

    .profile-follow {
      color: #7e8779;
      font-size: .9rem;
      margin-top: 2px
    }

    /* Masonry-Layout für Beiträge */
    .posts-masonry {
      column-count: 1;
      column-gap: 14px;
    }

    @media (min-width: 860px) {
      .posts-masonry {
        column-count: 2;
      }

    }

    .post-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      box-shadow: var(--shadow-sm);
      padding: 0px;
      transition: transform .12s, box-shadow .2s;
      display: inline-block;
      /* wichtig für Masonry */
      width: 100%;
      margin: 0 0 12px;
      /* Abstand zwischen Karten */
      break-inside: avoid;
      /* verhindert Umbrüche innerhalb einer Karte */
      -webkit-column-break-inside: avoid;
      -moz-column-break-inside: avoid;
      contain: paint;
      isolation: isolate;
    }

    .post-readmore,
    .post-readless {
      display: block;
      margin-top: 6px;
    }

    .post-content p {
      /* optional: sauberer Absatzabstand */
      margin: 0 0 1.1em;
    }


    .post-card .post-inner {
      padding: 16px;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: var(--card);
      box-shadow: var(--shadow-sm);
      transition: transform .12s, box-shadow .2s;
      transform: translateZ(0);
      will-change: transform;
    }

    /* Nur der Inhalt reagiert auf Hover – jede Karte für sich */
    .post-card .post-inner:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }



    .post-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 10px
    }

    .post-avatar {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, .08);
      display: grid;
      place-items: center;
      background: #eceeea;
      color: #6c6c6c;
      font-weight: 800;
      flex: 0 0 46px;
    }

    .post-title {
      font-size: 1.22rem;
      color: #1f241b;
      margin: 6px 0 8px
    }

    .post-meta .author {
      color: var(--brand-sec);
      font-weight: 800;
      font-size: .95rem
    }

    .post-meta .date {
      color: #879184;
      font-size: .85rem;
      margin-left: 8px
    }

    .post-image {
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--border);
      margin: 10px 0
    }

    .post-image img {
      width: 100%;
      height: auto;
      display: block
    }

    .post-content {
      font-family: 'Lora', Georgia, serif;
      color: #42473e;
      font-size: 1.02rem;
      line-height: 1.68
    }

    .more-link {
      color: #4a4d47;
      font-weight: 800;
      font-size: .92rem
    }

    .more-content {
      display: none
    }

    /* Leerer Zustand */
    .empty {
      display: grid;
      place-items: center;
      text-align: center;
      background: #fbfdf9;
      border: 1px dashed #dbe6d4;
      color: #6b7963;
      border-radius: 16px;
      padding: 30px;
      margin-top: 8px;
    }

    /* Skeletons (erstes Laden / schwaches Netz) */
    .skeleton {
      position: relative;
      overflow: hidden;
      background: #eef2ec;
      border-radius: 12px
    }

    .skeleton::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .5), transparent);
      transform: translateX(-100%);
      animation: shimmer 1.2s infinite;
    }

    @keyframes shimmer {
      100% {
        transform: translateX(100%)
      }
    }

    /* Bottom-Nav (fein abgestimmt) */
    .bottom-nav {
      position: fixed;
      left: 50%;
      transform: translateX(-50%);
      bottom: 12px;
      width: calc(100% - 40px);
      max-width: 520px;
      height: 44px;
      background: linear-gradient(90deg, var(--brand-prim), var(--brand-prim));
      border-radius: 999px;
      display: flex;
      justify-content: space-evenly;
      align-items: center;
      box-shadow: var(--shadow-lg);
      z-index: 1000;
      padding: 0 8px;
      border: 1px solid rgba(255, 255, 255, .14);
    }

    .bottom-nav a {
      color: #fff;
      font-weight: 900;
      font-size: .95rem;
      padding: 6px 12px;
      border-radius: 10px;
      transition: background .2s, transform .06s, opacity .2s;
      opacity: .96;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25);
    }

    .bottom-nav a:hover {
      background: rgba(255, 255, 255, .1);
      transform: translateY(-1px);
    }

    .bottom-nav a.is-active {
      background: rgba(255, 255, 255, .18);
    }

    /* Responsiveness */
    @media (max-width: 720px) {
      .container {
        margin: -76px auto 24px;
        padding: 0 14px;
      }

      .banner {
        height: 132px
      }

      .banner h2 {
        font-size: 1.55rem
      }

      .search-row {
        grid-template-columns: 1fr
      }

      /* Masonry bleibt bei 1 Spalte, keine Änderung nötig */
    }

    @media (prefers-reduced-motion: reduce) {

      .post-card,
      .profile-result,
      .bottom-nav a {
        transition: none !important;
      }
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
  <div class="banner" role="img" aria-label="Suchbanner">
  </div>

  <!-- Inhalt -->
  <main class="container">
    <!-- Sticky Suche -->
    <section class="search-card" aria-label="Suche">
      <div class="search-header">
        <h3 class="search-title">Suche</h3>
        <div class="search-sub">
          <?= $searchQuery !== '' ? e($totalFound . ' Treffer insgesamt') : 'Tippe einen Begriff ein' ?>
        </div>
      </div>

      <form method="GET" action="" class="search-row" role="search" aria-label="Suchformular">
        <div class="input-wrap">
          <input type="text" name="q" placeholder="Suche nach Profilen oder Beiträgen…" value="<?= e($searchQuery) ?>"
            aria-label="Suchbegriff" autofocus>
        </div>
        <button class="btn" type="submit" aria-label="Suchen">Suchen</button>
      </form>

      <?php if ($searchQuery !== ''): ?>
        <div class="toolbar" aria-live="polite">
          <div class="badges">
            <span class="badge" title="Gefundene Profile">
              Profile <span class="count"><?= $countProfiles ?></span>
            </span>
            <span class="badge" title="Gefundene Beiträge">
              Beiträge <span class="count"><?= $countPosts ?></span>
            </span>
          </div>
          <div class="result-count">
            <?= $totalFound ?> Ergebnis<?= $totalFound === 1 ? '' : 'se' ?>
          </div>
        </div>

        <?php if ($usedFuzzy || !empty($suggestions)): ?>
          <div class="suggestion-panel" aria-live="polite">
            <?php if ($usedFuzzy): ?>
              <strong>Wir zeigen auch Treffer zu &auml;hnlichen Begriffen.</strong>
            <?php else: ?>
              <strong>Meintest du vielleicht:</strong>
            <?php endif; ?>

            <?php if (!empty($suggestions)): ?>
              <div class="suggestion-list">
                <?php foreach ($suggestions as $suggestion): ?>
                  <a class="suggestion-chip" href="search.php?<?= e(http_build_query(['q' => $suggestion])) ?>">
                    <?= e((string) $suggestion) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>

    <?php if ($searchQuery !== ''): ?>
      <!-- Profile -->
      <section id="profiles" class="results" aria-labelledby="profiles-title">
        <div class="section-head">
          <h3 id="profiles-title">Profile</h3>
          <small><?= $countProfiles ?> Treffer</small>
        </div>

        <?php if ($countProfiles === 0): ?>
          <div class="empty">Keine passenden Profile gefunden.</div>
        <?php else: ?>
          <?php foreach ($resultsProfiles as $p): ?>
            <a class="profile-result" href="profile.php?user_id=<?= (int) $p['id'] ?>">
              <div class="profile-avatar" aria-hidden="true">
                <?php if (!empty($p['profile_image']) && $p['profile_image'] !== 'default_profile.png'): ?>
                  <img src="data:image/jpeg;base64,<?= base64_encode($p['profile_image']) ?>"
                    alt="Profilbild von @<?= e($p['username']) ?>">
                <?php else: ?>
                  <?= strtoupper(substr($p['username'], 0, 1)) ?>
                <?php endif; ?>
              </div>
              <div class="profile-info">
                <div class="profile-name">@<?= e($p['username']) ?></div>
                <div class="profile-topic"><?= e((string) $p['main_topic']) ?></div>
                <div class="profile-follow"><?= (int) $p['follower_count'] ?> Follower</div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <!-- Beiträge -->
      <section id="posts" class="results" aria-labelledby="posts-title" style="margin-top:14px">
        <div class="section-head">
          <h3 id="posts-title">Beiträge</h3>
          <small><?= $countPosts ?> Treffer</small>
        </div>

        <?php if ($countPosts === 0): ?>
          <div class="empty">Keine Beiträge gefunden.</div>
        <?php else: ?>
          <!-- MASONRY-CONTAINER -->
          <div class="posts-masonry">
            <?php foreach ($resultsPosts as $post): ?>
              <article class="post-card skeleton" data-skel="1">
                <div class="post-inner">
                  <header class="post-header">
                    <div class="post-avatar">
                      <?php if (!empty($post['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($post['profile_image']) ?>"
                          alt="Profilbild von @<?= e($post['username']) ?>">
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
                  $pid = 'res-' . (int) $post['id'];
                  $hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1);

                  $pv = mb_substr($raw, 0, $limit);
                  $rs = mb_substr($raw, $limit);

                  // Absätze nur bei Leerzeilen; einfache \n innerhalb eines Absatzes -> Leerzeichen
                  $renderParagraphs = function (string $txt): void {
                    $txt = str_replace(["\r\n", "\r"], "\n", $txt);
                    $blocks = preg_split("/\n\s*\n/", $txt);
                    foreach ($blocks as $p) {
                      $p = trim(preg_replace("/\n+/", " ", $p));
                      if ($p === '')
                        continue;
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
    <?php else: ?>
      <!-- Startansicht: Neueste Beiträge + dezente Skeletons -->
      <section class="results" style="margin-top:14px" aria-labelledby="latest-title">
        <div class="section-head">
          <h3 id="latest-title">Neueste Beiträge</h3>
          <small><?= count($latestPosts) ?> angezeigt</small>
        </div>

        <?php if (empty($latestPosts)): ?>
          <div class="empty">Noch keine Beiträge vorhanden.</div>
        <?php else: ?>
          <!-- MASONRY-CONTAINER -->
          <div class="posts-masonry" id="latest-grid">
            <?php foreach ($latestPosts as $post): ?>
              <article class="post-card skeleton" data-skel="1">
                <div class="post-inner">
                  <header class="post-header">
                    <div class="post-avatar">
                      <?php if (!empty($post['profile_image'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($post['profile_image']) ?>"
                          alt="Profilbild von @<?= e($post['username']) ?>">
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
                  $pid = 'latest-' . (int) $post['id'];
                  $hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1);

                  $pv = mb_substr($raw, 0, $limit);
                  $rs = mb_substr($raw, $limit);

                  $renderParagraphs = function (string $txt): void {
                    $txt = str_replace(["\r\n", "\r"], "\n", $txt);
                    $blocks = preg_split("/\n\s*\n/", $txt);
                    foreach ($blocks as $p) {
                      $p = trim(preg_replace("/\n+/", " ", $p));
                      if ($p === '')
                        continue;
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
  </main>

  <!-- Bottom-Navbar (dein HTML beibehalten) -->
  <nav class="bottom-nav" aria-label="Hauptnavigation">
    <a href="platform.php">Home</a>
    <a href="search.php">Suche</a>
    <a href="posten.php">+</a>
    <a href="news.php">News</a>
    <a href="profile.php?user_id=<?= (int) $_SESSION['user_id'] ?>">Profil</a>
  </nav>

  <script>
    // Mehr/Weniger-Toggle
    function toggleMore(id, ev) {
      ev.preventDefault();
      const more = document.getElementById('more-' + id);
      const moreRow = document.getElementById('more-row-' + id); // "mehr lesen"
      const lessRow = document.getElementById('less-row-' + id); // "weniger lesen"

      const isHidden = !more || more.style.display === '' || more.style.display === 'none';

      if (isHidden) {
        if (more) more.style.display = 'block';
        if (moreRow) moreRow.style.display = 'none';
        if (lessRow) lessRow.style.display = 'block';
      } else {
        if (more) more.style.display = 'none';
        if (moreRow) moreRow.style.display = 'block';
        if (lessRow) lessRow.style.display = 'none';
      }
    }

    // Markiere aktiven Nav-Link
    (function markActive() {
      document.querySelectorAll('.bottom-nav a').forEach(a => {
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('search.php') !== -1) {
          a.classList.add('is-active');
        }
      });
    })();

    // Skeletons nach erstem Paint entfernen
    window.addEventListener('load', function () {
      document.querySelectorAll('[data-skel]').forEach(el => el.classList.remove('skeleton'));
    });

    // Enter auf Input -> submit (Barrierearmut/Fallback)
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        const active = document.activeElement;
        if (active && active.name === 'q') {
          active.form && active.form.submit();
        }
      }
    });
  </script>
</body>

</html>
