<?php
require_once __DIR__ . '/app/bootstrap.php';
$pdo = humplore_db();

/* ================
   Auth-Guard
   ================ */
humplore_require_login();

/* ================
   Creator-Guard (NEU)
   ================ */
humplore_require_creator($pdo);

/* ================
   CSRF Helper
   ================ */
$csrfToken = humplore_ensure_csrf_token();
$headerSearchQuery = humplore_post_editor_header_query($_GET);

/* ================
   State
   ================ */
$error = '';
$success = '';

$submitState = humplore_post_editor_handle_submission($pdo, humplore_current_user_id(), $_POST, $_FILES);
$error = $submitState['error'];
$success = $submitState['success'];

$active = 'post';

?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Beitrag erstellen â€“ humplore</title>

  <!-- Fonts & Basis wie Suche -->
  <link rel="stylesheet" href="css/styles.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css?family=Lora&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --brand-prim: #6a743a;
      /* GrÃ¼n */
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
      --ok: #245a33;
      --warn: #7a1e1e;

      --header-h: 84px;
      /* HeaderhÃ¶he (grÃ¶ÃŸer) */
      --brand-img-h: 56px;
      /* BildhÃ¶he im Header */

      --bottom-nav-h: 50px;
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
      background:
        radial-gradient(900px 300px at 20% -10%, rgba(88, 15, 65, .06), transparent 60%),
        radial-gradient(900px 300px at 80% -8%, rgba(75, 87, 62, .06), transparent 60%),
        var(--bg);
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      line-height: 1.6;
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
      min-height: var(--header-h);
      padding: 14px 20px;
      border-bottom: 1px solid var(--border);
      box-shadow: 0 2px 4px rgba(0, 0, 0, .04);
    }

    .header-inner {
      max-width: 1360px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: var(--brand-img-h);
    }

    /* Logo-Container + BildgrÃ¶ÃŸe */
    header .brand {
      display: inline-flex;
      align-items: center;
      position: absolute;
      left: 76px;
      top: 50%;
      transform: translateY(-50%);
    }

    header .brand img {
      height: var(--brand-img-h);
      width: auto;
      display: block;
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
      border-bottom: 1px solid rgba(255, 255, 255, .22);
    }

    .banner::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(1100px 300px at 50% -22%, rgba(255, 255, 255, .12), transparent 60%);
    }

    .banner h2 {
      color: #fff;
      font-size: 2rem;
      text-shadow: 0 2px 4px rgba(0, 0, 0, .35);
    }

    /* Container */
    .container {
      max-width: var(--container);
      margin: -96px auto 28px;
      padding: 0 18px calc(var(--bottom-nav-h) + 32px)
    }

    /* Editor Card */
    .editor-card {
      background: rgba(255, 255, 255, .86);
      backdrop-filter: saturate(1.05) blur(10px);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 18px;
      transform: translateY(0);
      transition: transform .2s ease, box-shadow .25s ease;
    }

    .editor-card:hover {
      transform: translateY(-1px);
      box-shadow: 0 16px 40px rgba(0, 0, 0, .14);
    }

    @media (prefers-reduced-transparency: reduce) {
      .editor-card {
        backdrop-filter: none;
        background: var(--card);
      }
    }

    .editor-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 10px;
    }

    .editor-title {
      font-size: 1.42rem;
      color: #25301f;
    }

    .hint {
      color: var(--muted);
      font-weight: 600;
      font-size: .95rem
    }

    /* Pills */
    .pillbar {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin: 8px 2px 10px
    }

    .pill {
      background: #eef3ea;
      color: #3e4736;
      border: 1px solid #dbe6d6;
      padding: 6px 10px;
      border-radius: 999px;
      font-weight: 700;
      font-size: .88rem;
    }

    /* Form */
    .form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
    }

    @media (min-width: 940px) {
      .form-grid {
        grid-template-columns: 1fr 340px;
        align-items: start;
        gap: 16px;
      }
    }

    .field {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 14px;
      box-shadow: var(--shadow-sm);
    }

    .label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-weight: 800;
      color: #3a4532;
      margin-bottom: 8px;
    }

    .badge {
      background: #f0f2f0;
      color: #667260;
      font-size: .82rem;
      padding: 3px 8px;
      border-radius: 8px;
      font-weight: 800;
      border: 1px solid #e4e7e3;
    }

    .input,
    .select,
    .textarea {
      width: 100%;
      border: 2px solid #e7e9e5;
      border-radius: 12px;
      background: #f7f8f6;
      font-size: 1rem;
      padding: 12px 14px;
      transition: border .2s, box-shadow .2s, background .2s;
    }

    .input:focus,
    .select:focus,
    .textarea:focus {
      background: #fff;
      border-color: var(--brand-sec);
      box-shadow: var(--focus);
      outline: 0;
    }

    .textarea {
      min-height: 200px;
      resize: vertical;
      font-family: inherit;
      line-height: 1.6
    }

    .row {
      display: grid;
      grid-template-columns: 1fr 220px;
      gap: 10px;
      align-items: end;
    }

    @media (max-width: 720px) {
      .row {
        grid-template-columns: 1fr
      }
    }

    .btn {
      appearance: none;
      border: 0;
      cursor: pointer;
      font-weight: 900;
      letter-spacing: .2px;
      background: #6a743a;
      color: #fff;
      border-radius: 12px;
      padding: 12px 16px;
      box-shadow: 0 10px 22px rgba(88, 15, 65, .28);
      transition: transform .06s, background .2s, box-shadow .2s;
      font-size: 17px;
    }

    .btn:hover {
      box-shadow: 0 14px 28px rgba(88, 15, 65, .32)
    }

    .btn:active {
      transform: translateY(1px)
    }

    /* Upload Card (Drag & Drop) */
    .media-card {
      background: var(--card);
      border: 1px dashed #dbe6d6;
      border-radius: 16px;
      box-shadow: var(--shadow-sm);
      padding: 14px;
      transition: border-color .2s, background .2s;
    }

    .dropzone {
      display: grid;
      place-items: center;
      text-align: center;
      border: 2px dashed #ccd6c9;
      border-radius: 14px;
      padding: 16px;
      background: #f8faf7;
      color: #536050;
      transition: border-color .2s, background .2s, transform .1s;
    }

    .dropzone.drag {
      border-color: #9db397;
      background: #f2f7f0;
      transform: scale(1.01);
    }

    .dropzone small {
      display: block;
      margin-top: 6px;
      color: #778276;
      font-weight: 700
    }

    .file-meta {
      display: none;
      margin-top: 10px;
      font-size: .92rem;
      color: #5d6a58;
      font-weight: 700;
      word-break: break-all;
    }

    .preview {
      display: none;
      margin-top: 10px;
      border-radius: 12px;
      border: 1px solid var(--border);
      overflow: hidden;
    }

    /* Progress (gefÃ¼hlt) */
    .progress-wrap {
      display: none;
      margin-top: 10px;
      background: #eef3ea;
      border-radius: 999px;
      overflow: hidden;
      height: 8px
    }

    .progress {
      width: 0%;
      height: 100%;
      background: linear-gradient(90deg, var(--brand-prim), #6aa061);
      transition: width .25s ease
    }

    /* Alerts */
    .alert {
      border-radius: 12px;
      padding: 12px 14px;
      font-weight: 700;
      margin: 10px 0 12px;
      border: 1px solid;
      box-shadow: var(--shadow-sm);
    }

    .alert.error {
      background: #fff4f4;
      color: var(--warn);
      border-color: #f3c2c2;
    }

    .alert.success {
      background: #f1fff5;
      color: var(--ok);
      border-color: #bfe8c9;
    }


    /* Responsive Tuning */
    @media (max-width: 720px) {
      .container {
        margin: -80px auto 24px;
        padding: 0 14px;
      }

      .banner {
        height: 136px
      }

      .editor-title {
        font-size: 1.28rem
      }
    }

    @media (prefers-reduced-motion: reduce) {

      .btn,
      .bottom-nav a,
      .editor-card {
        transition: none !important;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <div class="header-inner">
      <a href="platform.php" class="brand" aria-label="Humplore â€“ Startseite">
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


  <!-- Banner -->
  <div class="banner" role="img" aria-label="Erstellen-Banner">
    <h2>Erstellen</h2>
  </div>

  <!-- Inhalt -->
  <main class="container">
    <section class="editor-card" aria-label="Beitrag erstellen">
      <div class="editor-head">
        <h3 class="editor-title">Neuen Beitrag erstellen</h3>

      </div>

      <!-- <div class="pillbar" aria-hidden="true">
        <span class="pill">Minimal & clean</span>
        <span class="pill">Barrierearm</span>
        <span class="pill">Drag & Drop</span>
      </div> -->

      <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert success"><?= e($success) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" id="postForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>" />

        <div class="form-grid">
          <!-- Linke Spalte -->
          <div>
            <div class="field">
              <label for="title" class="label">
                <span>Titel</span>
                <span class="badge" id="titleCount">0 / 120</span>
              </label>
              <input type="text" id="title" name="title" class="input" required maxlength="120"
                placeholder="Kurzer, prÃ¤gnanter Titelâ€¦" autocomplete="off" inputmode="text">
            </div>

            <div class="field" style="margin-top:10px">
              <label for="content" class="label">
                <span>Inhalt</span>
                <span class="badge" id="contentHint">Markdown-freundlich</span>
              </label>
              <textarea id="content" name="content" class="textarea" required
                placeholder="WorÃ¼ber mÃ¶chtest du schreiben?"></textarea>
            </div>

            <div class="field">
              <label for="category" class="label">
                <span>Kategorie</span>
                <span class="badge">WÃ¤hle oder erstelle</span>
              </label>

              <div class="row" style="grid-template-columns: 1fr; gap: 10px;">
                <!-- Auswahl bestehender Kategorien -->
                <select id="category" name="category" class="select" aria-describedby="catHelp">
                  <option value="Allgemein">Allgemein</option>
                  <option value="Alltag">Alltag</option>
                  <option value="Familie">Familie</option>
                  <option value="Liebe">Liebe</option>
                </select>
                <small id="catHelp" class="hint">Du kannst unten auch eine neue Kategorie anlegen.</small>

                <!-- Neue Kategorie erstellen -->
                <div style="display:flex; flex-direction:column; gap:8px;">
                  <label for="category_new" style="font-weight:800; color:#3a4532;">Neue Kategorie (optional)</label>
                  <input type="text" id="category_new" name="category_new" class="input"
                    placeholder="z. B. Training, ErnÃ¤hrung, Mindset â€¦" maxlength="40" inputmode="text" />
                  <small class="hint">2â€“40 Zeichen; erlaubt: Buchstaben, Zahlen, Leerzeichen, - _ & /</small>
                </div>
              </div>
            </div>



            <div class="field" style="margin-top:10px">
              <label class="label">
                <span>Monetarisierung</span>
                <span class="badge">Optional</span>
              </label>
              <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap">
                <label style="display:flex; align-items:center; gap:8px; font-weight:700">
                  <input type="checkbox" id="is_paid" name="is_paid" value="1">
                  Beitrag kostenpflichtig
                </label>
                <div id="price_wrap" style="display:none">
                  <label for="price_eur" style="font-weight:700; margin-right:6px">Preis</label>
                  <input type="number" id="price_eur" name="price_eur" class="input" min="0.50" step="0.10"
                    placeholder="z. B. 2.99" style="max-width:140px">
                  <span style="color:#6f7a69; font-weight:700">EUR</span>
                </div>
              </div>
            </div>
            <div class="field" style="margin-top:12px; display:flex; justify-content:flex-end">
              <button type="submit" class="btn" aria-label="Beitrag verÃ¶ffentlichen">Share!</button>
            </div>

          </div>

          <!-- Rechte Spalte -->
          <div>
            <div class="media-card">
              <label class="label" for="media"><span>Bild hochladen</span><span class="badge">Optional</span></label>

              <div id="dropzone" class="dropzone" tabindex="0" role="button"
                aria-label="Datei hierher ziehen oder klicken">
                <div>
                  <strong>Ziehe ein Bild hierher</strong> oder klicke zum AuswÃ¤hlen
                  <small>JPG/PNG/WebP, max. 5 MB</small>
                </div>
              </div>
              <input type="file" id="media" name="media" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" style="display:none">

              <div class="file-meta" id="fileMeta"></div>

              <div class="progress-wrap" id="progressWrap">
                <div class="progress" id="progressBar"></div>
              </div>

              <div class="preview" id="imagePreviewWrap">
                <img id="imagePreview" alt="Vorschau">
              </div>
            </div>
          </div>
        </div>
      </form>
    </section>
  </main>



  <script>
    const isPaid = document.getElementById('is_paid');
    const priceWrap = document.getElementById('price_wrap');
    const priceInput = document.getElementById('price_eur');
    const categoryNewEl = document.getElementById('category_new');

    function togglePrice() { priceWrap.style.display = isPaid.checked ? 'block' : 'none'; }
    isPaid && isPaid.addEventListener('change', togglePrice);
    togglePrice();

    // ===== Utils
    const $ = (sel, ctx = document) => ctx.querySelector(sel);

    // ===== Title counter
    const titleInput = $('#title');
    const titleCount = $('#titleCount');
    function updateTitleCount() {
      if (!titleInput || !titleCount) return;
      titleCount.textContent = `${titleInput.value.length} / 120`;
    }
    titleInput && titleInput.addEventListener('input', updateTitleCount);
    updateTitleCount();

    // ===== Local autosave (alle 500ms)
    const contentEl = $('#content');
    const categoryEl = $('#category');
    const KEY = 'humplore_post_draft';
    const saveDraft = () => {
      const data = {
        t: titleInput?.value || '',
        c: contentEl?.value || '',
        k: categoryEl?.value || 'Allgemein',
        kn: categoryNewEl?.value || '',
        at: Date.now()
      };
      localStorage.setItem(KEY, JSON.stringify(data));
    };

    const loadDraft = () => {
      try {
        const raw = localStorage.getItem(KEY);
        if (!raw) return;
        const { t, c, k, kn } = JSON.parse(raw);
        if (titleInput && !titleInput.value) titleInput.value = t || '';
        if (contentEl && !contentEl.value) contentEl.value = c || '';
        if (categoryEl && k) categoryEl.value = k;
        if (categoryNewEl && !categoryNewEl.value && kn) categoryNewEl.value = kn;
        updateTitleCount();

      } catch (e) { }
    };
    loadDraft();
    let autosaveTimer = null;
    ['input', 'change'].forEach(evt => {
      [titleInput, contentEl, categoryEl, categoryNewEl].forEach(el => {
        el && el.addEventListener(evt, () => {
          clearTimeout(autosaveTimer);
          autosaveTimer = setTimeout(saveDraft, 500);
        });
      });
    });

    // nach erfolgreichem Submit wird auf profile.php umgeleitet; lokal lÃ¶schen wir beim unload sicherheitshalber nicht

    // ===== Drag & Drop Upload
    const dropzone = $('#dropzone');
    const mediaInput = $('#media');
    const previewWrap = $('#imagePreviewWrap');
    const previewImg = $('#imagePreview');
    const fileMeta = $('#fileMeta');
    const progressWrap = $('#progressWrap');
    const progressBar = $('#progressBar');

    function setPreview(file) {
      if (!file) {
        previewWrap.style.display = 'none';
        fileMeta.style.display = 'none';
        progressWrap.style.display = 'none';
        previewImg.src = '';
        return;
      }
      // Checks
      if (file.size > 5 * 1024 * 1024) {
        alert('Das Bild ist grÃ¶ÃŸer als 5 MB.');
        mediaInput.value = ''; return;
      }
      const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp'];
      if (!allowedImageTypes.includes(file.type)) {
        alert('Nur JPEG-, PNG- und WebP-Bilder sind erlaubt.');
        mediaInput.value = ''; return;
      }
      // Fake progress (gefÃ¼hlte RÃ¼ckmeldung)
      progressWrap.style.display = 'block';
      progressBar.style.width = '0%';
      let p = 0;
      const iv = setInterval(() => {
        p = Math.min(100, p + Math.random() * 22 + 8);
        progressBar.style.width = p + '%';
        if (p >= 100) { clearInterval(iv); setTimeout(() => { progressWrap.style.display = 'none'; }, 250); }
      }, 120);

      // Vorschau
      previewWrap.style.display = 'block';
      previewImg.src = URL.createObjectURL(file);

      // Meta
      fileMeta.textContent = `${file.name} â€¢ ${(file.size / 1024 / 1024).toFixed(2)} MB`;
      fileMeta.style.display = 'block';
    }

    function pickFile() { mediaInput && mediaInput.click(); }

    if (dropzone) {
      dropzone.addEventListener('click', pickFile);
      dropzone.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); pickFile(); } });
      ;['dragenter', 'dragover'].forEach(ev => {
        dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('drag'); });
      });
      ;['dragleave', 'drop'].forEach(ev => {
        dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('drag'); });
      });
      dropzone.addEventListener('drop', e => {
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        // Sync in hidden input
        const dt = new DataTransfer();
        dt.items.add(file);
        mediaInput.files = dt.files;
        setPreview(file);
      });
    }

    mediaInput && mediaInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
      setPreview(file);
    });

    // ===== Active nav link
    (function markActive() {
      document.querySelectorAll('.bottom-nav a').forEach(a => {
        if (a.getAttribute('href') && a.getAttribute('href').indexOf('posten.php') !== -1) {
          a.classList.add('is-active');
        }
      });
    })();

    // ===== Basic client validation on submit
    const form = document.getElementById('postForm');
    form && form.addEventListener('submit', (e) => {
      if (!titleInput.value.trim() || !contentEl.value.trim()) {
        e.preventDefault();
        alert('Bitte fÃ¼lle Titel und Inhalt aus.');
      }
    });
  </script>

  <?php
  // HIER (am Ende des Body) includen:
  require __DIR__ . '/inc/buttomnav.php';
  ?>
</body>

</html>
