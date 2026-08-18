<?php
$headerTag = $headerTag ?? 'div';
$imageMode = $imageMode ?? 'lenient';
$wrapRawContent = $wrapRawContent ?? false;
$commentEmptyText = $commentEmptyText ?? 'Noch keine Kommentare';
$viewerInitial = $viewerInitial ?? '';
$postId = (int) ($post['id'] ?? 0);
$profileHref = 'profile.php?user_id=' . (int) ($post['creator_id'] ?? 0);
$showInlineImage = !empty($post['media_image']) && ($imageMode === 'lenient' || (($post['media_type'] ?? '') === 'image'));
$showRemoteImage = !$showInlineImage && !empty($post['media_url']) && ($imageMode === 'lenient' || (($post['media_type'] ?? '') === 'image'));
$shouldRenderContent = !$wrapRawContent || $raw !== '';
$isQuestionPost = !empty($post['source_question_id']);
$cardClassNames = trim('post-card ' . trim((string) ($cardClass ?? '')) . ($isQuestionPost ? ' question-post' : ''));
$hasSaved = !empty($hasSaved);
$viewerUserId = (int) ($viewerUserId ?? $userId ?? 0);
?>
<article class="<?= e($cardClassNames) ?>" id="post-<?= $postId ?>">
  <div class="post-inner">
    <<?= $headerTag ?> class="post-header">
      <a class="post-avatar" href="<?= e($profileHref) ?>" aria-label="Zum Profil">
        <?php if (!empty($post['profile_image']) && $post['profile_image'] !== 'default_profile.png'): ?>
          <img src="data:image/jpeg;base64,<?= base64_encode($post['profile_image']) ?>" loading="lazy" decoding="async"
            alt="Profilbild von @<?= e($post['username']) ?>">
        <?php else: ?>
          <?= strtoupper(substr((string) $post['username'], 0, 1)) ?>
        <?php endif; ?>
      </a>
      <div class="post-meta">
        <a class="author" href="<?= e($profileHref) ?>">@<?= e($post['username']) ?></a>

        <?php if (!empty($post['creator_main_topic']) || !empty($post['cat_list']) || !empty($post['category'])): ?>
          <?php if (!empty($post['creator_main_topic'])): ?>
            <span class="topic-pill"><?= e($post['creator_main_topic']) ?></span>
          <?php endif; ?>

          <?php if (!empty($post['cat_list']) || !empty($post['category'])): ?>
            <span class="cat-pill"><?= e($post['cat_list'] ?: $post['category']) ?></span>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </<?= $headerTag ?>>

    <?php if (!empty($post['title'])): ?>
      <h3 class="post-title"><?= e($post['title']) ?></h3>
    <?php endif; ?>

    <?php if (!$unlocked && (int) ($post['is_paid'] ?? 0) === 1): ?>
      <div class="lock-banner" role="note" aria-label="Beitrag ist kostenpflichtig">
        <svg class="lock-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="currentColor"
            d="M12 2a5 5 0 00-5 5v3H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 8V7a3 3 0 116 0v3H9z" />
        </svg>
        <span>Gesperrter Inhalt</span>
        <?php if ($priceLabel): ?>
          <span class="lock-price"><?= e($priceLabel) ?></span>
        <?php endif; ?>
        <button type="button" class="lock-btn" title="Freischalten (bald verfügbar)" disabled>Freischalten</button>
      </div>
    <?php endif; ?>

    <?php if ($showInlineImage): ?>
      <div class="post-image"><img src="<?= e(post_img_src($postId)) ?>" loading="lazy" decoding="async" alt="Beitragsbild"></div>
    <?php elseif ($showRemoteImage): ?>
      <div class="post-image"><img src="<?= e($post['media_url']) ?>" loading="lazy" decoding="async" alt="Beitragsbild"></div>
    <?php endif; ?>

    <?php if (!empty($post['cat_list']) || !empty($post['category']) || !empty($post['creator_main_topic'])): ?>
      <div class="post-catline">
        <?php if (!empty($post['cat_list']) || !empty($post['category'])): ?>
          Kategorie: <?= e($post['cat_list'] ?: $post['category']) ?>
        <?php endif; ?>
        <?php if (!empty($post['creator_main_topic'])): ?>
          <?= (!empty($post['cat_list']) || !empty($post['category'])) ? ' · ' : '' ?>
          Thema: <?= e($post['creator_main_topic']) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($shouldRenderContent && !empty($paras)): ?>
      <?php
      $paragraphs = $paras;
      $previewLimit = 160;
      require __DIR__ . '/platform-post-preview.php';
      ?>
    <?php endif; ?>

    <div class="post-actions">
      <button type="button" class="action-button like-button <?= $hasLiked ? 'liked' : '' ?>" data-post-id="<?= $postId ?>" onclick="toggleLike(this)"
        aria-label="<?= $hasLiked ? 'Neues gelernt! entfernen' : 'Neues gelernt! markieren' ?>" aria-pressed="<?= $hasLiked ? 'true' : 'false' ?>">
        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path
            d="M9 21h6v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.86-3.14-7-7-7zm2 11.7V16h-4v-2.3l-.85-.6A4.98 4.98 0 0 1 7 9a5 5 0 0 1 10 0 4.98 4.98 0 0 1-2.15 4.1l-.85.6z" />
        </svg>
        <span class="action-count like-count"><?= (int) $likeCount ?></span><span class="action-label">Neues gelernt!</span>
      </button>
      <button type="button" class="action-button comments-button" onclick="toggleComments(<?= $postId ?>, this)" aria-label="Kommentieren">
        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
        </svg>
        <span class="action-count comment-count"><?= (int) $commentCount ?></span><span class="action-label">Kommentieren</span>
      </button>
      <?php if ($viewerUserId > 0): ?>
        <button type="button" class="action-button save-post-button <?= $hasSaved ? 'saved' : '' ?>" data-post-id="<?= $postId ?>"
          onclick="toggleSavedPost(this)" aria-label="<?= $hasSaved ? 'Beitrag nicht mehr merken' : 'Beitrag merken' ?>" aria-pressed="<?= $hasSaved ? 'true' : 'false' ?>">
          <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17 3H7a2 2 0 00-2 2v16l7-4 7 4V5a2 2 0 00-2-2zm0 14.55l-5-2.86-5 2.86V5h10v12.55z" />
          </svg>
          <span class="action-label">Merken</span>
        </button>
      <?php endif; ?>
      <button type="button" class="action-button share-button" onclick="sharePost(<?= $postId ?>)" aria-label="Teilen">
        <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path
            d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92z" />
        </svg>
        <span class="action-label">Teilen</span>
      </button>
    </div>

    <div class="comments-section" id="comments-<?= $postId ?>">
      <?php if ($commentCount === 0): ?>
        <div class="comments-empty"><?= e($commentEmptyText) ?></div>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <?php $commentId = (int) ($comment['id'] ?? 0); ?>
          <div class="comment">
            <div class="comment-avatar"><?= strtoupper(substr((string) $comment['username'], 0, 1)) ?></div>
            <div class="comment-bubble">
              <div class="comment-header"><span class="comment-user">@<?= e($comment['username']) ?></span><span class="comment-time"><?= e(date("d.m.Y H:i", strtotime($comment['created_at']))) ?></span></div>
              <div class="comment-text"><?= nl2br(e($comment['comment_text'])) ?></div>
              <?php
              $reportTargetType = 'comment';
              $reportTargetId = $commentId;
              $reportIsReported = !empty($comment['is_reported']);
              require __DIR__ . '/report-control.php';
              ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <form method="post" class="comment-form">
        <div class="comment-avatar"><?= e($viewerInitial) ?></div>
        <div class="comment-input">
          <input type="hidden" name="post_id" value="<?= $postId ?>">
          <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
          <textarea name="comment_text" placeholder="Schreibe einen freundlichen Kommentar …" required></textarea>
          <div class="comment-actions"><button type="submit" class="btn-send">Senden</button></div>
        </div>
      </form>
    </div>
  </div>
</article>
