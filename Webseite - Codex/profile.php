<?php
require_once __DIR__ . '/app/bootstrap.php';

humplore_require_login($_SERVER['REQUEST_URI'] ?? 'profile.php');
$viewerUserId = humplore_current_user_id();
$pdo = humplore_db();

/* ===========================
   Security: CSRF Token
   =========================== */
$csrf_token = humplore_ensure_csrf_token();
$headerSearchQuery = trim((string) ($_GET['q'] ?? ''));

/* ===========================
   Eingangsparameter
   =========================== */
$profile_user_id = humplore_profile_resolve_user_id($_GET, $viewerUserId);
$activeCatSlug = humplore_profile_active_category_slug($_GET);

try {
  $profileContext = humplore_profile_load_context($pdo, $profile_user_id, $viewerUserId);
} catch (RuntimeException $e) {
  die($e->getMessage());
}

$is_own_profile = $profileContext['isOwnProfile'];
$user = $profileContext['user'];
$isCreator = $profileContext['isCreator'];
$viewerIsCreator = $profileContext['viewerIsCreator'];
$recommendedCreator = $profileContext['recommendedCreator'];
$isFollowing = $profileContext['isFollowing'];
$postsCount = $profileContext['postsCount'];
$followerCount = $profileContext['followerCount'];
$followingCount = $profileContext['followingCount'];
$data = $profileContext['data'];
$allCategories = $profileContext['allCategories'];


/* ===========================
   Flash-Messages fÃƒÆ’Ã‚Â¼r Q&A
   =========================== */
$profileActionState = humplore_profile_handle_actions(
  $pdo,
  [
    'viewerUserId' => $viewerUserId,
    'profileUserId' => $profile_user_id,
    'isCreator' => $isCreator,
    'isOwnProfile' => $is_own_profile,
  ],
  $_POST,
  $_FILES,
  $_SERVER
);
$ask_error = $profileActionState['ask_error'];
$ask_success = $profileActionState['ask_success'];
$answer_error = $profileActionState['answer_error'];
$answer_success = $profileActionState['answer_success'];

/* ===========================
   Basis-URLs
   =========================== */
$profileLink = humplore_profile_share_link($_SERVER, $profile_user_id);

/* ===========================
   Fragen (Top-20 nach Likes)
   =========================== */
$questions = humplore_profile_load_questions($pdo, $profile_user_id, $isCreator);
$questionIds = array_values(array_filter(array_map(static function ($question) {
  return (int) ($question['id'] ?? 0);
}, $questions), static function (int $id): bool {
  return $id > 0;
}));
$reportedQuestions = humplore_bulk_reported_targets($pdo, $viewerUserId, 'question', $questionIds);
$questions = humplore_apply_report_state($questions, $reportedQuestions);

/* ===========================
   Profil-Standardwerte
   =========================== */
$profileView = humplore_profile_view_model($user, $isCreator, $data);
$profileBio = $profileView['profileBio'];
$profileTitle = $profileView['profileTitle'];
$profileUsername = $profileView['profileUsername'];
$profileTagline = $profileView['profileTagline'];
$profileHashtags = $profileView['profileHashtags'];
$profileLocation = $profileView['profileLocation'];
$profileLanguages = $profileView['profileLanguages'];
$profileExchange = $profileView['profileExchange'];

/* ===========================
   Posts laden (+ Kategorien/Filter)
   =========================== */
$postsData = humplore_profile_load_posts($pdo, $profile_user_id, $activeCatSlug, $viewerUserId, $_GET);
$posts = $postsData['posts'];
$postsPerPage = $postsData['postsPerPage'];
$postsPage = $postsData['postsPage'];
$postsOffset = $postsData['postsOffset'];
$postsTotal = $postsData['postsTotal'];
$postsTotalPages = $postsData['postsTotalPages'];
$likeCountsByPost = $postsData['likeCountsByPost'];
$likedByViewer = $postsData['likedByViewer'];
$commentsByPost = $postsData['commentsByPost'];
$savedByViewer = $postsData['savedByViewer'];

$active = 'profile';



?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Humannlibrary - Profil</title>
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/post-actions.css">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href='https://fonts.googleapis.com/css?family=Lora' rel='stylesheet'>
  <link href='https://fonts.googleapis.com/css?family=DM Serif Display' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    /* (dein bestehendes CSS unverÃƒÆ’Ã‚Â¤ndert) */
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

    /* Schriftarten fÃƒÆ’Ã‚Â¼r spezifische Elemente */
    h1 {
      font-family: 'DM Serif Display';
    }

    /* ==================== */
    /* HEADER & BANNER */
    /* ==================== */
    header {
      background-color: #fff;
      padding: 14px 20px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 70;
      min-height: var(--header-h);
      border-bottom: 1px solid rgba(0, 0, 0, .08);
    }

    .header-inner {
      max-width: 1360px;
      margin: 0 auto;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: var(--brand-img-h);
    }

    /* Logo-Container + BildgrÃƒÆ’Ã‚Â¶ÃƒÆ’Ã…Â¸e */
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
      /* begrenzt die GesamthÃƒÆ’Ã‚Â¶he in der Sidebar */
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
      /* Trennlinie unter der ÃƒÆ’Ã…â€œberschrift */
    }


    .q-scroll {
      flex: 1 1 auto;
      overflow-y: auto;
      padding-right: 6px;
      /* Platz fÃƒÆ’Ã‚Â¼r Scrollbar */
      max-height: 60vh;
      /* eigentliche ScrollflÃƒÆ’Ã‚Â¤chengrÃƒÆ’Ã‚Â¶ÃƒÆ’Ã…Â¸e */
    }

    /* hÃƒÆ’Ã‚Â¼bschere Scrollbar */
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
      /* ErhÃƒÆ’Ã‚Â¶hte Maximalbreite */
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

    /* Flex-Layout fÃƒÆ’Ã‚Â¼r Profilheader */
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
      /* mint-grÃƒÆ’Ã‚Â¼n */
      box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5) inset;
    }

    .follow-btn.is-active .follow-dot {
      background: #fee2e2;
      /* zart-rot fÃƒÆ’Ã‚Â¼r "Entfolgen" aktiv */
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
    /* BEITRÃƒÆ’Ã¢â‚¬Å¾GE */
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

    /* Abstand zwischen AbsÃƒÆ’Ã‚Â¤tzen im Post-Text */
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

    /* ==================== MODAL ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“ modern & sauber (v2) ==================== */
    :root {
      --brand: #6a743a#;
      --modal-bg: rgba(255, 255, 255, .88);
      --modal-overlay: rgba(17, 24, 39, .55);
      --modal-shadow: 0 30px 80px rgba(0, 0, 0, .25);
      --modal-radius: 20px;
      --modal-pad: 22px;

      --header-h: 84px;
      /* HeaderhÃƒÆ’Ã‚Â¶he (grÃƒÆ’Ã‚Â¶ÃƒÆ’Ã…Â¸er) */
      --brand-img-h: 56px;
      /* BildhÃƒÆ’Ã‚Â¶he im Header */
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

    body.side-modal-open {
      overflow: hidden;
    }

    .side-modal-overlay {
      position: fixed;
      inset: 0;
      background: var(--modal-overlay);
      backdrop-filter: saturate(120%) blur(3px);
      z-index: 999;
      opacity: 0;
      display: none;
      transition: opacity .20s ease;
    }

    .side-modal {
      position: fixed;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -46%) scale(.985);
      width: min(92vw, 760px);
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

    .side-modal-open .side-modal-overlay {
      display: block;
      opacity: 1;
    }

    .side-modal-open .side-modal {
      display: block;
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }

    .category-modal-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
      padding: var(--modal-pad);
      padding-top: 0;
    }

    .category-modal-list .cat-item {
      min-width: 0;
    }

    @media (max-width: 860px) {
      .category-modal-list {
        grid-template-columns: 1fr;
      }
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
      /* macht den Text grÃƒÆ’Ã‚Â¶ÃƒÆ’Ã…Â¸er (Standard: ca. 16px ÃƒÂ¢Ã¢â‚¬Â Ã¢â‚¬â„¢ jetzt ca. 17px) */
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

    .profile-side-card {
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
      box-sizing: border-box;
    }

    .profile-side-card::before {
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

    .cat-item.is-active {
      border-color: #aebaa7;
      background: linear-gradient(180deg, #f1f6ee, #edf3e9);
      box-shadow: inset 0 0 0 1px rgba(175, 187, 168, .45);
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
    }

    .stats-share {
      margin-top: 5px;
      padding-top: 5px;
      border-top: 1px solid #dfe6d7;
    }

    .stats-share-title {
      font-size: .9rem;
      font-weight: 800;
      color: #2f3729;
      margin-bottom: 8px;
    }

    .stats-share-row {
      gap: 8px;
    }

    .stats-share-row input {
      flex: 1 1 auto;
      font-size: .82rem;
      padding: 9px 10px;
      border-radius: 10px;
      background: #fff;
    }

    .stats-share-row button {
      padding: 9px 12px;
      border-radius: 10px;
      font-size: .82rem;
      background: #eef2ec;
      color: #34402d;
      border: 1px solid #d3dbcf;
      font-weight: 700;
    }

    .stats-share-row button:hover {
      background: #e5ece2;
    }

    .stats-share-button {
      width: 100%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px 8px;
      border-radius: 9px;
      font-size: .76rem;
  background: #eef2ec;
  color: #34402d;
  border: 1px solid #d3dbcf;
  font-weight: 800;
      cursor: pointer;
      transition: background .2s ease, transform .15s ease;
    }

    .stats-share-button:hover {
      background: #e5ece2;
      transform: translateY(-1px);
    }

    .stats-share-confirmation {
      margin-top: 5px;
      font-size: .72rem;
      color: #58713a;
      text-align: center;
    }

    .copy-confirmation {
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
        height: 132px;
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

    /* Visuelles Highlight fÃƒÆ’Ã‚Â¼r geteilte Karte */
    .shared-highlight {
      outline: 3px solid #4b573e33;
      box-shadow: 0 0 0 4px #4b573e22, 0 6px 18px rgba(0, 0, 0, .12);
      animation: pulseHL 1.2s ease 1;
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
      /* fÃƒÆ’Ã‚Â¼r Slide-Animation */
      transition: max-height .3s ease, padding-top .3s ease, border-top-color .3s ease;
    }

    .comments-section.open {
      max-height: 1000px;
      /* groÃƒÆ’Ã…Â¸ genug fÃƒÆ’Ã‚Â¼r "Slide" */
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


    /* Weniger WeiÃƒÆ’Ã…Â¸raum in Karten/Typografie */
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

    /* kleinere AbsatzabstÃƒÆ’Ã‚Â¤nde */

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

    /* spezifische Farben je Button dauerhaft */
    .like-button {
      color: #d8ab10;
    }

    .like-button:hover {
      color: #f1bf13;
      background: rgba(255, 212, 59, .10);
    }

    .comments-button {
      color: #580F41;
    }

    .save-post-button {
      color: #4f5b47;
    }

    .save-post-button:hover {
      color: #2f6f4f;
      background: rgba(47, 111, 79, .10);
    }

    .save-post-button.saved {
      color: #2f6f4f;
      font-weight: 700;
    }

    .save-post-button.saved .action-icon {
      filter: drop-shadow(0 0 5px rgba(47, 111, 79, .22));
    }

    /* dein Violett */
    .share-button {
      color: #3498db;
    }

    /* Hover-Effekte nur minimal (kein Farbwechsel nÃƒÆ’Ã‚Â¶tig) */
    .action-button:hover {
      background: #f6f6f6;
      filter: brightness(1.02);
    }

    /* Bereits-geliked darf deutlich leuchten */
    .like-button.liked {
      color: #ffd54a;
      font-weight: 700;
      text-shadow: 0 0 10px rgba(255, 213, 74, .45);
    }

    .like-button.liked .action-icon {
      filter: drop-shadow(0 0 5px rgba(255, 221, 87, .8)) drop-shadow(0 0 12px rgba(255, 193, 7, .45));
    }

    .like-button.liked:hover {
      background: rgba(255, 213, 74, .16);
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

/* Stat-Karte: modern, kompakt, ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œRowÃƒÂ¢Ã¢â€šÂ¬Ã‚Â-Look */
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

/* Value rechts groÃƒÆ’Ã…Â¸, Label links */
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

/* Follow-Button als eigene ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œCardÃƒÂ¢Ã¢â€šÂ¬Ã‚Â darunter */
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

/* ÃƒÂ¢Ã‚ÂÃ…â€™ KEIN ÃƒÆ’Ã‚Â¤usserer Rahmen ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“ endgÃƒÆ’Ã‚Â¼ltig aus */
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
  border-radius: 18px;              /* <- gleichmÃƒÆ’Ã‚Â¤ÃƒÆ’Ã…Â¸ige Ecken */
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
  --sidebar-start: 0px;     /* direkt oben im Grid starten */
  --sidebar-stick: calc(var(--header-h) + 87px);   /* wie in Explore */
}

/* beide Sidebars gleich */
.side-container{
  margin-top: var(--sidebar-start);  /* <- DAS ist ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œam Anfang weiter untenÃƒÂ¢Ã¢â€šÂ¬Ã‚Â */
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
  box-sizing: border-box;

  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* ÃƒÆ’Ã…â€œberschrift wie "Label" (wie Stat-Label) */
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

/* Scrollbereich: ohne ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œeigene BoxÃƒÂ¢Ã¢â€šÂ¬Ã‚Â, clean */
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
  max-height: none !important;
  height: auto !important;
}

.questions-card h3 {
  font-size: 1.05rem;
  color: #25301f;
}

.q-scroll {
  max-height: 72vh;
  padding-right: 4px;
}

.questions-card--visitor .q-scroll {
  max-height: clamp(220px, 36vh, 420px) !important;
}

.qa-anonymous-control {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 10px;
  color: #25301f;
  font-size: .92rem;
  font-weight: 800;
  cursor: pointer;
}

.qa-anonymous-control input {
  width: 18px;
  height: 18px;
  margin: 0;
  accent-color: #6a743a;
}

.qa-anonymous-hint {
  margin-top: 5px;
  color: #667160;
  font-size: .8rem;
  line-height: 1.4;
}

.qa-item form textarea {
  width: 100% !important;
  box-sizing: border-box;
}

.qa-answer-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.qa-answer-actions button {
  flex: 1 1 0;
  margin-top: 0 !important;
}

.qa-answer-button--post {
  background: #eef4e7 !important;
  border: 1px solid #d4dec8 !important;
  color: #33402c !important;
}

.qa-answer-button--post:hover {
  background: #e5eedb !important;
  border-color: #c3d0b4 !important;
}

.qa-item {
  margin-bottom: 10px;
}

.qa-mode-chooser {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin-top: 12px;
}

.qa-mode-button,
.qa-inline-button {
  appearance: none;
  border: 1px solid #d8e4d2;
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 800;
  font-size: .92rem;
  cursor: pointer;
  transition: transform .12s ease, box-shadow .18s ease, background .18s ease, border-color .18s ease;
}

.qa-mode-button {
  background: #f7faf4;
  color: #2f3b2b;
}

.qa-mode-button:hover,
.qa-inline-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 24px rgba(28, 43, 24, .08);
}

.qa-mode-button--post,
.qa-inline-button--post-submit {
  background: linear-gradient(135deg, #dff5e7, #cdf0de);
  border-color: #b5ddc5;
  color: #1f4f33;
}

.qa-reply-editor[hidden] {
  display: none !important;
}

.qa-reply-editor {
  margin-top: 12px;
}

.qa-reply-editor textarea,
.qa-post-modal__form textarea,
.qa-post-modal__form input {
  width: 100% !important;
  box-sizing: border-box;
  border: 1px solid #dbe5d5;
  border-radius: 14px;
  background: #fbfdf9;
  padding: 12px 14px;
  font-size: 1rem;
  line-height: 1.55;
  transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
}

.qa-reply-editor textarea:focus,
.qa-post-modal__form textarea:focus,
.qa-post-modal__form input:focus {
  outline: none;
  background: #fff;
  border-color: #98c2aa;
  box-shadow: 0 0 0 4px rgba(116, 185, 146, .14);
}

.qa-inline-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 10px;
}

.qa-inline-button--ghost {
  background: #f4f6f2;
  color: #52604d;
}

.qa-inline-button--send {
  background: #25301f;
  border-color: #25301f;
  color: #fff;
}

body.qa-post-modal-open {
  overflow: hidden;
}

body.qa-post-modal-open .side-container.right-container {
  z-index: 4000 !important;
}

body.qa-post-modal-open .right-container .questions-card {
  overflow: visible !important;
}

.qa-post-modal-overlay {
  position: fixed;
  inset: 0;
  display: none;
  place-items: center;
  padding: 20px;
  background: rgba(17, 24, 39, .52);
  backdrop-filter: blur(4px);
  z-index: 4100;
  opacity: 0;
  transition: opacity .18s ease;
}

body.qa-post-modal-open .qa-post-modal-overlay {
  display: grid;
  opacity: 1;
}

.qa-post-modal {
  width: min(92vw, 680px);
  max-height: min(86vh, 860px);
  overflow: auto;
  background: rgba(255, 255, 255, .97);
  border: 1px solid rgba(216, 228, 210, .95);
  border-radius: 24px;
  box-shadow: 0 34px 80px rgba(9, 19, 15, .24);
  transform: translateY(18px);
  transition: transform .18s ease;
}

body.qa-post-modal-open .qa-post-modal {
  transform: translateY(0);
}

.qa-post-modal__header {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  padding: 22px 24px 16px;
  border-bottom: 1px solid #e3ece0;
}

.qa-post-modal__title {
  font-size: 1.28rem;
  font-weight: 900;
  color: #20311e;
}

.qa-post-modal__sub {
  margin-top: 6px;
  color: #66715f;
  font-size: .92rem;
  line-height: 1.45;
}

.qa-post-modal__close {
  align-self: start;
  appearance: none;
  border: 0;
  background: #eef4ea;
  color: #475642;
  border-radius: 12px;
  padding: 10px 12px;
  font-weight: 800;
  cursor: pointer;
}

.qa-post-modal__form {
  display: grid;
  gap: 12px;
  padding: 20px 24px 24px;
}

.qa-post-modal__form label {
  margin: 0;
  font-weight: 800;
  color: #2c3828;
}

.qa-post-modal__question {
  background: linear-gradient(180deg, #f5fbf7, #edf8f1);
  border: 1px solid #d6eadb;
  border-radius: 16px;
  padding: 14px 16px;
}

.qa-post-modal__eyebrow {
  font-size: .78rem;
  font-weight: 900;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: #5f7868;
}

.qa-post-modal__question-text {
  margin-top: 6px;
  font-weight: 700;
  color: #1f2b1d;
  line-height: 1.5;
}

.qa-post-modal__hint {
  margin-top: -4px;
  font-size: .82rem;
  color: #6f7a69;
}

.qa-post-modal__actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 4px;
}

.post-card.question-post {
  background-color: #f1faf4;
  background-image: linear-gradient(180deg, #f5fcf7 0%, #edf8f1 100%);
  border: 1px solid #cfe5d7;
  box-shadow: 0 12px 28px rgba(83, 133, 104, .10);
}

.post-card.question-post .post-actions {
  border-top-color: rgba(94, 145, 116, .18);
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

@media (max-width: 560px) {
  .qa-mode-chooser,
  .qa-inline-actions,
  .qa-post-modal__actions {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .qa-post-modal-overlay {
    padding: 12px;
  }

  .qa-post-modal__header,
  .qa-post-modal__form {
    padding-left: 16px;
    padding-right: 16px;
  }

  .qa-anonymous-control {
    min-height: 44px;
  }
}

@media (min-width: 768px) and (max-width: 1366px) {
  .header-flex-one {
    flex-direction: column !important;
    align-items: start !important;
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
    margin: -125px auto 40px;
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
  height: 148px !important;
  display: grid !important;
  place-items: center !important;
  position: relative !important;
  overflow: hidden !important;
  background: linear-gradient(135deg, var(--brand-prim), var(--brand-prim)) !important;
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
  box-shadow: none !important;
}

.profile-container {
  margin-top: -44px !important;
  position: relative;
  z-index: 10;
}

.profile-card-spacer {
  height: 0;
}

.profile-compact-trigger {
  display: block;
  height: 1px;
  margin-top: -1px;
  pointer-events: none;
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
  transition: width .2s ease, padding .2s ease, max-width .2s ease, border-radius .2s ease, box-shadow .2s ease;
}

@media (min-width: 769px) {
  .profile-header-shell {
    position: sticky;
    top: calc(var(--header-h) + 12px);
    z-index: 58;
    align-self: start;
  }

  .profile-header-shell.is-compact {
    width: fit-content;
    max-width: calc(100% - 12px);
    margin-left: auto;
    margin-right: auto;
    justify-self: center !important;
  }

  .profile-card-top {
    position: relative !important;
    top: auto !important;
    z-index: 1;
    box-shadow: 0 20px 42px rgba(25, 35, 19, .16) !important;
    border-color: #d9e2cf !important;
  }

  .profile-card-top.is-compact {
    width: fit-content !important;
    max-width: min(calc(100% - 12px), 520px) !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding: 11px 24px 11px 14px !important;
    border-radius: 16px !important;
    box-shadow: 0 14px 28px rgba(25, 35, 19, .18) !important;
  }

  .profile-card-top.is-compact .header-flex-one {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
  }

  .profile-card-top.is-compact .profile-content-grid {
    grid-template-columns: minmax(0, 1fr) 250px;
    gap: 18px;
    align-items: center;
  }

  .profile-card-top.is-compact .profile-header {
    margin: 0 !important;
  }

  .profile-card-top.is-compact .profile-img-wrapper {
    width: 72px !important;
    height: 72px !important;
  }

  .profile-card-top.is-compact .profile-avatar-stack {
    width: 72px !important;
    gap: 6px !important;
  }

  .profile-card-top.is-compact .profile-identity {
    gap: 16px;
    align-items: center;
  }

  .profile-card-top.is-compact .profile-primary {
    gap: 4px;
  }

  .profile-card-top.is-compact .profile-topic-label {
    font-size: .88rem;
  }

  .profile-card-top.is-compact .profile-title {
    padding: 6px 14px !important;
    font-size: 1rem !important;
    box-shadow: 0 6px 14px rgba(85, 100, 54, .18) !important;
  }

  .profile-card-top.is-compact .profile-username {
    font-size: .96rem;
    font-weight: 800;
    line-height: 1.15;
  }

  .profile-card-top.is-compact .profile-identity-meta,
  .profile-card-top.is-compact .profile-tagline,
  .profile-card-top.is-compact .profile-info .bio,
  .profile-card-top.is-compact .edit-profile-btn {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-avatar-follow-form .follow-btn {
    padding: 5px 4px !important;
    font-size: .65rem !important;
    border-radius: 9px !important;
  }

  .profile-card-top.is-compact .profile-content-grid {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
  }

  .profile-card-top.is-compact .profile-info {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-header .profile-info {
    border-left: 0;
    padding-left: 0 !important;
  }

  .profile-card-top.is-compact .profile-meta {
    display: block;
    margin-top: 0;
  }

  .profile-card-top.is-compact .profile-meta-row {
    display: block;
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
  gap: 20px;
  align-items: start;
}

.profile-left-stack {
  display: grid;
  gap: 0;
  align-content: start;
}

.profile-identity {
  display: flex;
  align-items: flex-start;
  gap: 18px;
}

.profile-avatar-stack {
  display: grid;
  gap: 8px;
  justify-items: stretch;
  flex: 0 0 auto;
  width: 122px;
}

.profile-primary {
  min-width: 0;
  display: grid;
  gap: 6px;
}

.profile-topic-label {
  margin: 0 !important;
  padding: 0 !important;
  color: #8a9184;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.1;
}

.profile-header .profile-info {
  padding-top: 0 !important;
  display: grid;
  gap: 6px;
  border-left: 1px solid #e6ebdf;
  padding-left: 20px !important;
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
  font-size: .94rem !important;
  line-height: 1.2 !important;
  margin: 0 !important;
}

.profile-tagline:empty {
  display: none !important;
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
  gap: 5px;
}

.profile-identity-meta {
  gap: 4px;
  padding: 8px 10px;
  border: 1px solid #e1e8d8;
  border-radius: 12px;
  background: #f7f9f4;
}

.profile-identity-meta p {
  font-size: .82rem !important;
  line-height: 1.25 !important;
}

.profile-primary-meta {
  margin: 13px;
  padding: 2px 0 0;
  border: 0;
  border-radius: 0;
  background: transparent;
}

.profile-primary-meta p {
  color: #667160 !important;
}

.profile-info .bio {
  background: #f6f9f1 !important;
  border: 1px solid #e1e8d8 !important;
  border-radius: 14px !important;
  max-width: 100% !important;
  margin: 0 !important;
  padding: 12px 15px !important;
  line-height: 1.5 !important;
}

.profile-bio-actions {
  display: flex;
  align-items: flex-start;
}

.profile-bio-actions .edit-profile-btn {
  margin-top: 0 !important;
}

.profile-bio-actions .edit-profile-btn {
  width: auto !important;
  min-width: 200px;
  margin-top: 0 !important;
  justify-content: center !important;
}

.profile-avatar-follow-form {
  margin-top: 0 !important;
}

.profile-avatar-follow-form .follow-btn {
  width: 100% !important;
  min-width: 0 !important;
  padding: 7px 8px !important;
  font-size: .76rem !important;
  font-weight: 800 !important;
  letter-spacing: .01em;
  border-radius: 11px !important;
  line-height: 1.15 !important;
  background: #eff4e8 !important;
  color: #34402d !important;
  border: 1px solid #ced8c5 !important;
  box-shadow: none !important;
}

.profile-avatar-follow-form .follow-btn:hover {
  background: #e5ecdc !important;
  filter: none !important;
  transform: none !important;
}

.profile-avatar-follow-form .follow-btn.is-active {
  background: #eef1f4 !important;
  color: #4a5563 !important;
  border-color: #d5dbe4 !important;
  box-shadow: none !important;
}

.stats-panel {
  display: block !important;
  border: 1px solid var(--hp-border);
  border-radius: 16px;
  padding: 8px;
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
  margin-top: 0;
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

.post-card.question-post {
  background-color: #f1faf4 !important;
  background-image: linear-gradient(180deg, #f5fcf7 0%, #edf8f1 100%) !important;
  border-color: #cfe5d7 !important;
  box-shadow: 0 12px 28px rgba(83, 133, 104, .10) !important;
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
      gap: 12px;
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
    align-items: start !important;
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

/* iPad Pro Landscape / groÃƒÆ’Ã…Â¸e Tablets: 3 Spalten, aber mit genug Platz */
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

  .profile-avatar-stack {
    width: 104px !important;
    gap: 6px !important;
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
  display: inline !important;
}

.post-actions {
  justify-content: space-around !important;
}

.action-button {
  gap: 6px !important;
}

@media (max-width: 768px) {
  .action-label {
    display: none !important;
  }

  .action-button {
    min-width: 44px !important;
    min-height: 44px !important;
    justify-content: center !important;
  }

  .comments-button.action-button {
    display: inline-flex !important;
  }
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
    gap: 18px !important;
    align-items: center !important;
  }

  .profile-card-top.is-compact .profile-meta-row {
    display: block !important;
    margin-top: 0 !important;
  }

  .profile-card-top.is-compact .edit-profile-btn {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-meta-text {
    justify-self: end !important;
    align-self: center !important;
    text-align: left !important;
    width: 100% !important;
    max-width: 230px !important;
    background: #f6f8f1 !important;
    border: 1px solid #e2e9d9 !important;
    border-radius: 14px !important;
    padding: 12px 14px !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7) !important;
  }

  .profile-card-top.is-compact .profile-meta-text p {
    margin: 0 0 6px 0 !important;
    line-height: 1.35 !important;
    font-size: .92rem !important;
  }

  .profile-card-top.is-compact .profile-meta-text p:last-child {
    margin-bottom: 0 !important;
  }
}
/* Final alignment override: match Explore rails and compact profile stats */
@media (min-width: 981px) {
  .main-content-wrapper {
    width: calc(100% - 16px) !important;
    max-width: 1560px !important;
    margin: -122px auto 32px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    display: grid !important;
    grid-template-columns: 196px minmax(0, 1fr) minmax(250px, 300px) !important;
    column-gap: 12px !important;
    row-gap: 10px !important;
    align-items: start !important;
    justify-content: stretch !important;
  }

  .main-content-wrapper > * {
    min-width: 0 !important;
  }

  .side-container {
    position: sticky !important;
    top: calc(var(--header-h) + 87px) !important;
    margin-top: 0 !important;
    display: flex !important;
    justify-content: center !important;
    align-self: start !important;
    width: 100% !important;
    max-width: none !important;
    height: auto !important;
    padding: 0 !important;
  }

  .left-container,
  .right-container {
    justify-self: stretch !important;
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
  }

  .right-container {
    max-height: none !important;
    overflow: visible !important;
    padding-right: 0 !important;
    scrollbar-gutter: auto !important;
  }

  .profile-side-card,
  .questions-card {
    position: relative !important;
    overflow: hidden !important;
    background: linear-gradient(180deg, #ffffff, #f8faf7) !important;
    border: 1px solid #dde3d8 !important;
    border-radius: 16px !important;
    box-shadow: 0 14px 34px rgba(27, 37, 22, .12) !important;
    padding: 16px !important;
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    min-height: 0 !important;
    margin: 0 !important;
    box-sizing: border-box !important;
  }

  .q-scroll {
    max-height: calc(100vh - var(--header-h) - 220px) !important;
    overflow: auto !important;
    padding-right: 2px !important;
  }

  .profile-container {
    min-width: 0 !important;
    width: 100% !important;
    max-width: 920px !important;
    margin: -18px auto 40px !important;
    padding: 0 !important;
    flex: none !important;
  }

  .profile-card-top {
    margin: 0 !important;
    padding: 22px 24px !important;
  }

  .header-flex-one {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 248px !important;
    gap: 18px !important;
    align-items: start !important;
  }

  .header-flex-two-left {
    width: auto !important;
    min-width: 0 !important;
  }

  .header-flex-two-right {
    display: block !important;
    width: 248px !important;
    min-width: 248px !important;
    padding: 0 !important;
    margin: 0 !important;
    align-self: start !important;
    justify-self: end !important;
  }

  .header-flex-two-right .stats-panel {
    display: block !important;
        height: 100% !important;
    border: 1px solid var(--hp-border) !important;
    border-radius: 18px !important;
    padding: 12px !important;
    box-shadow: 0 10px 24px rgba(25, 35, 19, .08) !important;
  }

  .header-flex-two-right .stats-grid {
    display: grid !important;
    grid-template-columns: 1fr !important;
    gap: 8px !important;
  }

  .profile-card-top.is-compact .header-flex-two-right {
    display: none !important;
  }
}

@media (min-width: 768px) and (max-width: 1180px) {
  .main-content-wrapper {
    grid-template-columns: minmax(210px, 24vw) minmax(0, 1fr) minmax(210px, 24vw) !important;
    gap: 12px !important;
  }

  .left-container,
  .right-container {
    max-width: 300px !important;
  }

  .profile-side-card,
  .questions-card {
    width: min(100%, 292px) !important;
    max-width: 292px !important;
    padding: 12px !important;
  }

  .sidebar-head {
    gap: 8px;
    margin-bottom: 8px;
    padding-bottom: 8px;
  }

  .sidebar-title {
    font-size: .98rem;
  }

  .sidebar-clear {
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
    font-size: .85rem;
  }

  .cat-name {
    font-size: .84rem;
  }

  .cat-go {
    width: 20px;
    height: 20px;
    flex: 0 0 20px;
    font-size: .78rem;
  }

  .sidebar-tip {
    font-size: .74rem;
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
    --side-question-max-fluid: clamp(360px, calc(100vh - var(--header-h) - 210px), 680px);
  }

  .profile-side-card,
  .questions-card {
    padding: var(--side-card-pad-fluid) !important;
  }

  .profile-side-card {
    max-height: var(--side-card-max-fluid) !important;
    overflow: auto !important;
    scrollbar-gutter: stable;
  }

  .sidebar-head {
    gap: var(--side-head-gap-fluid);
    margin-bottom: var(--side-head-gap-fluid);
    padding-bottom: var(--side-head-gap-fluid);
  }

  .sidebar-title,
  .questions-card h3 {
    font-size: var(--side-title-fluid) !important;
  }

  .sidebar-section-label {
    font-size: var(--side-label-fluid) !important;
  }

  .sidebar-section-head {
    margin-top: clamp(10px, 0.6vh + 6px, 14px);
    margin-bottom: clamp(6px, 0.35vh + 4px, 8px);
  }

  .cat-list {
    gap: var(--side-item-gap-fluid) !important;
  }

  .cat-item {
    padding: var(--side-item-pad-y-fluid) var(--side-item-pad-x-fluid) !important;
  }

  .cat-left {
    gap: var(--side-item-gap-fluid) !important;
  }

  .cat-icon {
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

  .q-scroll {
    max-height: var(--side-question-max-fluid) !important;
    padding-right: clamp(3px, 0.2vw + 2px, 6px) !important;
  }
}

@media (min-width: 981px) {
  .main-content-wrapper {
    grid-template-columns: 196px minmax(0, 1fr) minmax(250px, 300px) !important;
    justify-content: stretch !important;
  }

  .left-container,
  .right-container {
    width: 100% !important;
    max-width: none !important;
    justify-self: stretch !important;
  }

  .left-container .profile-side-card,
  .left-container .questions-card,
  .right-container .profile-side-card,
  .right-container .questions-card {
    width: 100% !important;
    max-width: none !important;
    min-width: 0 !important;
    max-height: none !important;
    overflow: visible !important;
    scrollbar-gutter: auto !important;
  }
}

.profile-header-shell {
  width: 100%;
}

.profile-header-shell .profile-card-spacer {
  height: 0;
}

@media (min-width: 981px) {
  .profile-header-shell {
    grid-column: 1 / -1;
    width: 100%;
    margin: 0;
    position: sticky;
    top: calc(var(--header-h) + 12px);
    z-index: 58;
    align-self: start;
  }

  .profile-header-shell.is-compact {
    width: fit-content;
    max-width: calc(100% - 12px);
    margin-left: auto;
    margin-right: auto;
    justify-self: center !important;
  }

  .profile-container {
    grid-column: 2;
    min-width: 0 !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 auto 40px !important;
    padding: 0 !important;
  }

  .left-container {
    grid-column: 1;
  }

  .right-container {
    grid-column: 3;
  }

  .profile-card-top {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 22px 24px !important;
    overflow: visible !important;
  }

  .header-flex-one {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) minmax(270px, 320px) !important;
    gap: 20px !important;
    align-items: start !important;
  }

  .header-flex-two-left {
    width: 100% !important;
    min-width: 0 !important;
  }

  .profile-content-grid {
    grid-template-columns: minmax(250px, 330px) minmax(0, 1fr) !important;
    gap: 20px !important;
    align-items: start !important;
  }

  .profile-left-stack {
    gap: 0 !important;
  }

  .profile-identity {
    gap: 16px !important;
  }

  .profile-avatar-stack {
    width: 122px !important;
    gap: 7px !important;
  }

  .profile-primary {
    gap: 5px !important;
    align-content: start !important;
  }

  .profile-header .profile-info {
    border-left: 1px solid #e6ebdf !important;
    padding-left: 18px !important;
    gap: 8px !important;
  }

  .profile-info .bio {
    max-width: none !important;
  }

  .header-flex-two-right {
    width: 100% !important;
    min-width: 0 !important;
    max-width: 320px !important;
    justify-self: end !important;
  }

  .header-flex-two-right .stats-panel {
    display: grid !important;
    gap: 6px !important;
    padding: 7px !important;
  }

  .header-flex-two-right .stats-grid {
    gap: 4px !important;
  }

  .header-flex-two-right .stat-card {
    gap: 6px !important;
    padding: 5px 6px !important;
    border-radius: 10px !important;
  }

  .header-flex-two-right .stat-icon {
    width: 26px !important;
    height: 26px !important;
    border-radius: 8px !important;
  }

  .header-flex-two-right .stat-icon svg {
    width: 14px !important;
    height: 14px !important;
  }

  .header-flex-two-right .stat-label {
    font-size: .78rem !important;
    line-height: 1.1 !important;
  }

  .header-flex-two-right .stat-sub {
    font-size: .62rem !important;
    margin-top: 0 !important;
    line-height: 1.1 !important;
  }

  .header-flex-two-right .stat-value {
    font-size: .88rem !important;
    padding: 3px 6px !important;
    border-radius: 7px !important;
  }

  .profile-card-top.is-compact {
    width: fit-content !important;
    max-width: min(calc(100% - 12px), 520px) !important;
    margin-left: auto !important;
    margin-right: auto !important;
    padding: 11px 24px 11px 14px !important;
    border-radius: 18px !important;
  }
}

@media (min-width: 769px) {
  .profile-header-shell .profile-card-spacer {
    display: block !important;
  }

  .profile-card-top.is-compact .header-flex-one {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
  }

  .profile-card-top.is-compact .profile-content-grid {
    grid-template-columns: 1fr !important;
    gap: 0 !important;
    align-items: stretch !important;
  }

  .profile-card-top.is-compact .profile-header {
    margin: 0 !important;
  }

  .profile-card-top.is-compact .profile-left-stack {
    min-width: 0 !important;
    display: block !important;
  }

  .profile-card-top.is-compact .profile-identity {
    align-items: center !important;
    gap: 10px !important;
  }

  .profile-card-top.is-compact .profile-avatar-stack {
    width: 58px !important;
    gap: 0 !important;
  }

  .profile-card-top.is-compact .profile-img-wrapper {
    width: 58px !important;
    height: 58px !important;
  }

  .profile-card-top.is-compact .profile-avatar-follow-form,
  .profile-card-top.is-compact .profile-tagline,
  .profile-card-top.is-compact .profile-info,
  .profile-card-top.is-compact .profile-info .bio,
  .profile-card-top.is-compact .profile-bio-actions,
  .profile-card-top.is-compact .edit-profile-btn,
  .profile-card-top.is-compact .header-flex-two-right {
    display: none !important;
  }

  .profile-card-top.is-compact .profile-primary {
    display: grid !important;
    width: fit-content !important;
    max-width: 100% !important;
    grid-template-columns: fit-content(170px) 156px !important;
    justify-content: start !important;
    column-gap: 8px !important;
    row-gap: 4px !important;
    align-items: center !important;
    flex: 0 1 auto !important;
    min-width: 0 !important;
  }

  .profile-card-top.is-compact .profile-topic-label,
  .profile-card-top.is-compact .profile-title,
  .profile-card-top.is-compact .profile-username {
    grid-column: 1 !important;
    justify-self: start !important;
  }

  .profile-card-top.is-compact .profile-topic-label {
    font-size: .84rem !important;
    color: #747c70 !important;
  }

  .profile-card-top.is-compact .profile-title {
    margin: 0 !important;
    padding: 4px 10px !important;
    font-size: .9rem !important;
    box-shadow: 0 7px 16px rgba(85, 100, 54, .18) !important;
  }

  .profile-card-top.is-compact .profile-username {
    font-size: .94rem !important;
    font-weight: 800 !important;
    line-height: 1.15 !important;
  }

  .profile-card-top.is-compact .profile-identity-meta,
  .profile-card-top.is-compact .profile-primary-meta {
    display: grid !important;
    grid-column: 2 !important;
    grid-row: 1 / span 3 !important;
    justify-self: start !important;
    align-self: center !important;
    width: 100% !important;
    max-width: 156px !important;
    margin: 0 !important;
    padding: 9px 10px !important;
    gap: 5px !important;
    border: 1px solid #dbe5d1 !important;
    border-radius: 16px !important;
    background: linear-gradient(180deg, #f7f9f3 0%, #f1f5eb 100%) !important;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, .82) !important;
  }

  .profile-card-top.is-compact .profile-identity-meta p,
  .profile-card-top.is-compact .profile-primary-meta p {
    margin: 0 !important;
    color: #5d6758 !important;
    font-size: .82rem !important;
    line-height: 1.28 !important;
  }

  .profile-card-top.is-compact .profile-identity-meta strong,
  .profile-card-top.is-compact .profile-primary-meta strong {
    color: #495345 !important;
    font-weight: 800 !important;
  }
}

@media (min-width: 769px) and (max-width: 980px) {
  .profile-card-top.is-compact .profile-primary {
    grid-template-columns: fit-content(156px) 144px !important;
    column-gap: 8px !important;
  }

  .profile-card-top.is-compact .profile-identity-meta,
  .profile-card-top.is-compact .profile-primary-meta {
    max-width: 144px !important;
  }
}

@media (max-width: 980px) {
  .profile-header-shell {
    width: 100%;
    margin: 0 0 10px 0;
  }

  .profile-container {
    margin-top: 0 !important;
  }
}

@media (max-width: 768px) {
  .profile-header-shell {
    order: 1;
  }

  .side-container.right-container {
    display: flex !important;
    order: 2;
    position: static !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 12px 12px !important;
    box-sizing: border-box;
    align-items: stretch;
  }

  .right-container .questions-card {
    width: 100% !important;
    max-width: none !important;
    overflow: visible !important;
  }

  .profile-container {
    order: 3;
  }
}
  </style>
</head>

<body data-post-share-title="Beitrag ansehen" data-post-share-text="Schau dir diesen Beitrag auf humplore an."
  data-post-share-confirmation="Link zum Beitrag kopiert!">
  <!-- Header -->
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

  <div class="banner"></div>

  <div class="main-content-wrapper">
    <div class="profile-header-shell">
      <div class="profile-compact-trigger" id="profileCompactTrigger" aria-hidden="true"></div>
      <?php require __DIR__ . '/app/views/partials/profile-header-card.php'; ?>
      <div class="profile-card-spacer" id="profileCardSpacer"></div>
    </div>

    <!-- Linke Seitenleiste -->
    <div class="side-container left-container">
      <div class="profile-side-card">
        <div class="sidebar-section-label">Navigation</div>
        <div class="cat-list">
          <a class="cat-item" href="platform.php">
            <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">Explore</span></span>
            <span class="cat-go" aria-hidden="true">&rarr;</span>
          </a>
          <a class="cat-item" href="gemerkt.php">
            <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">Gemerkt</span></span>
            <span class="cat-go" aria-hidden="true">&rarr;</span>
          </a>
          <?php if ($viewerIsCreator): ?>
            <a class="cat-item" href="posten.php">
              <span class="cat-left"><span class="cat-icon" aria-hidden="true">+</span><span class="cat-name">Posten</span></span>
              <span class="cat-go" aria-hidden="true">&rarr;</span>
            </a>
          <?php endif; ?>
          <a class="cat-item" href="news.php">
            <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">News</span></span>
            <span class="cat-go" aria-hidden="true">&rarr;</span>
          </a>
          <?php if ($viewerIsCreator): ?>
            <a class="cat-item is-active" href="profile.php?user_id=<?= (int) $viewerUserId ?>">
              <span class="cat-left"><span class="cat-icon" aria-hidden="true">&bull;</span><span class="cat-name">Profil</span></span>
              <span class="cat-go" aria-hidden="true">&rarr;</span>
            </a>
          <?php endif; ?>
        </div>

<?php
        $sidebarCats = humplore_profile_sidebar_categories();
        $visibleCats = array_slice($sidebarCats, 0, 5);
        $hiddenCats = array_slice($sidebarCats, 5);
        $currentQ = txt_lower(trim((string) ($_GET['q'] ?? '')));
        ?>

        <div class="sidebar-section-head">
          <div class="sidebar-section-label">Beitrags-/Lebenskategorien</div>
          <?php if (!empty($hiddenCats)): ?>
            <button type="button" class="sidebar-clear sidebar-clear--button" id="cat-more-toggle" aria-expanded="false" aria-controls="categoriesSidebarModal">
              mehr
            </button>
          <?php endif; ?>
        </div>

        <div class="cat-list">
          <?php foreach ($visibleCats as $item): ?>
            <?php require __DIR__ . '/app/views/partials/profile-sidebar-category-link.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Hauptprofilbereich -->
    <div class="profile-container">
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

          <div class="profile-compact-trigger" id="profilePostsCompactTrigger" aria-hidden="true"></div>
          <div class="posts-container" id="profilePostsAnchor">
            <?php
            if ($posts) {
              foreach ($posts as $post) {
                $viewerId = $viewerUserId;
                $postId = (int) $post['id'];
                $comments = $commentsByPost[$postId] ?? [];
                $commentCount = count($comments);
                $likeCount = (int) ($likeCountsByPost[$postId] ?? 0);
                $hasLiked = !empty($likedByViewer[$postId]);
                $hasSaved = !empty($savedByViewer[$postId]);
                $postCardClass = post_has_access($post, $viewerId) ? '' : ' locked';
                $priceLabel = isset($post['price_cents']) ? formatEuroCents($post['price_cents']) : '';
                $profileOwner = $user;
                $profileOwnerId = (int) $profile_user_id;
                $canDelete = $is_own_profile && (int) $post['creator_id'] === $viewerUserId;
                $csrfToken = $csrf_token;
                $commentAvatarInitial = strtoupper(substr((string) $user['username'], 0, 1));
                $previewLimit = 220;
                require __DIR__ . '/app/views/partials/profile-post-card.php';
              } // foreach posts
            } else {
              echo "<p>Noch keine BeitrÃƒÆ’Ã‚Â¤ge vorhanden.</p>";
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
                  <a class="cat-chip" href="profile.php?<?= htmlspecialchars(http_build_query($prevParams), ENT_QUOTES, 'UTF-8') ?>">ZurÃƒÆ’Ã‚Â¼ck</a>
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
      <?php require __DIR__ . '/app/views/partials/profile-questions-card.php'; ?>
    </div>
    <?php require __DIR__ . '/app/views/partials/profile-categories-modal.php'; ?>

    <?php if ($is_own_profile): ?>
      <?php require __DIR__ . '/app/views/partials/profile-settings-modal.php'; ?>
    <?php endif; ?>



    <script>

      const csrfToken = "<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>";

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

        const formData = new FormData(form);
        formData.append('csrf_token', csrfToken);

        fetch('report_handler.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin',
          headers: { 'X-CSRF-Token': csrfToken }
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

      // Profillink kopieren
      async function copyProfileLink(trigger) {
        const profileLink = trigger?.dataset?.profileLink || "";
        const confirmation = document.getElementById("copyConfirmation");
        if (!profileLink) return;

        if (navigator.clipboard && window.isSecureContext) {
          try {
            await navigator.clipboard.writeText(profileLink);
          } catch (_) {}
        } else {
          const tempInput = document.createElement("textarea");
          tempInput.value = profileLink;
          tempInput.setAttribute("readonly", "");
          tempInput.style.position = "absolute";
          tempInput.style.left = "-9999px";
          document.body.appendChild(tempInput);
          tempInput.select();
          tempInput.setSelectionRange(0, tempInput.value.length);
          try { document.execCommand("copy"); } catch (_) {}
          document.body.removeChild(tempInput);
        }
        if (confirmation) {
          confirmation.style.display = "block";
          setTimeout(() => { confirmation.style.display = "none"; }, 2000);
        }
      }

      // Auto-Resize fÃƒÆ’Ã‚Â¼r Kommentar-Textareas (optional, nice UX)
      function autosizeTextarea(ta) {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
      }
      document.addEventListener('input', e => {
        if (e.target.matches('.comment-input textarea, .qa-reply-editor textarea, .qa-post-modal__form textarea')) autosizeTextarea(e.target);
      });
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.comment-input textarea, .qa-reply-editor textarea, .qa-post-modal__form textarea').forEach(autosizeTextarea);
      });

      // ===== Modal: ÃƒÆ’Ã‚Â¶ffnen/schlieÃƒÆ’Ã…Â¸en + Fokus-Management =====
      const qaPostModalOverlay = document.getElementById('qaPostModalOverlay');
      const qaPostModalClose = document.getElementById('qaPostModalClose');
      const qaPostModalCancel = document.getElementById('qaPostModalCancel');
      const qaPostModalQuestionId = document.getElementById('qaPostModalQuestionId');
      const qaPostModalQuestionText = document.getElementById('qaPostModalQuestionText');
      const qaPostModalAnswer = document.getElementById('qaPostModalAnswer');
      const qaPostModalCategory = document.getElementById('qaPostModalCategory');
      let qaLastTrigger = null;

      function closeReplyEditors() {
        document.querySelectorAll('.qa-reply-editor').forEach((form) => {
          form.hidden = true;
          const textarea = form.querySelector('textarea');
          if (textarea) {
            textarea.value = '';
            textarea.style.height = 'auto';
          }
        });

        document.querySelectorAll('.qa-mode-chooser').forEach((chooser) => {
          chooser.hidden = false;
        });
      }

      function openReplyEditor(questionId) {
        closeReplyEditors();

        const chooser = document.querySelector(`.qa-mode-chooser[data-question-id="${questionId}"]`);
        const form = document.querySelector(`.qa-reply-editor[data-question-id="${questionId}"]`);
        if (!chooser || !form) return;

        chooser.hidden = true;
        form.hidden = false;

        const textarea = form.querySelector('textarea');
        if (textarea) {
          autosizeTextarea(textarea);
          textarea.focus();
        }
      }

      function closeQaPostModal() {
        if (!qaPostModalOverlay) return;

        document.body.classList.remove('qa-post-modal-open');
        qaPostModalOverlay.setAttribute('aria-hidden', 'true');

        if (qaPostModalQuestionId) qaPostModalQuestionId.value = '';
        if (qaPostModalQuestionText) qaPostModalQuestionText.textContent = '';
        if (qaPostModalAnswer) {
          qaPostModalAnswer.value = '';
          qaPostModalAnswer.style.height = 'auto';
        }
        if (qaPostModalCategory) qaPostModalCategory.value = '';

        if (qaLastTrigger) qaLastTrigger.focus();
        qaLastTrigger = null;
      }

      function openQaPostModal(questionId, questionText, trigger) {
        if (!qaPostModalOverlay) return;

        closeReplyEditors();
        qaLastTrigger = trigger || null;
        if (qaPostModalQuestionId) qaPostModalQuestionId.value = questionId;
        if (qaPostModalQuestionText) qaPostModalQuestionText.textContent = questionText || '';
        if (qaPostModalAnswer) {
          qaPostModalAnswer.value = '';
          qaPostModalAnswer.style.height = 'auto';
        }
        if (qaPostModalCategory) qaPostModalCategory.value = '';

        document.body.classList.add('qa-post-modal-open');
        qaPostModalOverlay.setAttribute('aria-hidden', 'false');

        window.setTimeout(() => {
          if (qaPostModalAnswer) {
            autosizeTextarea(qaPostModalAnswer);
            qaPostModalAnswer.focus();
          }
        }, 20);
      }

      document.querySelectorAll('.qa-mode-button').forEach((button) => {
        button.addEventListener('click', () => {
          const questionId = button.getAttribute('data-question-id');
          const mode = button.getAttribute('data-answer-mode');
          if (!questionId || !mode) return;

          if (mode === 'reply') {
            openReplyEditor(questionId);
            return;
          }

          openQaPostModal(questionId, button.getAttribute('data-question-text') || '', button);
        });
      });

      document.querySelectorAll('[data-qa-cancel]').forEach((button) => {
        button.addEventListener('click', () => {
          const questionId = button.getAttribute('data-qa-cancel');
          if (!questionId) return;

          const chooser = document.querySelector(`.qa-mode-chooser[data-question-id="${questionId}"]`);
          const form = document.querySelector(`.qa-reply-editor[data-question-id="${questionId}"]`);
          if (chooser) chooser.hidden = false;
          if (form) {
            form.hidden = true;
            const textarea = form.querySelector('textarea');
            if (textarea) {
              textarea.value = '';
              textarea.style.height = 'auto';
            }
          }
        });
      });

      qaPostModalClose?.addEventListener('click', closeQaPostModal);
      qaPostModalCancel?.addEventListener('click', closeQaPostModal);
      qaPostModalOverlay?.addEventListener('click', (e) => {
        if (e.target === qaPostModalOverlay) {
          closeQaPostModal();
        }
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('qa-post-modal-open')) {
          closeQaPostModal();
        }
      });

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


      // Overlay-Klick schlieÃƒÆ’Ã…Â¸t Modal
      if (overlayEl) {
        overlayEl.addEventListener('click', (e) => {
          if (e.target === overlayEl) closeModal();
        });
      }

      // ===== Live-Preview fÃƒÆ’Ã‚Â¼r Profilbild + Validierung =====
      function previewImage(input) {
        const file = input.files && input.files[0];
        if (!file) return;

        const maxBytes = 5 * 1024 * 1024; // 5 MB
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        if (!allowed.includes(file.type)) {
          alert('Bitte ein Bild im Format JPG/PNG/WebP/GIF wÃƒÆ’Ã‚Â¤hlen.');
          input.value = '';
          return;
        }
        if (file.size > maxBytes) {
          alert('Die Datei ist zu groÃƒÆ’Ã…Â¸ (max. 5 MB).');
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

      // ===== Bio-ZeichenzÃƒÆ’Ã‚Â¤hler =====
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

    // ÃƒÂ¢Ã…â€œÃ¢â‚¬Â¦ Nur sichtbare <p> (nicht innerhalb .more-content)
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

      // Kebab-MenÃƒÆ’Ã‚Â¼ ÃƒÆ’Ã‚Â¶ffnen/schlieÃƒÆ’Ã…Â¸en
      let openMenuId = null;

      function togglePostMenu(e, postId) {
        e.stopPropagation();
        const menu = document.getElementById(`menu-${postId}`);
        const trigger = e.currentTarget;

        // SchlieÃƒÆ’Ã…Â¸e anderes offenes MenÃƒÆ’Ã‚Â¼
        if (openMenuId && openMenuId !== postId) {
          const prev = document.getElementById(`menu-${openMenuId}`);
          if (prev) prev.classList.remove('open');
        }

        const willOpen = !menu.classList.contains('open');
        menu.classList.toggle('open', willOpen);
        trigger.setAttribute('aria-expanded', String(willOpen));
        openMenuId = willOpen ? postId : null;
      }

      // Klicke auÃƒÆ’Ã…Â¸erhalb -> MenÃƒÆ’Ã‚Â¼s schlieÃƒÆ’Ã…Â¸en
      document.addEventListener('click', () => {
        if (!openMenuId) return;
        const menu = document.getElementById(`menu-${openMenuId}`);
        if (menu) menu.classList.remove('open');
        openMenuId = null;
      });

      // Esc schlieÃƒÆ’Ã…Â¸t offenes MenÃƒÆ’Ã‚Â¼
      document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape' && openMenuId) {
          const menu = document.getElementById(`menu-${openMenuId}`);
          if (menu) menu.classList.remove('open');
          openMenuId = null;
        }
      });

      // BestÃƒÆ’Ã‚Â¤tigungsdialog fÃƒÆ’Ã‚Â¼rs LÃƒÆ’Ã‚Â¶schen
      function confirmDelete(postId) {
        return confirm('Diesen Beitrag wirklich lÃƒÆ’Ã‚Â¶schen?');
      }

      // === Live-Preview + Validierung (ersetzt/erweitert deine previewImage) ===
      function previewImageFromFile(file) {
        const maxBytes = 5 * 1024 * 1024;
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!file) return;
        if (!allowed.includes(file.type)) { alert('Bitte JPG/PNG/WebP/GIF wÃƒÆ’Ã‚Â¤hlen.'); return; }
        if (file.size > maxBytes) { alert('Die Datei ist zu groÃƒÆ’Ã…Â¸ (max. 5 MB).'); return; }

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

      // Kompaktmodus fÃƒÆ’Ã‚Â¼r sticky Profilkarte beim Scrollen (Desktop/Tablet)
      function initProfileHeaderCompact() {
        const profileCard = document.querySelector('.profile-card-top');
        const profileShell = profileCard?.closest('.profile-header-shell');
        const spacer = document.getElementById('profileCardSpacer');
        const postsTrigger = document.getElementById('profilePostsCompactTrigger');
        const postsAnchor = document.getElementById('profilePostsAnchor');
        const fallbackTrigger = document.getElementById('profileCompactTrigger');
        const compactAnchor = postsTrigger || postsAnchor || fallbackTrigger;
        if (!profileCard || !profileShell || !spacer || !compactAnchor) return;

        const desktopMq = window.matchMedia('(min-width: 769px)');
        let compactDiff = 0;
        let isCompact = false;
        let compactObserver = null;
        let resizeTicking = false;

        const getStickyTop = () => {
          const rootStyles = getComputedStyle(document.documentElement);
          const headerHeight = parseFloat(rootStyles.getPropertyValue('--header-h')) || 84;
          return headerHeight + 12;
        };

        const setCompactState = (nextCompact) => {
          const normalizedState = desktopMq.matches ? nextCompact : false;
          if (isCompact === normalizedState) return;

          isCompact = normalizedState;
          profileShell.classList.toggle('is-compact', isCompact);
          profileCard.classList.toggle('is-compact', isCompact);
          spacer.style.height = isCompact ? `${compactDiff}px` : '0px';
        };

        const measureCompactDiff = () => {
          const previousState = isCompact;
          profileShell.classList.remove('is-compact');
          profileCard.classList.remove('is-compact');
          spacer.style.height = '0px';

          const expandedHeight = profileCard.offsetHeight;
          profileShell.classList.add('is-compact');
          profileCard.classList.add('is-compact');
          const compactHeight = profileCard.offsetHeight;
          compactDiff = Math.max(0, expandedHeight - compactHeight);

          profileShell.classList.toggle('is-compact', previousState);
          profileCard.classList.toggle('is-compact', previousState);
          spacer.style.height = previousState ? `${compactDiff}px` : '0px';
        };

        const disconnectCompactObserver = () => {
          if (!compactObserver) return;
          compactObserver.disconnect();
          compactObserver = null;
        };

        const refreshCompactObserver = () => {
          disconnectCompactObserver();

          if (!desktopMq.matches) {
            compactDiff = 0;
            setCompactState(false);
            return;
          }

          measureCompactDiff();

          const topOffset = getStickyTop();
          const evaluateCompactState = () => {
            setCompactState(compactAnchor.getBoundingClientRect().top <= topOffset);
          };

          compactObserver = new IntersectionObserver(() => {
            evaluateCompactState();
          }, {
            root: null,
            threshold: 0,
            rootMargin: `-${topOffset}px 0px 0px 0px`
          });

          compactObserver.observe(compactAnchor);
          evaluateCompactState();
        };

        const scheduleObserverRefresh = () => {
          if (resizeTicking) return;
          resizeTicking = true;
          window.requestAnimationFrame(() => {
            resizeTicking = false;
            refreshCompactObserver();
          });
        };

        refreshCompactObserver();
        window.addEventListener('resize', scheduleObserverRefresh);
        desktopMq.addEventListener?.('change', scheduleObserverRefresh);
      }
      if (document.readyState === 'complete') {
        initProfileHeaderCompact();
      } else {
        window.addEventListener('load', initProfileHeaderCompact, { once: true });
      }

      const catMoreToggle = document.getElementById('cat-more-toggle');
      const categoriesSidebarModal = document.getElementById('categoriesSidebarModal');
      const categoriesSidebarClose = document.getElementById('categoriesSidebarClose');
      const categoriesOverlay = document.getElementById('categoriesOverlay');

      function openCategoriesSidebarModal() {
        if (!categoriesSidebarModal || !categoriesOverlay) return;
        document.body.classList.add('side-modal-open');
        categoriesOverlay.setAttribute('aria-hidden', 'false');
        if (catMoreToggle) catMoreToggle.setAttribute('aria-expanded', 'true');
      }

      function closeCategoriesSidebarModal() {
        document.body.classList.remove('side-modal-open');
        categoriesOverlay?.setAttribute('aria-hidden', 'true');
        if (catMoreToggle) catMoreToggle.setAttribute('aria-expanded', 'false');
      }

      if (catMoreToggle) {
        catMoreToggle.addEventListener('click', openCategoriesSidebarModal);
      }
      categoriesSidebarClose?.addEventListener('click', closeCategoriesSidebarModal);
      categoriesOverlay?.addEventListener('click', (e) => {
        if (e.target === categoriesOverlay) closeCategoriesSidebarModal();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('side-modal-open')) {
          closeCategoriesSidebarModal();
        }
      });


  </script>
  <script src="js/post-actions.js?v=20260811"></script>
</body>

</html>












