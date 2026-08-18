<?php
require_once __DIR__ . '/app/bootstrap.php';

// explore.php â€” Explore mit Schalter (Entdecken/Folge ich), Suche, Likes/Kommentare/Teilen
humplore_require_login();

$pdo = humplore_db();

// Zufalls-Seed pro Session (stabile â€œRandomâ€-Reihenfolge)
$feedSeed = humplore_platform_feed_seed();
$feedSeedInt = (int) sprintf('%u', crc32((string) $feedSeed));
$csrf_token = humplore_ensure_csrf_token();
$userId = humplore_current_user_id();
$isCreator = humplore_current_user_is_creator($pdo);

humplore_platform_handle_comment_submission(
  $pdo,
  $userId,
  $_POST,
  $_GET,
  (string) ($_SERVER['REQUEST_URI'] ?? 'platform.php')
);

$pageState = humplore_platform_page_state($_GET);
$perPage = $pageState['perPage'];
$page = $pageState['page'];
$offset = $pageState['offset'];
$mode = $pageState['mode'];
$searchQuery = $pageState['searchQuery'];
$filters = $pageState['filters'];
$sort = $pageState['sort'];
$activeTopicCategories = humplore_platform_filter_list($filters, 'topicCategories', 'topicCategory');
$activeTopics = humplore_platform_filter_list($filters, 'topics', 'topic');
$activeCategories = humplore_platform_filter_list($filters, 'categories', 'category');
$activeProfileCities = humplore_platform_filter_list($filters, 'profileCities', 'profileCity');
$activeProfileLanguages = humplore_platform_profile_language_filter_list($filters);
$activeTopicCategory = (string) ($activeTopicCategories[0] ?? '');
$activeTopic = (string) ($activeTopics[0] ?? '');
$activeCategory = (string) ($activeCategories[0] ?? '');
$hasActiveFilterChips = $activeTopicCategories !== []
  || $activeTopics !== []
  || $activeCategories !== []
  || $activeProfileCities !== []
  || $activeProfileLanguages !== []
  || $sort !== 'discover';

$filterOptions = humplore_platform_load_filter_options($pdo);
$topicCategoryOptions = $filterOptions['topicCategories'] ?? [];
$sidebarCats = $filterOptions['categories'];
$profileCityOptions = $filterOptions['profileCities'] ?? [];
$profileLanguageOptions = $filterOptions['profileLanguages'] ?? [];
$showBrowseOverview = humplore_platform_should_show_overview($pageState);
$browseOverview = $showBrowseOverview ? humplore_platform_load_overview($pdo) : ['topics' => [], 'categories' => []];

$searchData = humplore_platform_load_search_results($pdo, $pageState['hasSearch'], $searchQuery, $filters, $sort);
$resultsProfiles = $searchData['resultsProfiles'];
$resultsPosts = $searchData['resultsPosts'];
$countProfiles = $searchData['countProfiles'];
$countPosts = $searchData['countPosts'];
$totalFound = $searchData['totalFound'];

$questionsData = humplore_platform_load_questions($pdo);
$randomQuestions = $questionsData['randomQuestions'];
$allQuestions = $questionsData['allQuestions'];

$feedData = humplore_platform_load_feed($pdo, $userId, $mode, $perPage, $offset, $feedSeedInt, $filters, $sort);
$explorePosts = $feedData['explorePosts'];
$totalPages = $feedData['totalPages'];

?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>humplore â€“ Explore</title>
  <meta name="csrf-token" content="<?= e($csrf_token) ?>">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/post-actions.css">
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
      --focus: 0 0 0 3px rgba(88, 15, 65, .20);
      --container: 1120px;
      --header-h: 84px;
      --brand-img-h: 56px;
    }

    :root {
      --container: 1760px;
      --feed-max: 920px;
      --sidebar-w: 330px;
      --right-w: 330px;
      --gap: 16px;
      --explore-right-top: calc(var(--header-h) + 30px);
      --explore-right-margin-top: 100px;
      --explore-right-bottom-gap: 20px;
    }

    .layout {
      max-width: none;
      width: 100%;
      margin: -122px auto 32px;
      padding-left: 8px;
      padding-right: 8px;
      position: relative;
      z-index: 2;

      display: grid;
      grid-template-columns: minmax(320px, 1fr) minmax(0, var(--feed-max)) minmax(320px, 1fr);
      gap: var(--gap);
      align-items: start;
      justify-content: stretch;


      /* sorgt dafÃ¼r, dass das 3-Spalten-Layout als Block zentriert ist */
    }

    /* Feed-Spalte */
    .main-col {
      min-width: 0;
      padding-left: 0;
      margin-top: -18px;
    }

    /* linker Sidebar bleibt sticky */
    .sidebar {
      position: sticky;
      top: calc(var(--header-h) + 87px);
      z-index: 54;
      margin-right: 0;
      margin-top: 100px;
      /* sichtbar tiefer */
      display: flex;
      justify-content: center;
    }

    /* rechter Spacer ist nur Platzhalter */
    .right-spacer {
      position: sticky;
      top: var(--explore-right-top);
      margin-top: var(--explore-right-margin-top);
      align-self: start;
      display: flex;
      justify-content: center;
      height: calc(100vh - var(--explore-right-top) - var(--explore-right-bottom-gap));
      height: calc(100dvh - var(--explore-right-top) - var(--explore-right-bottom-gap));
      max-height: calc(100vh - var(--explore-right-top) - var(--explore-right-bottom-gap));
      max-height: calc(100dvh - var(--explore-right-top) - var(--explore-right-bottom-gap));
      min-height: 0;
    }

    @media (max-width: 1600px) {
      :root {
        --feed-max: 900px;
      }

      .layout {
        grid-template-columns: minmax(280px, 1fr) minmax(0, var(--feed-max)) minmax(280px, 1fr);
      }
    }

    @media (max-width: 1360px) {
      .layout {
        grid-template-columns: 1fr;
        gap: 14px;
        padding-left: 14px;
        padding-right: 14px;
      }

      .sidebar {
        position: static;
        margin-top: 0;
      }

      .right-spacer {
        position: static;
        margin-top: 0;
      }

      .main-col {
        margin-top: 0;
      }

      .search-card {
        position: static;
        top: auto;
      }
    }

    /* Mobile: kompaktere AbstÃ¤nde */
    @media (max-width: 720px) {
      .layout {
        margin: -76px auto 24px;
        padding: 0 12px;
      }

      .main-col {
        margin-top: 0;
      }

      .search-card {
        position: static;
        top: auto;
        padding: 12px;
        border-radius: 14px;
      }

      .post-inner {
        padding: 13px;
        border-radius: 14px;
      }

      .post-title {
        margin: 4px 0 8px;
      }

      .post-image {
        border-radius: 10px;
      }

      .post-actions {
        gap: 6px;
      }
    }

    /* Handy: nur Feed sichtbar, Sideleisten ausblenden */
    @media (max-width: 980px) {
      .sidebar,
      .right-spacer {
        display: none !important;
      }

      .layout {
        grid-template-columns: 1fr !important;
      }

      .main-col {
        width: 100%;
        max-width: 760px;
        margin: 0 auto;
      }

      .post-actions {
        justify-content: space-between;
      }

      .action-button {
        min-width: 44px;
        min-height: 44px;
        padding: 8px 10px;
      }

      .action-button .action-label {
        display: none;
      }
    }

    @media (max-width: 1180px) {
      .post-actions {
        gap: 6px;
      }

      .action-button {
        min-width: 44px;
        min-height: 44px;
        padding: 8px 10px;
        justify-content: center;
      }

      .action-button .action-label {
        display: none;
      }
    }

    @media (max-width: 720px) {
      .main-col {
        margin: 0 auto;
      }
    }

    /* =========================
   Sidebar (Kategorien)
   ========================= */
    .sidebar-card {
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, #ffffff, #f8faf7);
      border: 1px solid #dde3d8;
      border-radius: 16px;
      box-shadow: 0 14px 34px rgba(27, 37, 22, .12);
      padding: 16px;
      min-height: 0;
      display: flex;
      flex-direction: column;
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

    .sidebar-title-wrap {
      min-width: 0;
    }

    .sidebar-title {
      margin: 0;
      font-size: 1.08rem;
      font-weight: 900;
      color: #27301f;
      letter-spacing: .2px;
    }

    .sidebar-clear {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid #d9ddd6;
      background: #fff;
      color: #3a4333;
      font-size: .8rem;
      font-weight: 700;
      line-height: 1;
      white-space: nowrap;
      transition: border-color .15s ease, background .15s ease, color .15s ease;
    }

    .sidebar-clear:hover {
      border-color: #c6ccc2;
      background: #f7f9f6;
      color: #24301c;
    }

    .sidebar-section-label {
      margin: 14px 2px 8px;
      font-size: .78rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #7a8474;
    }

    .sidebar-section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-top: 14px;
      margin-bottom: 8px;
    }

    .sidebar-section-head .sidebar-section-label {
      margin: 0;
    }

    .sidebar-clear--button {
      appearance: none;
      cursor: pointer;
    }

    .cat-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .cat-list-extra {
      display: none;
      margin-top: 8px;
    }

    .cat-list-extra.is-open {
      display: flex;
    }

    .cat-item {
      position: relative;
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

    .cat-item:hover {
      border-color: #cdd4c9;
      background: #f8faf7;
      transform: translateY(-1px);
      box-shadow: 0 8px 18px rgba(24, 34, 19, .08);
    }

    .cat-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .cat-icon {
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

    .cat-name {
      font-weight: 800;
      font-size: .92rem;
      color: #2f3729;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .cat-go {
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

    .cat-item.is-active {
      border-color: #aebaa7;
      background: linear-gradient(180deg, #f1f6ee, #edf3e9);
      box-shadow: inset 0 0 0 1px rgba(175, 187, 168, .45);
    }

    .cat-item.is-active .cat-go {
      border-color: #c9d3c4;
      color: #4f5b47;
    }

    .sidebar-tip {
      margin-top: 10px;
      color: #7a8474;
      font-size: .8rem;
      line-height: 1.4;
    }

    .profile-filter {
      margin-top: 14px;
      border-top: 1px solid #ecefea;
      padding-top: 12px;
    }

    .profile-filter summary {
      cursor: pointer;
      list-style: none;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      color: #2f3729;
      font-size: .86rem;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .profile-filter summary::-webkit-details-marker {
      display: none;
    }

    .profile-filter summary::after {
      content: "+";
      width: 24px;
      height: 24px;
      display: grid;
      place-items: center;
      border: 1px solid #d9ddd6;
      border-radius: 7px;
      background: #fff;
      color: #6b7564;
      font-size: .95rem;
      line-height: 1;
    }

    .profile-filter[open] summary::after {
      content: "-";
    }

    .profile-filter__body {
      display: grid;
      gap: 12px;
      padding-top: 12px;
    }

    .profile-filter__field {
      display: grid;
      gap: 6px;
    }

    .profile-filter__label {
      color: #667160;
      font-size: .78rem;
      font-weight: 800;
    }

    .profile-filter__input {
      width: 100%;
      border: 1px solid #dfe5dc;
      border-radius: 10px;
      background: #fff;
      color: #25301f;
      padding: 9px 10px;
      font: inherit;
      font-size: .86rem;
    }

    .profile-filter__checks {
      display: grid;
      gap: 7px;
    }

    .profile-filter__check {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      border: 1px solid #e4e8e1;
      border-radius: 10px;
      background: #fff;
      padding: 8px 10px;
      color: #33402d;
      font-size: .84rem;
      font-weight: 700;
    }

    .profile-filter__check input {
      width: 16px;
      height: 16px;
      accent-color: #6a743a;
      flex: 0 0 auto;
    }

    .profile-filter__actions {
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .profile-filter__submit {
      appearance: none;
      cursor: pointer;
      border: 1px solid #cfd8c8;
      border-radius: 999px;
      background: #eff6e8;
      color: #486135;
      padding: 7px 12px;
      font-size: .8rem;
      font-weight: 800;
    }

    .rail-card {
      position: relative;
      overflow: hidden;
      background: linear-gradient(180deg, #ffffff, #f8faf7);
      border: 1px solid #dde3d8;
      border-radius: 16px;
      box-shadow: 0 14px 34px rgba(27, 37, 22, .12);
      padding: 16px;
      min-height: 0;
      display: grid;
      grid-template-rows: auto minmax(0, 1fr);
      width: min(100%, 338px);
      height: 100%;
      max-height: 100%;
    }

    .rail-card::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: #ffffff;
    }

    .rail-card h3 {
      margin: 0 0 10px;
      font-size: 1.08rem;
      font-weight: 900;
      color: #27301f;
      letter-spacing: .2px;
    }

    .rail-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 10px;
      flex: 0 0 auto;
    }

    .rail-head h3 {
      margin: 0;
    }

    .rail-more {
      appearance: none;
      border: 1px solid #d7e3cb;
      background: #eff6e8;
      color: #486135;
      border-radius: 999px;
      padding: 6px 12px;
      font-size: .78rem;
      font-weight: 800;
      cursor: pointer;
      transition: background .15s ease, border-color .15s ease, transform .15s ease;
    }

    .rail-more:hover {
      background: #e6f0dc;
      border-color: #c7d7b8;
      transform: translateY(-1px);
    }

    .question-list {
      display: flex;
      flex-direction: column;
      gap: 9px;
      align-self: stretch;
      min-height: 0;
      height: 100%;
      max-height: 100%;
      overflow-y: auto;
      overflow-x: hidden;
      overscroll-behavior: contain;
      scrollbar-gutter: stable;
      padding-right: 6px;
    }

    .question-list::-webkit-scrollbar {
      width: 10px;
    }

    .question-list::-webkit-scrollbar-thumb {
      background: rgba(88, 102, 73, .24);
      border-radius: 999px;
      border: 2px solid rgba(255, 255, 255, .7);
    }

    .question-item {
      border: 1px solid #e7ebe4;
      border-radius: 10px;
      padding: 10px;
      background: #ffffff;
      font-size: 0;
      transition: border-color .15s ease, background .15s ease, transform .15s ease, box-shadow .2s ease;
    }

    .question-item:hover {
      border-color: #cad2c4;
      background: #f8faf7;
      transform: translateY(-1px);
      box-shadow: 0 8px 16px rgba(28, 37, 23, .08);
    }

    .question-item .q {
      font-size: .98rem;
      font-weight: 900;
      color: #25301f;
      line-height: 1.4;
      margin-bottom: 8px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 4;
      -webkit-box-orient: vertical;
    }

    .question-target {
      display: flex;
      align-items: center;
      gap: 10px;
      padding-bottom: 9px;
      margin-bottom: 8px;
      border-bottom: 1px solid #edf1ea;
    }

    .question-target-avatar {
      width: 40px;
      height: 40px;
      flex: 0 0 40px;
      border-radius: 999px;
      object-fit: cover;
      border: 2px solid #d8e2d0;
      background: #f3f6ef;
    }

    .question-target-copy {
      min-width: 0;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .question-target-badges {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 6px 8px;
      margin-top: 3px;
      min-width: 0;
    }

    .question-target-label {
      font-size: .65rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #7b8870;
      line-height: 1.1;
    }

    .question-target-name {
      display: inline-flex;
      align-items: center;
      padding: 4px 9px;
      border-radius: 999px;
      font-size: .74rem;
      font-weight: 800;
      line-height: 1;
      color: #486135;
      background: #e8f2dd;
      border: 1px solid #d5e4c4;
    }

    .question-target .topic-pill {
      margin-left: 0;
      max-width: 100%;
      white-space: normal;
      line-height: 1.2;
      font-size: .74rem;
      padding: 4px 10px;
      word-break: break-word;
    }

    .question-item .meta {
      font-size: .75rem;
      color: #707a6a;
    }

    .question-item .a {
      margin-top: 8px;
      padding: 9px 10px;
      border-radius: 10px;
      background: #f5f8f1;
      border: 1px solid #e4ebdc;
      font-size: .8rem;
      line-height: 1.45;
      color: #465241;
      white-space: normal;
      overflow: visible;
    }

    .question-item .author-meta {
      display: block;
      margin-top: 8px;
      font-size: .75rem;
      color: #707a6a;
    }

    .question-item--preview {
      width: 100%;
      text-align: left;
      cursor: pointer;
      appearance: none;
    }

    .question-item--preview .q {
      margin-bottom: 0;
    }

    .question-item.is-flash {
      border-color: #7aa35a;
      box-shadow: 0 0 0 3px rgba(122, 163, 90, .22), 0 18px 30px rgba(28, 37, 23, .12);
      animation: questionFlash 1.4s ease;
    }

    @keyframes questionFlash {
      0% {
        background: #f6fbef;
      }
      100% {
        background: #ffffff;
      }
    }

    .question-modal__panel {
      width: min(980px, 100%);
    }

    .question-modal__content {
      padding: 18px;
    }

    .question-modal__header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 16px;
    }

    .question-modal__title {
      font-size: 1.35rem;
      font-weight: 900;
      color: #25301f;
      line-height: 1.1;
    }

    .question-modal__sub {
      margin-top: 5px;
      font-size: .85rem;
      color: #667160;
    }

    .category-modal__panel {
      width: min(760px, 100%);
    }

    .category-modal__content {
      padding: 18px;
    }

    .category-modal__header {
      margin-bottom: 14px;
    }

    .category-modal__title {
      font-size: 1.3rem;
      font-weight: 900;
      color: #25301f;
      line-height: 1.1;
    }

    .category-modal__sub {
      margin-top: 4px;
      font-size: .84rem;
      color: #667160;
    }

    .category-modal-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .category-modal-list .cat-item {
      min-width: 0;
    }

    .question-feed {
      column-count: 2;
      column-gap: 14px;
    }

    .question-feed .question-item {
      display: inline-block;
      width: 100%;
      margin: 0 0 14px;
      padding: 14px;
      font-size: inherit;
      break-inside: avoid;
      page-break-inside: avoid;
    }

    .question-feed .question-item .q {
      overflow: visible;
      display: block;
      -webkit-line-clamp: unset;
    }

    @media (max-width: 860px) {
      .category-modal-list {
        grid-template-columns: 1fr;
      }

      .question-feed {
        column-count: 1;
      }

      .question-modal__header {
        align-items: start;
        flex-direction: column;
      }
    }

    @media (max-width: 1360px) {
      .sidebar-card,
      .rail-card {
        min-height: auto;
      }

      .question-list {
        max-height: none;
        overflow: visible;
      }
    }

    @media (min-width: 768px) {
      .right-spacer .rail-card {
        overflow: hidden !important;
      }

      .right-spacer .question-list {
        min-height: 0 !important;
        height: 100% !important;
        max-height: 100% !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
      }
    }





    @media (max-width:720px) {
      :root {
        --header-h: 72px;
        --brand-img-h: 48px;
      }
    }

    /* Final Override:
       Phone = nur Feed
       iPad Air/Pro = Leisten links/rechts */
    @media (max-width: 767px) {
      .layout {
        grid-template-columns: 1fr !important;
      }

      .sidebar,
      .right-spacer {
        display: none !important;
      }
    }

    @media (min-width: 768px) and (max-width: 1366px) {
      :root {
        --explore-right-top: calc(var(--header-h) + 30px);
        --explore-right-margin-top: 70px;
      }

      .layout {
        grid-template-columns: minmax(180px, 22vw) minmax(0, 1fr) minmax(180px, 22vw) !important;
      }

      .sidebar {
        display: flex !important;
        position: sticky !important;
        top: calc(var(--header-h) + 70px) !important;
        margin-top: 70px !important;
        align-self: start;
      }

      .right-spacer {
        display: flex !important;
        position: sticky !important;
        top: var(--explore-right-top) !important;
        margin-top: var(--explore-right-margin-top) !important;
        align-self: start;
      }

    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      font-size: clamp(15px, 1.05vw, 16px);
      line-height: 1.6;
      padding-bottom: 84px
    }

    h1,
    h2,
    h3 {
      font-family: 'DM Serif Display', Georgia, serif
    }

    a {
      text-decoration: none;
      color: inherit
    }

    img {
      display: block;
      max-width: 100%
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
      left: 76px;
      top: 50%;
      transform: translateY(-50%);
    }

    header .brand img {
      height: var(--brand-img-h);
      width: auto
    }

    .header-search {
      width: min(620px, calc(100vw - 360px));
    }

    .header-search .search-row {
      grid-template-columns: minmax(0, 1fr) auto;
    }

    .header-spacer {
      display: none;
    }

    .banner {
      height: 148px;
      display: grid;
      place-items: center;
      position: relative;
      z-index: 1;
      overflow: hidden;
      background: linear-gradient(135deg, var(--brand-prim), var(--brand-prim))
    }

    .banner::after {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(1100px 300px at 50% -22%, rgba(255, 255, 255, .12), transparent 60%)
    }

    .banner h2 {
      color: #fff;
      font-size: 2rem;
      z-index: 1;
      text-shadow: 0 2px 4px rgba(0, 0, 0, .35)
    }

    .container {
      max-width: var(--container);
      margin: -122px auto 32px;
      padding: 0 18px
    }

    @media(max-width:720px) {
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
        top: auto;
        left: auto;
        transform: none;
        justify-self: center;
      }

      .header-search {
        width: 100%;
        justify-self: stretch;
      }

      .header-spacer {
        display: none;
      }

      .container {
        margin: -76px auto 24px;
        padding: 0 14px
      }

      .banner {
        height: 132px
      }

      .banner h2 {
        font-size: 1.55rem
      }
    }

    /* Suche */
    .search-card {
      position: static;
      background: rgba(255, 255, 255, .82);
      backdrop-filter: saturate(1.1) blur(10px);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-md);
      padding: 16px
    }

    @media (prefers-reduced-transparency: reduce) {
      .search-card {
        backdrop-filter: none;
        background: var(--card)
      }
    }

    .search-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 22px;
      height: 50px;
    }

    .search-title {
      font-size: 1.36rem;
      color: #25301f;
      font-weight: 800
    }

    .search-sub {
      color: var(--muted);
      font-weight: 700;
      font-size: .95rem
    }

    .search-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 10px
    }

    .search-card.compact {
      margin-bottom: 16px;
      padding: 14px 16px;
    }

    .search-card.compact .search-header {
      margin-bottom: 0;
    }

    .search-card.compact .search-row {
      display: none;
    }

    .browse-overview {
      margin-bottom: 18px;
      padding: 18px;
      border: 1px solid #dfe6d9;
      border-radius: 18px;
      background: #fff;
      box-shadow: var(--shadow-md);
    }

    .browse-overview__head {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 16px;
    }

    .browse-overview__title {
      margin: 0;
      color: #25301f;
      font-size: 1.28rem;
      font-weight: 900;
    }

    .browse-overview__sub {
      margin: 4px 0 0;
      color: #68735f;
      font-size: .92rem;
      font-weight: 700;
    }

    .browse-section + .browse-section {
      margin-top: 18px;
      padding-top: 16px;
      border-top: 1px solid #e3e9df;
    }

    .browse-section__title {
      margin: 0 0 10px;
      color: #465236;
      font-size: .86rem;
      font-weight: 900;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    .browse-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .browse-card {
      min-width: 0;
      padding: 14px;
      border: 1px solid #e1e7dc;
      border-radius: 12px;
      background: linear-gradient(180deg, #fbfcfa, #f5f7f2);
    }

    .browse-card__head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 12px;
    }

    .browse-card__title {
      margin: 0;
      color: #232b1f;
      font-size: 1.02rem;
      font-weight: 900;
      line-height: 1.25;
      overflow-wrap: anywhere;
    }

    .browse-card__meta {
      margin-top: 3px;
      color: #6e7967;
      font-size: .78rem;
      font-weight: 800;
    }

    .browse-card__more {
      flex: 0 0 auto;
      padding: 7px 10px;
      border-radius: 999px;
      background: #eef3ea;
      color: #3e4736;
      border: 1px solid #d8e2d2;
      font-size: .78rem;
      font-weight: 900;
    }

    .browse-card__block + .browse-card__block {
      margin-top: 12px;
    }

    .browse-card__label {
      margin: 0 0 6px;
      color: #6f7a69;
      font-size: .74rem;
      font-weight: 900;
      text-transform: uppercase;
    }

    .browse-posts,
    .browse-creators {
      display: grid;
      gap: 8px;
    }

    .browse-post {
      padding: 9px 10px;
      border-radius: 10px;
      background: #fff;
      border: 1px solid #e6ebe2;
    }

    .browse-post__title {
      color: #27301f;
      font-size: .9rem;
      font-weight: 900;
      line-height: 1.25;
      overflow-wrap: anywhere;
    }

    .browse-post__excerpt,
    .browse-post__meta {
      margin-top: 4px;
      color: #68735f;
      font-size: .78rem;
      font-weight: 700;
      line-height: 1.35;
    }

    .browse-creators {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .browse-creator {
      display: flex;
      align-items: center;
      gap: 8px;
      min-width: 0;
      color: #25301f;
      font-size: .84rem;
      font-weight: 900;
    }

    .browse-creator__avatar {
      width: 30px;
      height: 30px;
      flex: 0 0 30px;
      border-radius: 50%;
      object-fit: cover;
      background: #eceeea;
      border: 1px solid #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
    }

    .browse-empty {
      color: #7a8474;
      font-size: .82rem;
      font-weight: 700;
    }

    @media (max-width: 760px) {
      .browse-overview {
        padding: 14px;
      }

      .browse-overview__head {
        display: block;
      }

      .browse-grid {
        grid-template-columns: 1fr;
      }
    }

    .search-results-shell {
      margin-bottom: 18px;
      padding: 18px;
      border: 1px solid #dfe6d9;
      border-radius: 20px;
      background: linear-gradient(180deg, rgba(248, 250, 247, .98), rgba(255, 255, 255, .98));
      box-shadow: 0 12px 28px rgba(24, 34, 19, .08);
    }

    .search-results-shell .results + .results {
      margin-top: 18px;
    }

    .results-divider {
      margin: 18px 0 20px;
      padding: 14px 16px;
      border: 1px solid #d9e0d3;
      border-radius: 16px;
      background: linear-gradient(180deg, #f4f7f1, #eef3ea);
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .45);
    }

    .results-divider-title {
      display: block;
      color: #2b3524;
      font-size: 1rem;
      font-weight: 900;
    }

    .results-divider-sub {
      display: block;
      margin-top: 4px;
      color: #6b7564;
      font-size: .87rem;
      font-weight: 700;
    }

    .input-wrap input {
      width: 100%;
      padding: 12px 14px 12px 44px;
      border: 2px solid #e7e9e5;
      border-radius: 12px;
      background: #f7f8f6;
      font-size: 1rem;
      transition: border .2s, box-shadow .2s, background .2s;
      background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="%236f7a69" viewBox="0 0 24 24"><path d="M10 2a8 8 0 105.293 14.293l4.707 4.707 1.414-1.414-4.707-4.707A8 8 0 0010 2zm0 2a6 6 0 110 12A6 6 0 0110 4z"/></svg>');
      background-repeat: no-repeat;
      background-position: 12px center
    }

    .input-wrap input:focus {
      background: #fff;
      border-color: var(--brand-sec);
      box-shadow: var(--focus);
      outline: 0
    }

    .btn {
      appearance: none;
      border: 1px solid #d3dbcf;
      cursor: pointer;
      font-weight: 700;
      background: #eef2ec;
      color: #34402d;
      border-radius: 12px;
      padding: 12px 16px;
      box-shadow: 0 2px 8px rgba(44, 52, 40, .08);
      transition: transform .06s, background .2s, border-color .2s, color .2s
    }

    .btn:hover {
      background: #e5ece2;
      border-color: #c6d0c2;
      color: #2c3626;
    }

    .btn:active {
      transform: translateY(1px)
    }

    /* Schalter */
    .mode-switch {
      margin-top: 12px;
      display: flex;
      gap: 8px;
      flex-wrap: wrap
    }

    .mode-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      font-weight: 900;
      border: 1px solid #dbe6d6;
      background: #eef3ea;
      color: #3e4736
    }

    .mode-btn.is-active {
      background: #6a743a;
      color: #fff;
      border-color: #6a743a;
      box-shadow: 0 8px 18px rgba(75, 87, 62, .18)
    }

    .mode-sub {
      color: #6a7662;
      font-weight: 700;
      font-size: .92rem;
      margin-left: auto
    }

    .toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 12px
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
      font-size: .9rem
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
      font-size: .83rem
    }

    .result-count {
      color: #5f6c57;
      font-weight: 700;
      font-size: .96rem
    }

    .hint {
      color: var(--muted);
      font-weight: 700
    }

    /* Masonry & Cards */
    .posts-masonry {
      column-count: 1;
      column-gap: 14px;
      column-fill: balance;
    }

    @media (min-width:860px) {
      .posts-masonry {
        column-count: 2
      }
    }

    .post-card {
      display: inline-block;
      width: 100%;
      margin: 0 0 12px;
      break-inside: avoid;
      break-inside: avoid-column;
      -webkit-column-break-inside: avoid;
      vertical-align: top;
      contain: none
    }

    .post-inner {
      padding: 16px;
      border-radius: 16px;
      border: 1px solid var(--border);
      background: var(--card);
      box-shadow: var(--shadow-sm);
      transition: transform .12s, box-shadow .2s
    }

    .post-inner:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md)
    }

    .post-card.question-post .post-inner {
      background-color: #f1faf4 !important;
      background-image: linear-gradient(180deg, #f5fcf7 0%, #edf8f1 100%) !important;
      border-color: #cfe5d7 !important;
      box-shadow: 0 12px 26px rgba(74, 130, 97, .08) !important;
    }

    .post-card.question-post .post-actions {
      border-top-color: rgba(94, 145, 116, .18);
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
      flex: 0 0 46px
    }

    .post-title {
      font-size: clamp(1.04rem, 2.4vw, 1.22rem);
      color: #1f241b;
      margin: 6px 0 8px
    }

    .post-meta .author {
      color:  #6a743a;
      font-weight: 800;
      font-size: .95rem
    }

    .post-meta .date {
      color: #879184;
      font-size: .85rem;
      margin-left: 8px
    }

    .post-catline {
      font-size: .85rem;
      color: #6b7280;
      margin-top: 6px
    }

    .post-image {
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid var(--border);
      margin: 10px 0
    }

    .post-content {
      font-family: 'Lora', Georgia, serif;
      color: #42473e;
      font-size: 1.02rem;
      line-height: 1.68
    }

    .post-content p {
      margin: 0 0 1.1em
    }

    .more-link {
      color: #4a4d47;
      font-weight: 800;
      font-size: .92rem
    }

    /*.more-content {
      display: none
    } */

    .post-readmore,
    .post-readless {
      display: block;
      margin-top: 6px
    }

    /* Aktionen */
    .post-actions {
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #eee;
      padding-top: 12px;
      margin-top: 12px
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
      transition: all .2s ease;
      color: #666
    }

    .action-button:hover {
      background: #f5f5f5;
      color: #580F41
    }

    .action-icon {
      width: 20px;
      height: 20px;
      fill: currentColor
    }

    .like-button {
      color: #d8ab10
    }

    .like-button:hover {
      color: #f1bf13;
      background: rgba(255, 212, 59, .1)
    }

    .like-button.liked {
      color: #ffd54a;
      text-shadow: 0 0 10px rgba(255, 213, 74, .45)
    }

    .like-button.liked:hover {
      background: rgba(255, 213, 74, .16)
    }

    .like-button.liked .action-icon {
      filter: drop-shadow(0 0 5px rgba(255, 221, 87, .8)) drop-shadow(0 0 12px rgba(255, 193, 7, .45))
    }

    .save-post-button {
      color: #4f5b47
    }

    .save-post-button:hover {
      color: #2f6f4f;
      background: rgba(47, 111, 79, .10)
    }

    .save-post-button.saved {
      color: #2f6f4f;
      font-weight: 700
    }

    .save-post-button.saved .action-icon {
      filter: drop-shadow(0 0 5px rgba(47, 111, 79, .22))
    }

    .action-count {
      font-weight: 700;
      font-size: .9rem
    }

    .action-label {
      font-size: .9rem
    }

    .comments-section {
      margin-top: 12px;
      border-top: 1px solid #edf0ec;
      padding-top: 12px;
      overflow: hidden;
      max-height: 0;
      transition: max-height .3s ease
    }

    .comments-section.open {
      max-height: 1000px
    }

    .comments-empty {
      padding: 12px;
      font-size: .95rem;
      color: #6b7280;
      background: #f6f8f4;
      border: 1px dashed #e3e8dc;
      border-radius: 10px
    }

    .comment {
      display: grid;
      grid-template-columns: 40px 1fr;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 1px solid #f2f4f0
    }

    .comment:last-child {
      border-bottom: 0
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
      color: #6a743a
    }

    .comment-bubble {
      background: #fff;
      border: 1px solid #e7ebdf;
      border-radius: 14px;
      padding: 10px 12px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .04)
    }

    .comment-header {
      display: flex;
      align-items: baseline;
      gap: 8px;
      margin-bottom: 6px
    }

    .comment-user {
      font-weight: 700;
      color: #374151;
      font-size: .95rem
    }

    .comment-time {
      font-size: .78rem;
      color: #9aa089
    }

    .comment-form {
      display: grid;
      grid-template-columns: 40px 1fr;
      gap: 10px;
      margin-top: 10px
    }

    .comment-input {
      background: #fff;
      border: 1px solid #dfe6d7;
      border-radius: 12px;
      padding: 8px 10px
    }

    .comment-input textarea {
      width: 100%;
      border: 0;
      outline: 0;
      resize: none;
      min-height: 44px;
      font-family: inherit;
      font-size: .95rem;
      color: #374151
    }

    .comment-actions {
      display: flex;
      justify-content: flex-end;
      gap: 8px;
      margin-top: 8px
    }

    .btn-send {
      appearance: none;
      border: 0;
      cursor: pointer;
      border-radius: 10px;
      padding: 8px 12px;
      font-weight: 700;
      background: #6a743a;
      color: #fff
    }

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
      border: 1px solid rgba(255, 255, 255, .14)
    }

    .bottom-nav a {
      color: #fff;
      font-weight: 900;
      font-size: .95rem;
      padding: 6px 12px;
      border-radius: 10px;
      opacity: .96;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25)
    }

    .bottom-nav a.is-active {
      background: rgba(255, 255, 255, .18)
    }

    .top-nav-bar {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      padding: 6px;
      background: linear-gradient(90deg, var(--brand-prim), var(--brand-prim));
      border-radius: 999px;
      box-shadow: var(--shadow-md);
      border: 1px solid rgba(255, 255, 255, .14);
    }

    .top-nav-bar a {
      color: #fff;
      font-weight: 900;
      font-size: .95rem;
      padding: 8px 14px;
      border-radius: 999px;
      opacity: .96;
      text-shadow: 0 1px 2px rgba(0, 0, 0, .25);
    }

    .top-nav-bar a.is-active {
      background: rgba(255, 255, 255, .18);
    }


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
      margin: 10px 0;
      background: #fff6f0;
      border: 1px solid #f3d1b8;
      border-radius: 12px;
      font-weight: 800;
      color: #7a4a1c;
    }

    .lock-btn {
      margin-left: auto;
      appearance: none;
      border: 0;
      cursor: not-allowed;
      border-radius: 10px;
      padding: 8px 12px;
      font-weight: 900;
      background: #6a743a;
      color: #fff;
      opacity: .85;
    }

    .lock-price {
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


    /* Hauptthema-Pill (prominent) */
    .topic-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-left: 10px;
      padding: 4px 12px;
      border-radius: 999px;
      border: 0;
      background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.07) 20%, rgba(255, 255, 255, 0) 42%),
        radial-gradient(circle at 50% 4%, rgba(255, 255, 255, 0.24) 0%, rgba(255, 255, 255, 0.10) 26%, rgba(255, 255, 255, 0) 58%),
        radial-gradient(circle at 14% 78%, rgba(171, 195, 98, 0.38) 0%, rgba(118, 137, 59, 0.16) 28%, rgba(106, 116, 58, 0) 50%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.30) 0%, rgba(255, 255, 255, 0.14) 24%, rgba(255, 255, 255, 0) 46%),
        linear-gradient(180deg, #899858 0%, #738243 50%, #627032 100%);
      color: #fff;
      font-weight: 800;
      font-size: .82rem;
      letter-spacing: .01em;
      text-shadow: 0 1px 1px rgba(36, 43, 18, 0.30);
      box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.24),
        inset 0 10px 12px -12px rgba(255, 255, 255, 0.30),
        inset 0 -1px 0 rgba(63, 74, 27, 0.24),
        0 10px 16px -16px rgba(54, 64, 22, 0.65),
        -8px 0 18px -12px rgba(145, 170, 81, 0.54),
        10px 0 18px -16px rgba(92, 112, 46, 0.28);
      white-space: nowrap;
    }

    /* Kategorie-Pill (dezent) */
    .cat-pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      margin-left: 6px;
      padding: 3px 9px;
      border-radius: 999px;
      background: #f3f5f2;
      border: 1px solid #dce3d7;
      color: #6b7280;
      font-weight: 600;
      font-size: .78rem;
      white-space: nowrap;
    }

    .post-meta {
      min-width: 0;
    }


    /* Zeitstempel unten rechts */
    .post-time {
      margin-top: 10px;
      text-align: right;
      font-size: .82rem;
      color: #9aa089;
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

    /* --- Card-Height & internal scroll on expand --- */
    :root {
      --post-collapsed-max: 520px;
      /* HÃ¶he im "zugeklappten" Zustand */
      --post-expanded-max: 520px;
      /* HÃ¶he im "aufgeklappten" Zustand (bleibt gleich, aber innen scrollt es) */
    }

    /* Die gesamte Card begrenzen */
    .post-inner {
      max-height: var(--post-collapsed-max);
      overflow: hidden;
      /* verhindert, dass Inhalt rauslÃ¤uft */
      display: flex;
      flex-direction: column;
      /* wichtig: Contentbereich kann flexen */
    }

    /* Contentbereich darf im collapsed Zustand NICHT scrollen */
    .post-content {
      overflow: hidden;
    }

    /* Im expanded Zustand bleibt die Card gleich hoch, aber Content wird scrollbar */
    .post-inner.is-expanded {
      max-height: var(--post-expanded-max);
    }

    /* Der Scrollbereich ist der Post-Text (oder optional mit Bild zusammen, siehe Hinweis unten) */
    .post-inner.is-expanded .post-content {
      overflow: auto;
      padding-right: 6px;
      /* Platz fÃ¼r Scrollbar */
    }

    /* Optional: hÃ¼bschere Scrollbar (Chrome/Edge/Safari) */
    .post-inner.is-expanded .post-content::-webkit-scrollbar {
      width: 10px;
    }

    .post-inner.is-expanded .post-content::-webkit-scrollbar-thumb {
      background: rgba(0, 0, 0, .18);
      border-radius: 10px;
      border: 2px solid rgba(255, 255, 255, .6);
    }

    /* =========================
   iPad FIX: Feed nur 1 Spalte (untereinander)
   ========================= */

    /* iPad / Tablets */
    @media (min-width: 768px) and (max-width: 1180px) {

      .posts-masonry {
        column-count: 2 !important;
        column-gap: 18px !important;
      }

      .post-card {
        display: inline-block !important;
        width: 100% !important;
        margin: 0 0 18px !important;
        break-inside: avoid !important;
        contain: none !important;
        overflow: visible !important;
      }

      .posts-masonry,
      .post-card {
        -webkit-column-break-inside: avoid;
      }

      .post-header {
        align-items: flex-start !important;
      }

      .post-meta {
        display: flex !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        gap: 6px 8px !important;
      }

      .post-meta .date {
        margin-left: 0 !important;
      }

      .topic-pill,
      .cat-pill {
        margin-left: 0 !important;
        max-width: 100%;
        white-space: normal !important;
        line-height: 1.2;
      }

      .lock-banner {
        flex-wrap: wrap !important;
        gap: 8px !important;
      }

      .lock-price {
        margin-left: auto;
      }

      .lock-btn {
        display: none !important;
      }
    }

    /* Optional: auch fÃ¼r kleinere Tablets / groÃŸe Phones */
    @media (max-width: 767px) {
      .posts-masonry {
        column-count: 1 !important;
      }
    }

    /* ===== Modal Overlay ===== */
    .post-modal {
      position: fixed;
      inset: 0;
      z-index: 3000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 18px;
      background: rgba(0, 0, 0, .55);
      backdrop-filter: blur(6px);
    }

    .post-modal.open {
      display: flex;
    }

    .post-modal__panel {
      width: min(940px, 100%);
      max-height: min(86vh, 900px);
      overflow: auto;
      background: #fff;
      border-radius: 18px;
      border: 1px solid rgba(255, 255, 255, .18);
      box-shadow: 0 18px 50px rgba(0, 0, 0, .35);
      position: relative;
    }

    .post-modal__close {
      position: sticky;
      top: 0;
      display: flex;
      justify-content: flex-end;
      padding: 10px 12px;
      background: linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(255, 255, 255, .75));
      border-bottom: 1px solid rgba(0, 0, 0, .06);
      z-index: 1;
    }

    .post-modal__close button {
      appearance: none;
      border: 0;
      cursor: pointer;
      padding: 10px 12px;
      border-radius: 12px;
      font-weight: 900;
      background: #111827;
      color: #fff;
    }

    .post-modal__content {
      padding: 12px 12px 18px;
    }

    /* Im Modal soll der Post nicht â€œabgeschnittenâ€ sein */
    .post-modal .post-inner {
      max-height: none !important;
      overflow: visible !important;
    }

    .post-modal .post-content {
      overflow: visible !important;
    }

    /* Optional: im Hintergrund nicht scrollen */
    body.modal-open {
      overflow: hidden;
    }

    /* Readmore-Link immer sichtbar halten */
.post-readmore,
.post-readless{
  position: sticky;
  bottom: 0;
  background: linear-gradient(180deg, rgba(255,255,255,0), rgba(255,255,255,.92) 40%, #fff 100%);
  padding-top: 10px;
}

    @media (min-width: 768px) and (max-width: 1180px) {
      .layout {
        grid-template-columns: minmax(210px, 24vw) minmax(0, 1fr) minmax(210px, 24vw) !important;
        gap: 16px !important;
      }

      .posts-masonry {
        display: block !important;
        column-count: 2 !important;
        column-gap: 18px !important;
      }

      .post-card {
        display: inline-block !important;
        width: 100% !important;
        margin: 0 0 18px !important;
        break-inside: avoid !important;
        -webkit-column-break-inside: avoid !important;
        contain: none !important;
        overflow: visible !important;
      }

      .sidebar-card,
      .rail-card {
        width: min(100%, 292px);
        padding: 12px;
      }

      .sidebar-head,
      .rail-head {
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 8px;
      }

      .sidebar-title,
      .rail-card h3 {
        font-size: .98rem;
      }

      .sidebar-clear,
      .rail-more {
        padding: 5px 9px;
        font-size: .72rem;
      }

      .sidebar-section-label {
        margin: 10px 2px 6px;
        font-size: .68rem;
      }

      .sidebar-section-head {
        margin-top: 10px;
        margin-bottom: 6px;
      }

      .cat-list,
      .question-list {
        gap: 6px;
      }

      .cat-item,
      .question-item {
        padding: 8px 9px;
        border-radius: 10px;
      }

      .cat-left,
      .question-target {
        gap: 8px;
      }

      .cat-icon,
      .question-target-avatar {
        width: 24px;
        height: 24px;
        flex-basis: 24px;
        border-radius: 7px;
        font-size: .85rem;
      }

      .question-target-avatar {
        border-radius: 999px;
      }

      .cat-name {
        font-size: .84rem;
      }

      .cat-go {
        width: 20px;
        height: 20px;
        flex-basis: 20px;
        font-size: .78rem;
      }

      .question-target {
        padding-bottom: 7px;
        margin-bottom: 7px;
      }

      .question-target-label {
        font-size: .58rem;
      }

      .question-target-name {
        padding: 3px 7px;
        font-size: .68rem;
      }

      .question-item .q {
        font-size: .9rem;
        line-height: 1.33;
      }

      .question-item .a,
      .question-item .author-meta,
      .sidebar-tip {
        font-size: .74rem;
      }
    }

    @media (min-width: 768px) and (max-width: 1024px) {
      .layout {
        grid-template-columns: minmax(178px, 22vw) minmax(0, 1fr) minmax(190px, 22vw) !important;
        gap: 10px !important;
      }

      .sidebar-card {
        width: min(100%, 256px);
        padding: 10px;
      }

      .sidebar-head {
        gap: 6px;
        margin-bottom: 6px;
        padding-bottom: 6px;
      }

      .sidebar-title {
        font-size: .9rem;
      }

      .sidebar-clear {
        padding: 4px 8px;
        font-size: .66rem;
      }

      .sidebar-section-label {
        margin: 8px 2px 5px;
        font-size: .62rem;
      }

      .sidebar-section-head {
        margin-top: 8px;
        margin-bottom: 5px;
      }

      .cat-list {
        gap: 5px;
      }

      .cat-item {
        padding: 7px 8px;
        border-radius: 9px;
      }

      .cat-left {
        gap: 7px;
      }

      .cat-icon {
        width: 22px;
        height: 22px;
        flex-basis: 22px;
        border-radius: 6px;
        font-size: .78rem;
      }

      .cat-name {
        font-size: .78rem;
      }

      .cat-go {
        width: 18px;
        height: 18px;
        flex-basis: 18px;
        font-size: .72rem;
      }

      .sidebar-tip {
        margin-top: 8px;
        font-size: .68rem;
        line-height: 1.3;
      }
    }

    @media (min-width: 981px) and (max-width: 1440px) {
      .layout {
        grid-template-columns: minmax(220px, 23vw) minmax(0, 1fr) minmax(220px, 23vw);
        gap: 12px;
      }

      .sidebar {
        top: calc(var(--header-h) + 72px);
        margin-top: 84px;
      }

      .sidebar-card {
        width: min(100%, 286px);
        padding: 12px;
      }

      .sidebar-head {
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 8px;
      }

      .sidebar-title {
        font-size: .97rem;
      }

      .sidebar-clear {
        padding: 5px 9px;
        font-size: .71rem;
      }

      .sidebar-section-label {
        margin: 10px 2px 6px;
        font-size: .67rem;
      }

      .sidebar-section-head {
        margin-top: 10px;
        margin-bottom: 6px;
      }

      .cat-list {
        gap: 6px;
      }

      .cat-item {
        padding: 8px 9px;
        border-radius: 10px;
      }

      .cat-left {
        gap: 8px;
      }

      .cat-icon {
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        border-radius: 7px;
        font-size: .82rem;
      }

      .cat-name {
        font-size: .82rem;
      }

      .cat-go {
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        font-size: .72rem;
      }

      .sidebar-tip {
        font-size: .72rem;
        line-height: 1.32;
      }
    }

    @media (min-width: 981px) and (max-height: 920px) {
      .sidebar {
        top: calc(var(--header-h) + 54px);
        margin-top: 66px;
      }

      .sidebar-card {
        max-height: calc(100vh - var(--header-h) - 76px);
        overflow: auto;
        width: min(100%, 274px);
        padding: 11px;
      }

      .sidebar-head {
        margin-bottom: 7px;
        padding-bottom: 7px;
      }

      .sidebar-title {
        font-size: .93rem;
      }

      .sidebar-clear {
        padding: 4px 8px;
        font-size: .68rem;
      }

      .sidebar-section-label {
        margin: 8px 2px 5px;
        font-size: .63rem;
      }

      .sidebar-section-head {
        margin-top: 8px;
        margin-bottom: 5px;
      }

      .cat-list {
        gap: 5px;
      }

      .cat-item {
        padding: 7px 8px;
      }

      .cat-icon {
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        font-size: .76rem;
      }

      .cat-name {
        font-size: .78rem;
      }

      .cat-go {
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        font-size: .68rem;
      }

      .sidebar-tip {
        margin-top: 7px;
        font-size: .68rem;
      }
    }

    @media (min-width: 981px) and (max-height: 780px) {
      .sidebar-card {
        max-height: calc(100vh - var(--header-h) - 58px);
        width: min(100%, 262px);
        padding: 10px;
      }

      .sidebar {
        top: calc(var(--header-h) + 42px);
        margin-top: 54px;
      }

      .cat-item {
        padding: 6px 7px;
      }

      .cat-name {
        font-size: .74rem;
      }

      .sidebar-tip {
        font-size: .65rem;
      }
    }

    @media (min-width: 768px) {
      :root {
        --side-card-pad-fluid: clamp(12px, 0.95vh + 6px, 17px);
        --side-head-gap-fluid: clamp(7px, 0.35vh + 4px, 10px);
        --side-title-fluid: clamp(.96rem, 0.22vh + .88rem, 1.08rem);
        --side-label-fluid: clamp(.7rem, 0.12vh + .66rem, .79rem);
        --side-item-gap-fluid: clamp(8px, 0.28vh + 6px, 10px);
        --side-item-pad-y-fluid: clamp(8px, 0.36vh + 5px, 11px);
        --side-item-pad-x-fluid: clamp(9px, 0.28vw + 7px, 12px);
        --side-icon-fluid: clamp(24px, 0.55vh + 20px, 28px);
        --side-icon-font-fluid: clamp(.84rem, 0.15vh + .8rem, 1rem);
        --side-name-fluid: clamp(.84rem, 0.12vh + .8rem, .92rem);
        --side-go-fluid: clamp(19px, 0.42vh + 16px, 24px);
        --side-tip-fluid: clamp(.72rem, 0.12vh + .68rem, .8rem);
        --side-card-max-fluid: clamp(430px, calc(100vh - var(--header-h) - 110px), 780px);
        --side-rail-max-fluid: clamp(360px, calc(100vh - var(--header-h) - 210px), 680px);
      }

      .sidebar-card,
      .rail-card {
        padding: var(--side-card-pad-fluid) !important;
      }

      .sidebar-card {
        max-height: var(--side-card-max-fluid);
        overflow: auto;
        scrollbar-gutter: stable;
      }

      .sidebar-head,
      .rail-head {
        gap: var(--side-head-gap-fluid);
        margin-bottom: var(--side-head-gap-fluid);
        padding-bottom: var(--side-head-gap-fluid);
      }

      .sidebar-title,
      .rail-card h3 {
        font-size: var(--side-title-fluid) !important;
      }

      .sidebar-section-label {
        font-size: var(--side-label-fluid) !important;
      }

      .sidebar-section-head {
        margin-top: clamp(10px, 0.6vh + 6px, 14px);
        margin-bottom: clamp(6px, 0.35vh + 4px, 8px);
      }

      .cat-list,
      .question-list {
        gap: var(--side-item-gap-fluid) !important;
      }

      .cat-item,
      .question-item {
        padding: var(--side-item-pad-y-fluid) var(--side-item-pad-x-fluid) !important;
      }

      .cat-left,
      .question-target {
        gap: var(--side-item-gap-fluid) !important;
      }

      .cat-icon,
      .question-target-avatar {
        width: var(--side-icon-fluid) !important;
        height: var(--side-icon-fluid) !important;
        flex: 0 0 var(--side-icon-fluid) !important;
        font-size: var(--side-icon-font-fluid) !important;
      }

      .cat-name {
        font-size: var(--side-name-fluid) !important;
      }

      .cat-go {
        width: var(--side-go-fluid) !important;
        height: var(--side-go-fluid) !important;
        flex: 0 0 var(--side-go-fluid) !important;
        font-size: clamp(.72rem, 0.1vh + .7rem, .8rem) !important;
      }

      .sidebar-tip {
        font-size: var(--side-tip-fluid) !important;
      }

      .rail-card {
        height: calc(100vh - var(--explore-right-top) - var(--explore-right-bottom-gap)) !important;
        height: calc(100dvh - var(--explore-right-top) - var(--explore-right-bottom-gap)) !important;
        max-height: calc(100vh - var(--explore-right-top) - var(--explore-right-bottom-gap)) !important;
        max-height: calc(100dvh - var(--explore-right-top) - var(--explore-right-bottom-gap)) !important;
      }

      .question-list {
        max-height: none !important;
      }

      .question-item .q {
        font-size: clamp(.92rem, 0.16vh + .86rem, .98rem) !important;
      }

      .question-item .a,
      .question-item .author-meta,
      .question-item .meta {
        font-size: clamp(.76rem, 0.12vh + .72rem, .82rem) !important;
      }
    }

    .posts-masonry>.post-card {
      display: block !important;
    }

    @media (min-width: 981px) {
      .layout {
        grid-template-columns: 196px minmax(0, var(--feed-max)) minmax(280px, 338px) !important;
        justify-content: center !important;
      }

      .sidebar {
        width: 100% !important;
        max-width: none !important;
        justify-self: stretch !important;
        justify-content: stretch !important;
      }

      .sidebar-card {
        width: 100% !important;
        max-width: 100% !important;
        max-height: none !important;
        overflow: visible !important;
        scrollbar-gutter: auto !important;
      }
    }

  </style>
</head>

<body data-post-share-title="Beitrag auf humplore" data-post-share-text="Schau dir diesen Beitrag an."
  data-post-share-confirmation="Link kopiert!">
  <!-- Header -->
  <header>
    <div class="header-inner">
    <a href="platform.php" class="brand" aria-label="Humplore â€“ Startseite">
      <img src="/pic/humplore-logo.png" alt="humplore Logo">
    </a>
      <section class="header-search" aria-label="Suche">
        <form method="GET" action="" class="search-row" role="search" aria-label="Suchformular">
          <div class="input-wrap">
            <input type="text" name="q" placeholder="Suche nach Profilen oder Beitragen..." value="<?= e($searchQuery) ?>"
              aria-label="Suchbegriff">
          </div>
          <input type="hidden" name="mode" value="<?= e($mode) ?>">
          <?php foreach ($activeTopicCategories as $value): ?>
            <input type="hidden" name="topic_cat" value="<?= e($value) ?>">
          <?php endforeach; ?>
          <?php foreach ($activeTopics as $value): ?>
            <input type="hidden" name="topic" value="<?= e($value) ?>">
          <?php endforeach; ?>
          <?php foreach ($activeCategories as $value): ?>
            <input type="hidden" name="cat" value="<?= e($value) ?>">
          <?php endforeach; ?>
          <?php foreach ($activeProfileCities as $value): ?>
            <input type="hidden" name="profile_city" value="<?= e($value) ?>">
          <?php endforeach; ?>
          <?php foreach ($activeProfileLanguages as $value): ?>
            <input type="hidden" name="profile_language" value="<?= e($value) ?>">
          <?php endforeach; ?>
          <?php if ($sort !== 'discover'): ?>
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
          <?php endif; ?>
          <button class="btn" type="submit" aria-label="Suchen">Suchen</button>
        </form>
      </section>
      <div class="header-spacer" aria-hidden="true"></div>
    </div>
  </header>

  <!-- Banner -->
  <div class="banner" role="img" aria-label="Explore-Banner">

  </div>

  <main class="layout">

    <!-- LEFT: Sidebar -->
    <aside class="sidebar" aria-label="Kategorien">
      <div class="sidebar-card">
        <div class="sidebar-section-label">Navigation</div>
        <div class="cat-list">
          <a class="cat-item is-active" href="platform.php">
            <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">Explore</span></span>
            <span class="cat-go" aria-hidden="true">&rarr;</span>
          </a>
          <?php if ($isCreator): ?>
            <a class="cat-item" href="posten.php">
              <span class="cat-left"><span class="cat-icon" aria-hidden="true">+</span><span class="cat-name">Posten</span></span>
              <span class="cat-go" aria-hidden="true">&rarr;</span>
            </a>
          <?php endif; ?>
          <a class="cat-item" href="news.php">
            <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">News</span></span>
            <span class="cat-go" aria-hidden="true">&rarr;</span>
          </a>
          <?php if ($isCreator): ?>
            <a class="cat-item" href="profile.php?user_id=<?= (int) $userId ?>">
              <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">Profil</span></span>
              <span class="cat-go" aria-hidden="true">&rarr;</span>
            </a>
          <?php endif; ?>
        </div>

        <?php
        $visibleCats = array_slice($sidebarCats, 0, 5);
        $hiddenCats = array_slice($sidebarCats, 5);
        $visibleTopicCategories = array_slice($topicCategoryOptions, 0, 6);
        $currentQ = txt_lower(trim((string) ($searchQuery ?? ($_GET['q'] ?? ''))));
        $categoryFilterPageState = $pageState;
        ?>

        <div class="sidebar-section-head">
          <div class="sidebar-section-label">Beitrags-/Lebenskategorien</div>
          <?php if (!empty($hiddenCats)): ?>
            <button type="button" class="sidebar-clear sidebar-clear--button" id="cat-more-toggle" aria-expanded="false" aria-controls="categoriesModal">
              mehr
            </button>
          <?php endif; ?>
        </div>

        <div class="cat-list">
          <?php foreach ($visibleCats as $item): ?>
            <?php require __DIR__ . '/app/views/partials/profile-sidebar-category-link.php'; ?>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($visibleTopicCategories)): ?>
          <div class="sidebar-section-head">
            <div class="sidebar-section-label">Themenkategorien</div>
            <?php if ($activeTopicCategories !== []): ?>
              <a class="sidebar-clear" href="<?= e(humplore_platform_url($pageState, ['topic_cat' => null, 'page' => null])) ?>">zuruecksetzen</a>
            <?php endif; ?>
          </div>
          <div class="cat-list">
            <?php foreach ($visibleTopicCategories as $topicItem): ?>
              <?php
              $topicName = (string) ($topicItem['name'] ?? '');
              if ($topicName === '') {
                  continue;
              }
              $nextTopicCategories = humplore_platform_values_toggle($activeTopicCategories, $topicName);
              $topicHref = humplore_platform_url($pageState, ['topic_cat' => $nextTopicCategories, 'topic' => null, 'page' => null]);
              $topicActive = in_array(txt_lower($topicName), array_map('txt_lower', $activeTopicCategories), true);
              ?>
              <a class="cat-item <?= $topicActive ? 'is-active' : '' ?>" href="<?= e($topicHref) ?>">
                <span class="cat-left">
                  <span class="cat-icon" aria-hidden="true"><?= e((string) ($topicItem['icon'] ?? 'T')) ?></span>
                  <span class="cat-name"><?= e($topicName) ?></span>
                </span>
                <span class="cat-go" aria-hidden="true"><?= (int) ($topicItem['post_count'] ?? 0) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <details class="profile-filter">
          <summary>Profilfilter</summary>
          <form class="profile-filter__body" method="GET" action="platform.php">
            <input type="hidden" name="mode" value="<?= e($mode) ?>">
            <?php if ($searchQuery !== '' || ($pageState['hasSearch'] ?? false)): ?>
              <input type="hidden" name="q" value="<?= e($searchQuery) ?>">
            <?php endif; ?>
            <?php foreach ($activeTopicCategories as $value): ?>
              <input type="hidden" name="topic_cat" value="<?= e($value) ?>">
            <?php endforeach; ?>
            <?php foreach ($activeTopics as $value): ?>
              <input type="hidden" name="topic" value="<?= e($value) ?>">
            <?php endforeach; ?>
            <?php foreach ($activeCategories as $value): ?>
              <input type="hidden" name="cat" value="<?= e($value) ?>">
            <?php endforeach; ?>
            <?php foreach ($activeProfileCities as $value): ?>
              <input type="hidden" name="profile_city" value="<?= e($value) ?>">
            <?php endforeach; ?>
            <?php if ($sort !== 'discover'): ?>
              <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <?php endif; ?>

            <label class="profile-filter__field">
              <span class="profile-filter__label">Wohnort</span>
              <input class="profile-filter__input" type="text" name="profile_city" list="profile-city-options" placeholder="Stadt oder Gemeinde">
            </label>
            <datalist id="profile-city-options">
              <?php foreach ($profileCityOptions as $cityOption): ?>
                <?php $cityName = humplore_platform_filter_value((string) ($cityOption['name'] ?? '')); ?>
                <?php if ($cityName !== ''): ?>
                  <option value="<?= e($cityName) ?>"></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </datalist>

            <div class="profile-filter__field">
              <div class="profile-filter__label">Sprache</div>
              <div class="profile-filter__checks">
                <?php foreach ($profileLanguageOptions as $languageOption): ?>
                  <?php
                  $languageName = (string) ($languageOption['name'] ?? '');
                  if ($languageName === '') {
                      continue;
                  }
                  $languageActive = in_array(txt_lower($languageName), array_map('txt_lower', $activeProfileLanguages), true);
                  ?>
                  <label class="profile-filter__check">
                    <span><?= e($languageName) ?></span>
                    <input type="checkbox" name="profile_language" value="<?= e($languageName) ?>" <?= $languageActive ? 'checked' : '' ?>>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="profile-filter__actions">
              <button class="profile-filter__submit" type="submit">Anwenden</button>
              <?php if ($activeProfileCities !== [] || $activeProfileLanguages !== []): ?>
                <a class="sidebar-clear" href="<?= e(humplore_platform_url($pageState, ['profile_city' => null, 'profile_language' => null, 'page' => null])) ?>">zuruecksetzen</a>
              <?php endif; ?>
            </div>
          </form>
        </details>

        <div class="sidebar-section-head">
          <div class="sidebar-section-label">Sortierung</div>
        </div>
        <div class="cat-list">
          <?php
          $sortOptions = [
            'discover' => 'Entdecken',
            'latest' => 'Neueste',
            'popular' => 'Beliebt',
          ];
          ?>
          <?php foreach ($sortOptions as $sortKey => $sortLabel): ?>
            <a class="cat-item <?= $sort === $sortKey ? 'is-active' : '' ?>" href="<?= e(humplore_platform_url($pageState, ['sort' => $sortKey === 'discover' ? null : $sortKey, 'page' => null])) ?>">
              <span class="cat-left">
                <span class="cat-icon" aria-hidden="true">&bull;</span>
                <span class="cat-name"><?= e($sortLabel) ?></span>
              </span>
              <span class="cat-go" aria-hidden="true">&rarr;</span>
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </aside>


    <div class="main-col">
      <!-- Suche -->

      <?php if ($hasActiveFilterChips): ?>
        <section class="search-card compact" aria-label="Aktive Filter" style="margin-bottom:12px;">
          <div class="toolbar" aria-live="polite" style="margin-top:0;">
            <div class="badges">
              <?php foreach ($activeTopicCategories as $value): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['topic_cat' => humplore_platform_values_without($activeTopicCategories, $value), 'page' => null])) ?>">
                  Themenkategorie: <?= e($value) ?> <span class="count">x</span>
                </a>
              <?php endforeach; ?>
              <?php foreach ($activeTopics as $value): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['topic' => humplore_platform_values_without($activeTopics, $value), 'page' => null])) ?>">
                  Thema: <?= e($value) ?> <span class="count">x</span>
                </a>
              <?php endforeach; ?>
              <?php foreach ($activeCategories as $value): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['cat' => humplore_platform_values_without($activeCategories, $value), 'page' => null])) ?>">
                  Kategorie: <?= e($value) ?> <span class="count">x</span>
                </a>
              <?php endforeach; ?>
              <?php foreach ($activeProfileCities as $value): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['profile_city' => humplore_platform_values_without($activeProfileCities, $value), 'page' => null])) ?>">
                  Profilfilter Wohnort: <?= e($value) ?> <span class="count">x</span>
                </a>
              <?php endforeach; ?>
              <?php foreach ($activeProfileLanguages as $value): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['profile_language' => humplore_platform_values_without($activeProfileLanguages, $value), 'page' => null])) ?>">
                  Profilfilter Sprache: <?= e($value) ?> <span class="count">x</span>
                </a>
              <?php endforeach; ?>
              <?php if ($sort !== 'discover'): ?>
                <a class="badge" href="<?= e(humplore_platform_url($pageState, ['sort' => null, 'page' => null])) ?>">
                  Sortierung: <?= e($sort === 'latest' ? 'Neueste' : 'Beliebt') ?> <span class="count">x</span>
                </a>
              <?php endif; ?>
            </div>
            <a class="sidebar-clear" href="<?= e(humplore_platform_url($pageState, ['topic_cat' => null, 'topic' => null, 'cat' => null, 'profile_city' => null, 'profile_language' => null, 'sort' => null, 'page' => null])) ?>">Alle Filter entfernen</a>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($searchQuery !== ''): ?>
      <section class="search-card compact" aria-label="Suche">
        <div class="toolbar" aria-live="polite" style="margin-top:10px;">
          <span class="badge">Profile <span class="count"><?= (int) $countProfiles ?></span></span>
          <span class="badge">BeitrÃ¤ge <span class="count"><?= (int) $countPosts ?></span></span>
          <div class="result-count"><?= (int) $totalFound ?> Ergebnis<?= $totalFound === 1 ? '' : 'se' ?></div>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($showBrowseOverview && (!empty($browseOverview['topics']) || !empty($browseOverview['categories']))): ?>
        <section class="browse-overview" aria-labelledby="browse-overview-title">
          <div class="browse-overview__head">
            <div>
              <h2 class="browse-overview__title" id="browse-overview-title">Stoebern nach Themenkategorien und Beitrags-/Lebenskategorien</h2>
              <p class="browse-overview__sub">Neue Beitraege und passende Creator direkt aus Explore.</p>
            </div>
          </div>

          <?php if (!empty($browseOverview['topics'])): ?>
            <section class="browse-section" aria-labelledby="browse-topics-title">
              <h3 class="browse-section__title" id="browse-topics-title">Themenkategorien</h3>
              <div class="browse-grid">
                <?php foreach ($browseOverview['topics'] as $topicGroup): ?>
                  <?php
                  $topicName = (string) ($topicGroup['name'] ?? '');
                  if ($topicName === '') {
                      continue;
                  }
                  $topicHref = humplore_platform_url($pageState, ['topic_cat' => $topicName, 'topic' => null, 'cat' => null, 'page' => null]);
                  ?>
                  <article class="browse-card">
                    <div class="browse-card__head">
                      <div>
                        <h4 class="browse-card__title"><?= e($topicName) ?></h4>
                        <div class="browse-card__meta">
                          <?= (int) ($topicGroup['post_count'] ?? 0) ?> Beitraege
                          <?php if ((int) ($topicGroup['creator_count'] ?? 0) > 0): ?>
                            &middot; <?= (int) $topicGroup['creator_count'] ?> Creator
                          <?php endif; ?>
                        </div>
                      </div>
                      <a class="browse-card__more" href="<?= e($topicHref) ?>">Mehr anzeigen</a>
                    </div>

                    <?php if (!empty($topicGroup['topics'])): ?>
                      <div class="browse-card__block">
                        <p class="browse-card__label">Themen</p>
                        <div class="badges">
                          <?php foreach ($topicGroup['topics'] as $topicPreview): ?>
                            <span class="badge"><?= e((string) ($topicPreview['name'] ?? '')) ?></span>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="browse-card__block">
                      <p class="browse-card__label">Neueste Beitraege</p>
                      <?php if (empty($topicGroup['posts'])): ?>
                        <div class="browse-empty">Noch keine Beitraege in dieser Themenkategorie.</div>
                      <?php else: ?>
                        <div class="browse-posts">
                          <?php foreach ($topicGroup['posts'] as $postPreview): ?>
                            <?php
                            $postTitle = trim((string) ($postPreview['title'] ?? ''));
                            $postExcerpt = trim((string) ($postPreview['content'] ?? ''));
                            if (txt_len($postExcerpt) > 110) {
                                $postExcerpt = rtrim(txt_sub($postExcerpt, 0, 110)) . '...';
                            }
                            ?>
                            <div class="browse-post">
                              <div class="browse-post__title"><?= e($postTitle !== '' ? $postTitle : 'Beitrag ohne Titel') ?></div>
                              <?php if ($postExcerpt !== ''): ?>
                                <div class="browse-post__excerpt"><?= e($postExcerpt) ?></div>
                              <?php endif; ?>
                              <div class="browse-post__meta">@<?= e((string) ($postPreview['username'] ?? '')) ?></div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="browse-card__block">
                      <p class="browse-card__label">Passende Creator</p>
                      <?php if (empty($topicGroup['creators'])): ?>
                        <div class="browse-empty">Noch keine Creator-Vorschau.</div>
                      <?php else: ?>
                        <div class="browse-creators">
                          <?php foreach ($topicGroup['creators'] as $creatorPreview): ?>
                            <a class="browse-creator" href="profile.php?user_id=<?= (int) ($creatorPreview['id'] ?? 0) ?>">
                              <img class="browse-creator__avatar" src="<?= e(profile_img_src((int) ($creatorPreview['id'] ?? 0))) ?>" alt="Profilbild von @<?= e((string) ($creatorPreview['username'] ?? '')) ?>">
                              <span>@<?= e((string) ($creatorPreview['username'] ?? '')) ?></span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if (!empty($browseOverview['categories'])): ?>
            <section class="browse-section" aria-labelledby="browse-categories-title">
              <h3 class="browse-section__title" id="browse-categories-title">Beitrags-/Lebenskategorien</h3>
              <div class="browse-grid">
                <?php foreach ($browseOverview['categories'] as $categoryGroup): ?>
                  <?php
                  $categoryName = (string) ($categoryGroup['name'] ?? '');
                  if ($categoryName === '') {
                      continue;
                  }
                  $categoryHref = humplore_platform_url($pageState, ['cat' => $categoryName, 'topic' => null, 'page' => null]);
                  ?>
                  <article class="browse-card">
                    <div class="browse-card__head">
                      <div>
                        <h4 class="browse-card__title"><?= e($categoryName) ?></h4>
                        <div class="browse-card__meta"><?= (int) ($categoryGroup['post_count'] ?? 0) ?> Beitraege</div>
                      </div>
                      <a class="browse-card__more" href="<?= e($categoryHref) ?>">Mehr anzeigen</a>
                    </div>

                    <div class="browse-card__block">
                      <p class="browse-card__label">Neueste Beitraege</p>
                      <?php if (empty($categoryGroup['posts'])): ?>
                        <div class="browse-empty">Noch keine Beitraege in dieser Kategorie.</div>
                      <?php else: ?>
                        <div class="browse-posts">
                          <?php foreach ($categoryGroup['posts'] as $postPreview): ?>
                            <?php
                            $postTitle = trim((string) ($postPreview['title'] ?? ''));
                            $postExcerpt = trim((string) ($postPreview['content'] ?? ''));
                            if (txt_len($postExcerpt) > 110) {
                                $postExcerpt = rtrim(txt_sub($postExcerpt, 0, 110)) . '...';
                            }
                            ?>
                            <div class="browse-post">
                              <div class="browse-post__title"><?= e($postTitle !== '' ? $postTitle : 'Beitrag ohne Titel') ?></div>
                              <?php if ($postExcerpt !== ''): ?>
                                <div class="browse-post__excerpt"><?= e($postExcerpt) ?></div>
                              <?php endif; ?>
                              <div class="browse-post__meta">@<?= e((string) ($postPreview['username'] ?? '')) ?></div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        </section>
      <?php endif; ?>

      <?php if ($searchQuery !== ''): ?>
        <div class="search-results-shell" aria-label="Suchergebnisse">
        <!-- Profile-Ergebnisse -->
        <section id="profiles" class="results" aria-label="Gefundene Profile">
          <?php if ($countProfiles === 0): ?>
            <div class="post-inner" style="border-style:dashed">Keine passenden Profile gefunden.</div>
          <?php else:
            foreach ($resultsProfiles as $p): ?>
              <a class="post-card" href="profile.php?user_id=<?= (int) $p['id'] ?>">
                <div class="post-inner" style="display:flex;align-items:center;gap:14px">
                  <div class="post-avatar" aria-hidden="true" style="width:56px;height:56px;flex:0 0 56px">
                    <?php if (!empty($p['profile_image']) && $p['profile_image'] !== 'default_profile.png'): ?>
                      <img src="data:image/jpeg;base64,<?= base64_encode($p['profile_image']) ?>" loading="lazy" decoding="async"
                        alt="Profilbild von @<?= e($p['username']) ?>">
                    <?php else: ?>         <?= strtoupper(substr($p['username'], 0, 1)) ?>       <?php endif; ?>
                  </div>
                  <div style="flex:1;min-width:0">
                    <div style="font-weight:800;color:#283221">@<?= e($p['username']) ?></div>
                    <div style="color:#6b7466"><?= e((string) $p['main_topic']) ?></div>
                    <div style="color:#7e8779;font-size:.93rem"><?= (int) $p['follower_count'] ?> Follower</div>
                  </div>
                </div>
              </a>
            <?php endforeach; endif; ?>
        </section>

        <!-- Beitrags-Ergebnisse -->
        <section id="posts" class="results" aria-label="BeitrÃ¤ge aus der Suche" style="margin-top:14px">

          <?php if ($countPosts === 0): ?>
            <div class="post-inner" style="border-style:dashed">Keine BeitrÃ¤ge gefunden.</div>
          <?php else: ?>
            <?php
            $resultPostIds = array_map(static function ($p) {
              return (int) ($p['id'] ?? 0);
            }, $resultsPosts);
            [$resultLikeCounts, $resultLikedMap] = getBulkLikeInfo($pdo, $resultPostIds, $userId);
            $resultCommentsMap = getBulkComments($pdo, $resultPostIds);
            $resultCommentIds = [];
            foreach ($resultCommentsMap as $commentsForPost) {
              foreach ($commentsForPost as $commentRow) {
                $commentId = (int) ($commentRow['id'] ?? 0);
                if ($commentId > 0) {
                  $resultCommentIds[] = $commentId;
                }
              }
            }
            $resultReportedComments = humplore_bulk_reported_targets($pdo, $userId, 'comment', $resultCommentIds);
            $resultCommentsMap = humplore_apply_comment_report_state_map($resultCommentsMap, $resultReportedComments);
            $resultSavedMap = getBulkSavedPostInfo($pdo, $resultPostIds, $userId);
            ?>
            <div class="posts-masonry">
              <?php foreach ($resultsPosts as $post): ?>
                <?php
                $postId = (int) $post['id'];
                $likeCount = (int) ($resultLikeCounts[$postId] ?? 0);
                $hasLiked = !empty($resultLikedMap[$postId]);
                $hasSaved = !empty($resultSavedMap[$postId]);
                $comments = $resultCommentsMap[$postId] ?? [];
                $commentCount = count($comments);

                $unlocked = hasAccess($post, $userId);
                $cardClass = $unlocked ? '' : ' locked';
                $priceLabel = isset($post['price_cents']) ? formatEuroCents($post['price_cents']) : '';


$raw = (string)($post['content'] ?? '');
$paras = parse_paragraphs($raw);
$pid = (int)$post['id'];
$headerTag = 'header';
$imageMode = 'lenient';
$wrapRawContent = false;
$commentEmptyText = 'Noch keine Kommentare â€“ starte das GesprÃ¤ch âœ¨';
$viewerInitial = strtoupper(substr((string) $_SESSION['user_id'], 0, 1));
require __DIR__ . '/app/views/partials/platform-post-card.php';
                ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
        </div>
      <?php endif; ?>

      <!-- Explore Feed -->
      <?php if ($searchQuery !== ''): ?>
      <div class="results-divider" aria-hidden="true">
        <span class="results-divider-title">Weitere BeitrÃ¤ge aus Explore</span>
        <span class="results-divider-sub">Ab hier beginnt wieder der normale Feed auÃŸerhalb deiner Suchtreffer.</span>
      </div>
      <?php endif; ?>
      <section class="results" style="margin-top:18px" aria-label="Explore Feed">

        <?php if (empty($explorePosts)): ?>
          <div class="post-inner" style="border-style:dashed">
            <?= $mode === 'following' ? 'Dein Feed ist noch leer. Folge ein paar Creator*innen â€“ oder wechsle zu â€žEntdeckenâ€œ.'
              : 'Noch keine BeitrÃ¤ge vorhanden.' ?>
          </div>
        <?php else: ?>
          <?php
          $explorePostIds = array_map(static function ($p) {
            return (int) ($p['id'] ?? 0);
          }, $explorePosts);
          [$exploreLikeCounts, $exploreLikedMap] = getBulkLikeInfo($pdo, $explorePostIds, $userId);
          $exploreCommentsMap = getBulkComments($pdo, $explorePostIds);
          $exploreCommentIds = [];
          foreach ($exploreCommentsMap as $commentsForPost) {
            foreach ($commentsForPost as $commentRow) {
              $commentId = (int) ($commentRow['id'] ?? 0);
              if ($commentId > 0) {
                $exploreCommentIds[] = $commentId;
              }
            }
          }
          $exploreReportedComments = humplore_bulk_reported_targets($pdo, $userId, 'comment', $exploreCommentIds);
          $exploreCommentsMap = humplore_apply_comment_report_state_map($exploreCommentsMap, $exploreReportedComments);
          $exploreSavedMap = getBulkSavedPostInfo($pdo, $explorePostIds, $userId);
          ?>
          <div class="posts-masonry" id="explore-feed" data-page="<?= (int) $page ?>" data-total-pages="<?= (int) $totalPages ?>">
            <?php foreach ($explorePosts as $post): ?>
              <?php
              $postId = (int) $post['id'];
              $likeCount = (int) ($exploreLikeCounts[$postId] ?? 0);
              $hasLiked = !empty($exploreLikedMap[$postId]);
              $hasSaved = !empty($exploreSavedMap[$postId]);
              $comments = $exploreCommentsMap[$postId] ?? [];
              $commentCount = count($comments);

              $unlocked = hasAccess($post, $userId);
              $cardClass = $unlocked ? '' : ' locked';
              $priceLabel = isset($post['price_cents']) ? formatEuroCents($post['price_cents']) : '';

$raw = (string)($post['content'] ?? '');
$paras = parse_paragraphs($raw);
$pid = (int)$post['id'];
$headerTag = 'div';
$imageMode = 'image_only';
$wrapRawContent = true;
$commentEmptyText = 'Noch keine Kommentare â€“ sag als Erste*r hallo ';
$viewerInitial = strtoupper(substr((string) $userId, 0, 1));
require __DIR__ . '/app/views/partials/platform-post-card.php';
              ?>
            <?php endforeach; ?>
          </div>

          <div id="feed-loader" class="hint" style="text-align:center;margin-top:14px" hidden>Lade weitere BeitrÃ¤ge â€¦</div>
          <div id="feed-end" class="hint" style="text-align:center;margin-top:14px" <?= $page < $totalPages ? 'hidden' : '' ?>>
            Du hast alle BeitrÃ¤ge gesehen.
          </div>
          <div id="feed-sentinel" aria-hidden="true" style="height:1px"></div>
        <?php endif; ?>
      </section>
    </div>

    <aside class="right-spacer" aria-label="ZufÃ¤llige Fragen">
      <div class="rail-card">
        <div class="rail-head">
          <h3>Gestellte Fragen</h3>
          <button type="button" class="rail-more" id="questionsModalOpen">mehr</button>
        </div>
        <?php if (empty($allQuestions)): ?>
          <div class="hint" style="font-size:.88rem">Noch keine Fragen vorhanden.</div>
        <?php else: ?>
          <div class="question-list">
            <?php foreach ($allQuestions as $rq): ?>
              <button type="button" class="question-item question-item--preview" data-question-id="<?= (int) $rq['id'] ?>">
                <div class="question-target">
                  <img class="question-target-avatar" src="<?= e(profile_img_src((int) $rq['creator_id'])) ?>" alt="Profilbild von @<?= e((string) $rq['creator_name']) ?>">
                  <div class="question-target-copy">
                    <div class="question-target-label">Frage an</div>
                    <div class="question-target-badges">
                      <div class="question-target-name">@<?= e((string) $rq['creator_name']) ?></div>
                      <?php if (!empty($rq['creator_main_topic'])): ?>
                        <span class="topic-pill"><?= e((string) $rq['creator_main_topic']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="q"><?= e((string) $rq['question_text']) ?></div>
                  <?php if (empty($rq['is_anonymous']) && empty($rq['answer_text']) && !empty($rq['author_name'])): ?> â€¢ von @<?= e((string) $rq['author_name']) ?><?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </aside>
  </main>

  <!-- Post Modal -->
  <div class="post-modal" id="postModal" aria-hidden="true" role="dialog" aria-label="Beitrag ansehen">
    <div class="post-modal__panel" role="document">
      <div class="post-modal__close">
        <button type="button" id="postModalClose" aria-label="SchlieÃŸen">SchlieÃŸen âœ•</button>
      </div>
      <div class="post-modal__content" id="postModalContent"></div>
    </div>
  </div>

  <div class="post-modal" id="questionsModal" aria-hidden="true" role="dialog" aria-label="Alle gestellten Fragen">
    <div class="post-modal__panel question-modal__panel" role="document">
      <div class="post-modal__close">
        <button type="button" id="questionsModalClose" aria-label="SchlieÃƒÅ¸en">SchlieÃŸen</button>
      </div>
      <div class="post-modal__content question-modal__content">
        <div class="question-modal__header">
          <div>
            <div class="question-modal__title">Alle gestellten Fragen</div>
            <div class="question-modal__sub"><?= count($allQuestions) ?> Fragen im Explore-Feed</div>
          </div>
        </div>
        <?php if (empty($allQuestions)): ?>
          <div class="hint">Noch keine Fragen vorhanden.</div>
        <?php else: ?>
          <div class="question-feed">
            <?php foreach ($allQuestions as $rq): ?>
              <a class="question-item" id="question-modal-<?= (int) $rq['id'] ?>" data-question-id="<?= (int) $rq['id'] ?>" href="profile.php?user_id=<?= (int) $rq['creator_id'] ?>">
                <div class="question-target">
                  <img class="question-target-avatar" src="<?= e(profile_img_src((int) $rq['creator_id'])) ?>" alt="Profilbild von @<?= e((string) $rq['creator_name']) ?>">
                  <div class="question-target-copy">
                    <div class="question-target-label">Frage an</div>
                    <div class="question-target-badges">
                      <div class="question-target-name">@<?= e((string) $rq['creator_name']) ?></div>
                      <?php if (!empty($rq['creator_main_topic'])): ?>
                        <span class="topic-pill"><?= e((string) $rq['creator_main_topic']) ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="q"><?= e((string) $rq['question_text']) ?></div>
                <?php if (!empty($rq['answer_text'])): ?>
                  <div class="a"><?= e((string) $rq['answer_text']) ?></div>
                <?php endif; ?>
                <?php if (empty($rq['is_anonymous']) && empty($rq['answer_text']) && !empty($rq['author_name'])): ?>
                  <span class="author-meta">von @<?= e((string) $rq['author_name']) ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="post-modal" id="categoriesModal" aria-hidden="true" role="dialog" aria-label="Alle Beitrags-/Lebenskategorien">
    <div class="post-modal__panel category-modal__panel" role="document">
      <div class="post-modal__close">
        <button type="button" id="categoriesModalClose" aria-label="SchlieÃŸen">SchlieÃŸen</button>
      </div>
      <div class="post-modal__content category-modal__content">
        <div class="category-modal__header">
          <div class="category-modal__title">Alle Beitrags-/Lebenskategorien</div>
          <div class="category-modal__sub"><?= count($sidebarCats) ?> Kategorien in Explore</div>
        </div>
        <div class="category-modal-list">
          <?php foreach ($sidebarCats as $item): ?>
            <?php require __DIR__ . '/app/views/partials/profile-sidebar-category-link.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    const catMoreToggle = document.getElementById('cat-more-toggle');
    const categoriesModal = document.getElementById('categoriesModal');
    const categoriesModalClose = document.getElementById('categoriesModalClose');

    function openCategoriesModal() {
      if (!categoriesModal) return;
      categoriesModal.classList.add('open');
      categoriesModal.setAttribute('aria-hidden', 'false');
      if (catMoreToggle) catMoreToggle.setAttribute('aria-expanded', 'true');
      document.body.classList.add('modal-open');
    }

    function closeCategoriesModal() {
      if (!categoriesModal) return;
      categoriesModal.classList.remove('open');
      categoriesModal.setAttribute('aria-hidden', 'true');
      if (catMoreToggle) catMoreToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('modal-open');
    }

    if (catMoreToggle) {
      catMoreToggle.addEventListener('click', openCategoriesModal);
    }
    if (categoriesModalClose) {
      categoriesModalClose.addEventListener('click', closeCategoriesModal);
    }
    if (categoriesModal) {
      categoriesModal.addEventListener('click', (e) => {
        if (e.target === categoriesModal) closeCategoriesModal();
      });
    }

   function toggleMore(id, ev) {
  if (ev) {
    ev.preventDefault();
    ev.stopPropagation();
  }

  const postCard = document.getElementById('post-' + id);
  if (!postCard) return;

  openPostModalFromCard(postCard);
}

    // Infinite Scroll: nÃ¤chste Seite laden und Posts anhÃ¤ngen
    const feedEl = document.getElementById('explore-feed');
    const feedSentinel = document.getElementById('feed-sentinel');
    const feedLoader = document.getElementById('feed-loader');
    const feedEnd = document.getElementById('feed-end');
    const feedHint = document.getElementById('feed-hint');

    if (feedEl && feedSentinel) {
      let page = Number(feedEl.dataset.page || 1);
      const totalPages = Number(feedEl.dataset.totalPages || 1);
      let loading = false;
      let done = page >= totalPages;

      const updateHint = () => {
        if (feedHint) {
          feedHint.textContent = `${feedEl.querySelectorAll('.post-card').length} angezeigt â€¢ ZufÃ¤llige Reihenfolge`;
        }
      };

      const loadNextPage = async () => {
        if (loading || done) return;
        loading = true;
        if (feedLoader) feedLoader.hidden = false;

        try {
          const params = new URLSearchParams(window.location.search);
          params.set('page', String(page + 1));
          const resp = await fetch(`${location.pathname}?${params.toString()}`, { credentials: 'same-origin' });
          if (!resp.ok) throw new Error('Feed-Laden fehlgeschlagen');

          const html = await resp.text();
          const doc = new DOMParser().parseFromString(html, 'text/html');
          const nextFeed = doc.querySelector('#explore-feed');
          if (!nextFeed) throw new Error('Kein Feed in Antwort gefunden');

          const cards = nextFeed.querySelectorAll('.post-card');
          cards.forEach((card) => feedEl.appendChild(card));

          page += 1;
          feedEl.dataset.page = String(page);
          done = page >= totalPages || cards.length === 0;
          if (done) {
            observer.disconnect();
            if (feedEnd) feedEnd.hidden = false;
          }
          updateHint();
        } catch (err) {
          console.error(err);
        } finally {
          loading = false;
          if (feedLoader) feedLoader.hidden = true;
        }
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) loadNextPage();
        });
      }, { rootMargin: '500px 0px' });

      if (done) {
        if (feedEnd) feedEnd.hidden = false;
      } else {
        observer.observe(feedSentinel);
      }
    }

    // Scroll zu geteiltem Post (falls ?post_id=â€¦)
    document.addEventListener('DOMContentLoaded', () => {
      const pid = new URLSearchParams(location.search).get('post_id');
      if (pid) {
        const el = document.getElementById(`post-${pid}`);
        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.style.outline = '3px solid #4b573e33'; setTimeout(() => el.style.outline = 'none', 1800); }
      }
    });


    function getCsrf() {
      const m = document.querySelector('meta[name="csrf-token"]');
      return m ? m.getAttribute('content') : '';
    }

    const reportBusy = new Set();

    function reportControlsFor(targetType, targetId) {
      return Array.from(document.querySelectorAll('[data-report-control]')).filter((control) => (
        control.getAttribute('data-report-target-type') === targetType
        && control.getAttribute('data-report-target-id') === String(targetId)
      ));
    }

    function setReportControlsReported(targetType, targetId) {
      reportControlsFor(targetType, targetId).forEach((control) => {
        control.classList.add('is-reported');
        const toggle = control.querySelector('[data-report-toggle]');
        const form = control.querySelector('[data-report-form]');
        const message = control.querySelector('[data-report-message]');
        if (toggle) {
          toggle.textContent = 'Gemeldet';
          toggle.disabled = true;
          toggle.setAttribute('aria-disabled', 'true');
        }
        if (form) form.hidden = true;
        if (message) {
          message.textContent = 'Danke, wir haben deine Meldung erfasst.';
          message.hidden = false;
          message.classList.remove('is-error');
        }
      });
    }

    document.addEventListener('click', (event) => {
      const toggle = event.target.closest('[data-report-toggle]');
      if (toggle) {
        const control = toggle.closest('[data-report-control]');
        const form = control ? control.querySelector('[data-report-form]') : null;
        if (form && !toggle.disabled) form.hidden = !form.hidden;
        return;
      }

      const cancel = event.target.closest('[data-report-cancel]');
      if (cancel) {
        const control = cancel.closest('[data-report-control]');
        const form = control ? control.querySelector('[data-report-form]') : null;
        const message = control ? control.querySelector('[data-report-message]') : null;
        if (form) form.hidden = true;
        if (message && message.classList.contains('is-error')) message.hidden = true;
      }
    });

    document.addEventListener('submit', (event) => {
      const form = event.target.closest('[data-report-form]');
      if (!form) return;

      event.preventDefault();
      const control = form.closest('[data-report-control]');
      if (!control) return;

      const targetType = control.getAttribute('data-report-target-type') || '';
      const targetId = control.getAttribute('data-report-target-id') || '';
      const busyKey = `${targetType}:${targetId}`;
      if (!targetType || !targetId || reportBusy.has(busyKey)) return;

      reportBusy.add(busyKey);
      const submit = form.querySelector('.report-submit');
      const message = control.querySelector('[data-report-message]');
      if (submit) submit.disabled = true;
      if (message) {
        message.hidden = true;
        message.classList.remove('is-error');
      }

      const fd = new FormData(form);
      fd.append('csrf_token', getCsrf());

      fetch('report_handler.php', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'X-CSRF-Token': getCsrf() }
      })
        .then(async (response) => {
          let data = null;
          try { data = await response.json(); } catch (_) {}
          if (!response.ok || !data || data.reported !== true || !data.target_type || !data.target_id) {
            throw new Error((data && data.error) || 'Report failed');
          }
          setReportControlsReported(String(data.target_type), String(data.target_id));
        })
        .catch(() => {
          if (message) {
            message.textContent = 'Meldung konnte nicht gesendet werden.';
            message.classList.add('is-error');
            message.hidden = false;
          }
        })
        .finally(() => {
          reportBusy.delete(busyKey);
          if (submit) submit.disabled = false;
        });
    });

    const postModal = document.getElementById('postModal');
    const postModalContent = document.getElementById('postModalContent');
    const postModalClose = document.getElementById('postModalClose');
    const questionsModal = document.getElementById('questionsModal');
    const questionsModalOpen = document.getElementById('questionsModalOpen');
    const questionsModalClose = document.getElementById('questionsModalClose');

    function openPostModalFromCard(cardEl) {
  if (!cardEl) return;

  // Clone der Card (damit dein Feed unberÃ¼hrt bleibt)
  const clone = cardEl.cloneNode(true);

  // Im Modal soll der Post komplett offen sein
  const inner = clone.querySelector('.post-inner');
  if (inner) inner.classList.add('is-expanded');

  // WICHTIG: versteckten Rest-Text sichtbar machen
  clone.querySelectorAll('.more-content').forEach(el => {
    el.style.display = 'block';
  });

  // Readmore/Readless im Modal ausblenden (wir zeigen ja alles)
  clone.querySelectorAll('.post-readmore, .post-readless').forEach(el => {
    el.style.display = 'none';
  });

  // "mehr lesen" Links im Modal deaktivieren
  clone.querySelectorAll('.more-link').forEach(a => {
    a.removeAttribute('onclick');
    a.addEventListener('click', (e) => e.preventDefault());
  });

  // Kommentare im Modal optional offen anzeigen (wie bei dir)
  clone.querySelectorAll('.comments-section').forEach(sec => {
    sec.classList.add('open');
    sec.style.maxHeight = '1000px';
  });

  // In Modal einsetzen
  postModalContent.innerHTML = '';
  postModalContent.appendChild(clone);

  // Ã¶ffnen
  postModal.classList.add('open');
  postModal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('modal-open');
}


    function closePostModal() {
      postModal.classList.remove('open');
      postModal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
      postModalContent.innerHTML = '';
    }

    function flashQuestionInModal(questionId) {
      if (!questionsModal || !questionId) return;
      const target = document.getElementById(`question-modal-${questionId}`);
      if (!target) return;

      target.classList.remove('is-flash');
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      requestAnimationFrame(() => {
        target.classList.add('is-flash');
        window.setTimeout(() => target.classList.remove('is-flash'), 1600);
      });
    }

    function openQuestionsModal(questionId = null) {
      if (!questionsModal) return;
      questionsModal.classList.add('open');
      questionsModal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      if (questionId) {
        window.setTimeout(() => flashQuestionInModal(questionId), 60);
      }
    }

    function closeQuestionsModal() {
      if (!questionsModal) return;
      questionsModal.classList.remove('open');
      questionsModal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open');
    }

    // Close handlers
    postModalClose.addEventListener('click', closePostModal);
    postModal.addEventListener('click', (e) => {
      // Klick auf den dunklen Hintergrund schlieÃŸt
      if (e.target === postModal) closePostModal();
    });
    if (questionsModalOpen) questionsModalOpen.addEventListener('click', openQuestionsModal);
    if (questionsModalClose) questionsModalClose.addEventListener('click', closeQuestionsModal);
    if (questionsModal) {
      questionsModal.addEventListener('click', (e) => {
        if (e.target === questionsModal) closeQuestionsModal();
      });
    }
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && categoriesModal && categoriesModal.classList.contains('open')) closeCategoriesModal();
      if (e.key === 'Escape' && postModal.classList.contains('open')) closePostModal();
      if (e.key === 'Escape' && questionsModal && questionsModal.classList.contains('open')) closeQuestionsModal();
    });
    document.querySelectorAll('.question-item--preview').forEach((button) => {
      button.addEventListener('click', () => {
        const questionId = button.getAttribute('data-question-id');
        openQuestionsModal(questionId);
      });
    });

    // Klick auf Post-Card (aber NICHT wenn man auf Buttons/Links klickt)
    document.addEventListener('click', (e) => {
      const card = e.target.closest('.post-card');
      if (!card) return;

      // nicht Ã¶ffnen, wenn Klick auf interaktive Elemente
      if (e.target.closest('button, a, textarea, input, form')) return;

      e.preventDefault();
      openPostModalFromCard(card);
    });

  </script>
  <script src="js/post-actions.js?v=20260811"></script>
</body>

</html>
