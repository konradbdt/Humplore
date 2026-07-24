<?php
require_once __DIR__ . '/app/bootstrap.php';
humplore_require_login();

$pdo = humplore_db();
date_default_timezone_set('Europe/Berlin');

function e(string $s): string
{
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$isCreator = 0;
$allCategories = [];
$headerSearchQuery = trim((string) ($_GET['q'] ?? ''));

try {
  $st = $pdo->prepare("SELECT is_creator FROM Users WHERE id = ?");
  $st->execute([$userId]);
  $isCreator = (int) ($st->fetchColumn() ?? 0);
} catch (Throwable $e) {
  $isCreator = 0;
}

try {
  $allCategories = $pdo->query("SELECT id, name FROM Categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $allCategories = [];
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>humplore - News</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand-prim: #6a743a;
      --brand-sec: #580F41;
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
      --container: 1120px;
      --header-h: 84px;
      --brand-img-h: 56px;
      --gap: 16px;
    }

    @media (max-width:720px) {
      :root {
        --header-h: 72px;
        --brand-img-h: 48px;
      }
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      height: 100%;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    h1,
    h2,
    h3 {
      font-family: 'DM Serif Display', Georgia, serif;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    img {
      display: block;
      max-width: 100%;
    }

    header {
      background: var(--card);
      position: sticky;
      top: 0;
      z-index: 60;
      min-height: var(--header-h);
      border-bottom: 1px solid var(--border);
      box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
      padding: 14px 20px;
    }

    .header-inner {
      max-width: 1360px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: var(--brand-img-h);
    }

    header .brand {
      display: inline-flex;
      align-items: center;
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
    }

    header .brand img {
      height: var(--brand-img-h);
      width: auto;
    }

    .header-search {
      width: min(620px, calc(100vw - 360px));
    }

    .header-search .search-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 10px;
    }

    .header-spacer {
      display: none;
    }

    .header-search .input-wrap input {
      width: 100%;
      height: 46px;
      padding: 0 14px;
      border-radius: 999px;
      border: 1px solid rgba(46, 58, 37, .14);
      background: rgba(255, 255, 255, .96);
      color: #25301f;
      font-size: .97rem;
      outline: none;
      transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .header-search .input-wrap input:focus {
      border-color: rgba(88, 15, 65, .36);
      box-shadow: 0 0 0 4px rgba(88, 15, 65, .12);
      background: #fff;
    }

    .header-search .btn {
      height: 46px;
      padding: 0 18px;
      border: 0;
      border-radius: 999px;
      background: #2f3729;
      color: #fff;
      font-weight: 800;
      cursor: pointer;
      transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
      box-shadow: 0 10px 24px rgba(25, 35, 19, .16);
    }

    .header-search .btn:hover {
      background: #24301c;
      transform: translateY(-1px);
    }

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
    }

    .banner h2 {
      color: #fff;
      font-size: 2rem;
      text-align: center;
      z-index: 1;
      position: relative;
      text-shadow: 0 2px 4px rgba(0, 0, 0, .35);
    }

    .layout {
      width: 100%;
      margin: -92px auto 32px;
      padding: 0 12px;
      display: grid;
      grid-template-columns: minmax(280px, 340px) minmax(0, 900px);
      gap: var(--gap);
      align-items: start;
      justify-content: center;
    }

    .sidebar {
      position: sticky;
      top: calc(var(--header-h) + 37px);
      margin-top: 50px;
      display: flex;
      justify-content: center;
    }

    .sidebar-card {
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, #ffffff, #f8faf7);
      border: 1px solid #dde3d8;
      border-radius: 16px;
      box-shadow: 0 14px 34px rgba(27, 37, 22, .12);
      padding: 16px;
      width: min(100%, 338px);
    }

    .sidebar-card::before {
      content: none;
    }

    .sidebar-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #ecefea;
    }

    .sidebar-title {
      margin: 0;
      font-size: 1.08rem;
      font-weight: 900;
      color: #27301f;
    }

    .sidebar-section-label {
      margin: 14px 2px 8px;
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #7a8474;
    }

    .side-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .side-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      padding: 10px 11px;
      border-radius: 11px;
      border: 1px solid #e4e8e1;
      background: #fff;
      transition: border-color .15s ease, background .15s ease, transform .15s ease, box-shadow .2s ease;
    }

    .side-item:hover {
      border-color: #cdd4c9;
      background: #f8faf7;
      transform: translateY(-1px);
      box-shadow: 0 8px 18px rgba(24, 34, 19, .08);
    }

    .side-item.is-active {
      border-color: #aebaa7;
      background: linear-gradient(180deg, #f1f6ee, #edf3e9);
      box-shadow: inset 0 0 0 1px rgba(175, 187, 168, .45);
    }

    .side-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .side-icon {
      width: 28px;
      height: 28px;
      border-radius: 8px;
      display: grid;
      place-items: center;
      font-size: 1rem;
      font-weight: 600;
      color: #384231;
      background: #edf1ea;
      flex: 0 0 28px;
    }

    .side-name {
      font-weight: 800;
      font-size: .92rem;
      color: #2f3729;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .side-go {
      width: 24px;
      height: 24px;
      border-radius: 7px;
      display: grid;
      place-items: center;
      background: #f4f6f3;
      border: 1px solid #e2e6e0;
      color: #6b7564;
      font-weight: 700;
      line-height: 1;
      flex: 0 0 24px;
    }

    .sidebar-tip {
      margin-top: 10px;
      color: #7a8474;
      font-size: .8rem;
      line-height: 1.4;
    }

    .news-main {
      min-width: 0;
      margin-top: 50px;
    }

    .cs-card {
      border: 1px solid var(--border);
      border-radius: 16px;
      background: var(--card);
      box-shadow: var(--shadow-sm);
      padding: 28px;
      display: grid;
      place-items: center;
      text-align: center;
      min-height: 260px;
    }

    .cs-title {
      font-size: 1.9rem;
      color: #25301f;
      margin-bottom: 8px;
    }

    .cs-sub {
      color: var(--muted);
      font-weight: 700;
    }

    .loader {
      margin-top: 22px;
      width: 58px;
      height: 58px;
      border: 6px solid #e8eee4;
      border-top: 6px solid var(--brand-sec);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    @media (max-width:720px) {
      header {
        padding: 10px 12px;
      }

      .header-inner {
        display: grid;
        position: static;
        grid-template-columns: 1fr;
        gap: 10px;
        min-height: 0;
      }

      header .brand {
        position: static;
        transform: none;
        justify-self: center;
      }

      .header-search {
        width: 100%;
        justify-self: stretch;
      }

      .layout {
        margin: -76px auto 24px;
        padding: 0 14px;
        grid-template-columns: 1fr;
      }

      .banner {
        height: 132px;
      }

      .banner h2 {
        font-size: 1.55rem;
      }
    }

    @media (max-width: 980px) {
      .sidebar {
        display: none;
      }

      .layout {
        grid-template-columns: 1fr;
      }

      .news-main {
        margin-top: 0;
      }
    }

    @media (min-width: 981px) {
      .layout {
        grid-template-columns: 196px minmax(0, 900px);
        justify-content: center;
      }

      .sidebar {
        width: 100%;
        max-width: none;
        justify-self: stretch;
        justify-content: stretch;
      }

      .sidebar-card {
        width: 100%;
        max-width: 100%;
      }
    }
  </style>
  <link rel="stylesheet" href="css/humplore-redesign.css">
</head>

<body class="page-news">
  <header>
    <div class="header-inner">
      <a href="platform.php" class="brand" aria-label="Humplore - Startseite">
        <img src="/pic/humplore-logo.png" alt="humplore Logo">
      </a>
      <section class="header-search" aria-label="Suche">
        <form method="GET" action="platform.php" class="search-row" role="search" aria-label="Suchformular">
          <div class="input-wrap">
            <input type="text" name="q" placeholder="Suche nach Profilen oder Beitragen..." value="<?= e($headerSearchQuery) ?>"
              aria-label="Suchbegriff">
          </div>
          <input type="hidden" name="mode" value="discover">
          <button class="btn" type="submit" aria-label="Suchen">Suchen</button>
        </form>
      </section>
      <div class="header-spacer" aria-hidden="true"></div>
    </div>
  </header>

  <div class="banner" role="img" aria-label="Newsbanner">
    <h2>News</h2>
  </div>

  <main class="layout">
    <aside class="sidebar" aria-label="Navigation und Kategorien">
      <div class="sidebar-card">
        <div class="sidebar-head">
          <h3 class="sidebar-title">News</h3>
        </div>

        <div class="sidebar-section-label">Navigation</div>
        <div class="side-list">
          <a class="side-item" href="platform.php">
            <span class="side-left"><span class="side-icon" aria-hidden="true">•</span><span class="side-name">Explore</span></span>
            <span class="side-go" aria-hidden="true">→</span>
          </a>
          <?php if ($isCreator === 1): ?>
            <a class="side-item" href="posten.php">
              <span class="side-left"><span class="side-icon" aria-hidden="true">+</span><span class="side-name">Posten</span></span>
              <span class="side-go" aria-hidden="true">→</span>
            </a>
          <?php endif; ?>
          <a class="side-item is-active" href="news.php">
            <span class="side-left"><span class="side-icon" aria-hidden="true">•</span><span class="side-name">News</span></span>
            <span class="side-go" aria-hidden="true">→</span>
          </a>
          <?php if ($isCreator === 1): ?>
            <a class="side-item" href="profile.php?user_id=<?= (int) $userId ?>">
              <span class="side-left"><span class="side-icon" aria-hidden="true">•</span><span class="side-name">Profil</span></span>
              <span class="side-go" aria-hidden="true">→</span>
            </a>
          <?php endif; ?>
        </div>

        <?php if (!empty($allCategories)): ?>
          <div class="sidebar-section-label">Kategorien</div>
          <div class="side-list">
            <?php foreach ($allCategories as $cat): ?>
              <a class="side-item" href="#" onclick="return false;" aria-disabled="true">
                <span class="side-left"><span class="side-icon" aria-hidden="true">•</span><span class="side-name"><?= e((string) $cat['name']) ?></span></span>
                <span class="side-go" aria-hidden="true">→</span>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="sidebar-tip">Kategorien sind auf der News-Seite aktuell ohne Funktion.</div>
        <?php endif; ?>
      </div>
    </aside>

    <section class="news-main" aria-labelledby="cs-title">
      <section class="cs-card" aria-live="polite">
        <h1 id="cs-title" class="cs-title">Coming Soon</h1>
        <p class="cs-sub">Deine Fragen und Kommentare werden hier bald mit den entsprechenden Antworten angezeigt.</p>
        <div class="loader" aria-label="Laedt"></div>
      </section>
    </section>
  </main>
</body>

</html>
