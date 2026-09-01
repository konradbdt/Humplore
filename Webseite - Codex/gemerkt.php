<?php
declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

humplore_require_login();

$pdo = humplore_db();
$userId = humplore_current_user_id();
$csrf_token = humplore_ensure_csrf_token();

humplore_platform_handle_comment_submission(
    $pdo,
    $userId,
    $_POST,
    $_GET,
    (string) ($_SERVER['REQUEST_URI'] ?? 'gemerkt.php')
);

$postsPerPage = 8;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $postsPerPage;

$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM SavedPosts sp JOIN Posts p ON p.id = sp.post_id WHERE sp.user_id = ?');
$stmtCount->execute([$userId]);
$savedPostCount = (int) $stmtCount->fetchColumn();
$totalPages = max(1, (int) ceil($savedPostCount / $postsPerPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $postsPerPage;
}

$stmtPosts = $pdo->prepare(humplore_platform_posts_select_sql() . "
    JOIN SavedPosts sp ON sp.post_id = p.id
    WHERE sp.user_id = :user_id
    ORDER BY sp.created_at DESC, p.created_at DESC, p.id DESC
    LIMIT :limit OFFSET :offset
");
$stmtPosts->bindValue(':user_id', $userId, PDO::PARAM_INT);
$stmtPosts->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$stmtPosts->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtPosts->execute();
$savedPosts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);

$postIds = array_values(array_filter(array_map(static function (array $post): int {
    return (int) ($post['id'] ?? 0);
}, $savedPosts), static function (int $postId): bool {
    return $postId > 0;
}));

[$likeCountsByPost, $likedByViewer] = getBulkLikeInfo($pdo, $postIds, $userId);
$commentsByPost = getBulkComments($pdo, $postIds);
$commentIds = [];
foreach ($commentsByPost as $comments) {
    foreach ($comments as $comment) {
        $commentId = (int) ($comment['id'] ?? 0);
        if ($commentId > 0) {
            $commentIds[] = $commentId;
        }
    }
}
$reportedComments = humplore_bulk_reported_targets($pdo, $userId, 'comment', $commentIds);
$commentsByPost = humplore_apply_comment_report_state_map($commentsByPost, $reportedComments);
$viewerInitial = strtoupper(substr((string) ($_SESSION['username'] ?? $userId), 0, 1));
$active = 'saved';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Humplore – Gemerkte Beiträge</title>
  <meta name="csrf-token" content="<?= e($csrf_token) ?>">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/post-actions.css">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --brand: #6a743a; --ink: #2c2f2b; --muted: #68735f; --surface: #fff; --border: #dfe6d9; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; padding-bottom: 88px; background: #f6f7f6; color: var(--ink); font-family: Poppins, system-ui, sans-serif; }
    body > header { height: 84px; display: grid; place-items: center; background: var(--surface); border-bottom: 1px solid rgba(0,0,0,.08); box-shadow: 0 2px 4px rgba(0,0,0,.04); }
    body > header .brand { display: inline-flex; height: 100%; align-items: center; }
    body > header .brand img { display: block; height: 56px; width: auto; }
    .saved-page { width: min(920px, calc(100% - 28px)); margin: 34px auto; }
    .saved-heading { margin-bottom: 18px; padding: 22px; border: 1px solid var(--border); border-radius: 18px; background: var(--surface); box-shadow: 0 6px 16px rgba(0,0,0,.08); }
    .saved-heading h1 { margin: 0; color: #25301f; font-family: 'DM Serif Display', Georgia, serif; font-size: clamp(1.8rem, 5vw, 2.45rem); }
    .saved-heading p { margin: 7px 0 0; color: var(--muted); font-weight: 600; }
    .saved-feed { display: grid; gap: 16px; }
    .post-card { display: block; min-width: 0; }
    .post-inner { padding: 18px; border: 1px solid var(--border); border-radius: 18px; background: var(--surface); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
    .post-header { display: flex; gap: 12px; align-items: center; }
    .post-avatar { display: grid; flex: 0 0 44px; width: 44px; height: 44px; overflow: hidden; place-items: center; border-radius: 50%; background: #e8efe0; color: #4b573e; font-weight: 800; }
    .post-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .post-meta { min-width: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 5px 8px; }
    .author { color: #283221; font-weight: 800; overflow-wrap: anywhere; }
    .date, .post-catline { color: #72806c; font-size: .82rem; font-weight: 600; }
    .topic-pill, .cat-pill { padding: 3px 8px; border-radius: 999px; background: #eef3ea; color: #4b573e; font-size: .75rem; font-weight: 700; }
    .post-title { margin: 16px 0 8px; color: #25301f; font-family: 'DM Serif Display', Georgia, serif; font-size: 1.45rem; line-height: 1.2; }
    .post-catline { margin: 10px 0; }
    .post-image { margin: 14px 0; overflow: hidden; border-radius: 12px; background: #eef1eb; }
    .post-image img { display: block; width: 100%; max-height: 520px; object-fit: cover; }
    .post-content { color: #374151; line-height: 1.7; overflow-wrap: anywhere; }
    .post-content p { margin: 10px 0 0; }
    .post-content p:first-child { margin-top: 0; }
    .empty-state { padding: 34px 22px; border: 1px dashed #cdd8c5; border-radius: 18px; background: #fff; text-align: center; }
    .empty-state h2 { margin: 0; color: #35422b; font-family: 'DM Serif Display', Georgia, serif; font-size: 1.55rem; }
    .empty-state p { max-width: 44ch; margin: 8px auto 18px; color: var(--muted); }
    .empty-state a, .pagination a { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border-radius: 10px; background: var(--brand); color: #fff; padding: 8px 14px; font-weight: 800; text-decoration: none; }
    .pagination { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px; margin-top: 20px; color: var(--muted); font-weight: 700; }
    .pagination a { background: #4b573e; }
    .bottom-nav { max-width: 600px; }
    @media (max-width: 720px) {
      body > header { height: 72px; }
      body > header .brand img { height: 48px; }
      .saved-page { width: min(100% - 24px, 920px); margin: 22px auto; }
      .saved-heading, .post-inner { border-radius: 14px; }
      .saved-heading { padding: 18px; }
      .post-inner { padding: 14px; }
      .bottom-nav { width: calc(100% - 20px); }
      .bottom-nav a { padding: 6px 8px; font-size: .78rem; }
    }
  </style>
</head>
<body data-saved-posts-page="true" data-post-share-title="Gemerkter Beitrag auf humplore" data-post-share-text="Schau dir diesen Beitrag auf humplore an." data-post-share-confirmation="Link kopiert!">
  <header>
    <a href="platform.php" class="brand" aria-label="Humplore – Startseite">
      <img src="/pic/humplore-logo.png" alt="humplore Logo">
    </a>
  </header>
  <main class="saved-page">
    <section class="saved-heading" aria-labelledby="saved-title">
      <h1 id="saved-title">Gemerkte Beiträge</h1>
      <p><?= $savedPostCount === 1 ? '1 Beitrag ist für dich vorgemerkt.' : (int) $savedPostCount . ' Beiträge sind für dich vorgemerkt.' ?></p>
    </section>

    <?php if ($savedPosts === []): ?>
      <section class="empty-state" aria-labelledby="saved-empty-title">
        <h2 id="saved-empty-title">Noch nichts gemerkt</h2>
        <p>Beiträge, die du in Explore mit „Merken“ auswählst, findest du hier wieder.</p>
        <a href="platform.php">Explore öffnen</a>
      </section>
    <?php else: ?>
      <section class="saved-feed" aria-label="Deine gemerkten Beiträge">
        <?php foreach ($savedPosts as $post): ?>
          <?php
          $postId = (int) $post['id'];
          $likeCount = (int) ($likeCountsByPost[$postId] ?? 0);
          $hasLiked = !empty($likedByViewer[$postId]);
          $hasSaved = true;
          $comments = $commentsByPost[$postId] ?? [];
          $commentCount = count($comments);
          $unlocked = hasAccess($post, $userId);
          $cardClass = $unlocked ? '' : ' locked';
          $priceLabel = isset($post['price_cents']) ? formatEuroCents($post['price_cents']) : '';
          $raw = (string) ($post['content'] ?? '');
          $paras = parse_paragraphs($raw);
          $headerTag = 'header';
          $imageMode = 'lenient';
          $wrapRawContent = false;
          $commentEmptyText = 'Noch keine Kommentare – starte das Gespräch.';
          $showCommentReports = true;
          $searchHighlightTerms = [];
          $searchRelatedOnly = false;
          require __DIR__ . '/app/views/partials/platform-post-card.php';
          ?>
        <?php endforeach; ?>
      </section>

      <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Seitennavigation für gemerkte Beiträge">
          <?php if ($page > 1): ?><a href="gemerkt.php?page=<?= $page - 1 ?>">Zurück</a><?php endif; ?>
          <span>Seite <?= (int) $page ?> von <?= (int) $totalPages ?></span>
          <?php if ($page < $totalPages): ?><a href="gemerkt.php?page=<?= $page + 1 ?>">Weiter</a><?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </main>
  <?php require __DIR__ . '/app/views/partials/bottom-nav.php'; ?>
  <script src="js/post-actions.js?v=20260831"></script>
</body>
</html>
