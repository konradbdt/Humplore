<?php
$postId = (int) ($post['id'] ?? 0);
$postCardClass = $postCardClass ?? '';
$profileOwner = $profileOwner ?? [];
$profileOwnerId = (int) ($profileOwnerId ?? 0);
$canDelete = !empty($canDelete);
$comments = $comments ?? [];
$commentCount = isset($commentCount) ? (int) $commentCount : count($comments);
$likeCount = (int) ($likeCount ?? 0);
$hasLiked = !empty($hasLiked);
$hasSaved = !empty($hasSaved);
$priceLabel = (string) ($priceLabel ?? '');
$csrfToken = (string) ($csrfToken ?? '');
$viewerUserId = (int) ($viewerUserId ?? 0);
$commentAvatarInitial = (string) ($commentAvatarInitial ?? '');
$commentsEmptyText = $commentsEmptyText ?? 'Noch keine Kommentare - sei die/der Erste';
$previewLimit = (int) ($previewLimit ?? 220);
$isQuestionPost = !empty($post['source_question_id']);
$postCardClassNames = trim('post-card ' . trim((string) $postCardClass) . ($isQuestionPost ? ' question-post' : ''));

$raw = (string) ($post['content'] ?? '');
$raw = str_replace(["\r\n", "\r"], "\n", $raw);
$raw = (string) preg_replace("/[ \t]+$/m", '', $raw);
$raw = (string) preg_replace("/\n{3,}/", "\n\n", trim($raw));
[$previewText, $remainingText] = smart_split($raw, $previewLimit);
$hasParagraphs = (preg_match("/\n\s*\n/", $raw) === 1);
$hasMoreContent = txt_len($raw) > $previewLimit;

$renderParagraphs = static function (string $text): void {
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $blocks = preg_split("/\n\s*\n/", $text) ?: [];

    foreach ($blocks as $block) {
        $block = trim((string) preg_replace("/\n+/", ' ', $block));
        if ($block === '') {
            continue;
        }

        echo '<p>' . htmlspecialchars($block, ENT_QUOTES, 'UTF-8') . '</p>';
    }
};
?>
<div class="<?= htmlspecialchars($postCardClassNames, ENT_QUOTES, 'UTF-8') ?>" id="post-<?= $postId ?>">
  <div class="post-header">
    <div class="post-header-img">
      <?php if (!empty($profileOwner['has_profile_image'])): ?>
        <img src="<?= htmlspecialchars(profile_img_src($profileOwnerId), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilbild">
      <?php else: ?>
        <div class="profile-initials"><?= strtoupper(substr((string) ($profileOwner['username'] ?? ''), 0, 1)) ?></div>
      <?php endif; ?>
    </div>

    <div class="post-header-info">
      <span class="post-author">@<?= htmlspecialchars((string) ($post['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
      <span class="post-date"><?= date("d.m.Y H:i", strtotime((string) ($post['created_at'] ?? 'now'))) ?></span>
    </div>

    <?php if ($canDelete): ?>
      <div class="post-menu">
        <button class="menu-trigger" type="button" aria-haspopup="true" aria-expanded="false"
          aria-controls="menu-<?= $postId ?>"
          onclick="togglePostMenu(event,'<?= $postId ?>')" title="MenÃƒÆ’Ã‚Â¼">
          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="5" cy="12" r="2"></circle>
            <circle cx="12" cy="12" r="2"></circle>
            <circle cx="19" cy="12" r="2"></circle>
          </svg>
        </button>

        <div class="menu-dropdown" id="menu-<?= $postId ?>" role="menu">
          <form method="post" onsubmit="return confirmDelete(<?= $postId ?>)" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="delete_post" value="1">
            <input type="hidden" name="delete_post_id" value="<?= $postId ?>">
            <button type="submit" class="menu-item danger" role="menuitem">
              <span>Löschen</span>
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($post['cat_list'])): ?>
    <div class="post-catline">
      <?php
      $postCats = preg_split('/\s+[ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢Ãƒâ€šÃ‚Â·|]\s+/u', (string) $post['cat_list']) ?: [(string) $post['cat_list']];
      foreach ($postCats as $name):
          $name = trim($name);
          if ($name === '') {
              continue;
          }
      ?>
        <span class="cat-pill"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h3 class="post-title"><?= htmlspecialchars((string) ($post['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>

  <?php if (!post_has_access($post, $viewerUserId) && (int) ($post['is_paid'] ?? 0) === 1): ?>
    <div class="lock-banner" role="note" aria-label="Beitrag ist kostenpflichtig">
      <svg class="lock-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="currentColor"
          d="M12 2a5 5 0 00-5 5v3H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5zm-3 8V7a3 3 0 116 0v3H9z" />
      </svg>
      <span>Gesperrter Inhalt</span>
      <?php if ($priceLabel !== ''): ?>
        <span class="lock-price"><?= htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8') ?></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="post-content-wrapper">
    <?php if (!empty($post['has_media_image'])): ?>
      <div class="post-image">
        <img src="<?= htmlspecialchars(post_img_src($postId), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Beitragsbild">
      </div>
    <?php elseif (!empty($post['media_url'])): ?>
      <div class="post-image">
        <img src="<?= htmlspecialchars((string) $post['media_url'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Beitragsbild">
      </div>
    <?php endif; ?>

    <div class="post-content" id="post-content-<?= $postId ?>">
      <?php if (!$hasParagraphs): ?>
        <p>
          <?= htmlspecialchars((string) preg_replace("/[\r\n]+/", ' ', $previewText), ENT_QUOTES, 'UTF-8') ?>
          <?php if ($hasMoreContent): ?>
            <span class="more-content" id="more-<?= $postId ?>" style="display:none">
              <?= htmlspecialchars((string) preg_replace("/[\r\n]+/", ' ', $remainingText), ENT_QUOTES, 'UTF-8') ?>
            </span>
          <?php endif; ?>
        </p>
      <?php else: ?>
        <?php $renderParagraphs($previewText); ?>
        <?php if ($hasMoreContent): ?>
          <div class="more-content" id="more-<?= $postId ?>" style="display:none">
            <?php $renderParagraphs($remainingText); ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($hasMoreContent): ?>
        <div class="post-readmore" id="more-row-<?= $postId ?>">
          ... <a href="#" id="more-link-<?= $postId ?>" class="more-link" onclick="toggleMore('<?= $postId ?>', event)">mehr lesen</a>
        </div>
        <div class="post-readless" id="less-row-<?= $postId ?>" style="display:none">
          <a href="#" id="less-link-<?= $postId ?>" class="more-link" onclick="toggleMore('<?= $postId ?>', event)">weniger anzeigen</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="post-actions">
    <button type="button" class="action-button like-button <?= $hasLiked ? 'liked' : '' ?>"
      data-post-id="<?= $postId ?>" onclick="toggleLike(this)"
      aria-label="<?= $hasLiked ? 'Neues gelernt! entfernen' : 'Neues gelernt! markieren' ?>" aria-pressed="<?= $hasLiked ? 'true' : 'false' ?>">
      <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path
          d="M9 21h6v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74 0-3.86-3.14-7-7-7zm2 11.7V16h-4v-2.3l-.85-.6A4.98 4.98 0 0 1 7 9a5 5 0 0 1 10 0 4.98 4.98 0 0 1-2.15 4.1l-.85.6z" />
      </svg>
      <span class="action-count like-count"><?= $likeCount ?></span>
      <span class="action-label">Neues gelernt!</span>
    </button>

    <button type="button" class="action-button comments-button" onclick="toggleComments(<?= $postId ?>, this)" aria-label="Kommentieren">
      <svg class="action-icon" viewBox="0 0 24 24" aria-hidden="true">
        <path d="M21.99 4c0-1.1-.89-2-1.99-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14l4 4-.01-18z" />
      </svg>
      <span class="action-count comment-count"><?= $commentCount ?></span>
      <span class="action-label">Kommentieren</span>
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
          d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92z" />
      </svg>
      <span class="action-label">Teilen</span>
    </button>
  </div>

  <div class="comments-section" id="comments-<?= $postId ?>">
    <?php if ($comments === []): ?>
      <div class="comments-empty"><?= htmlspecialchars($commentsEmptyText, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
      <?php foreach ($comments as $comment): ?>
        <?php $commentId = (int) ($comment['id'] ?? 0); ?>
        <div class="comment">
          <div class="comment-avatar"><?= strtoupper(substr((string) ($comment['username'] ?? ''), 0, 1)) ?></div>
          <div class="comment-bubble">
            <div class="comment-header">
              <span class="comment-user">@<?= htmlspecialchars((string) ($comment['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
              <span class="comment-time"><?= date("d.m.Y H:i", strtotime((string) ($comment['created_at'] ?? 'now'))) ?></span>
            </div>
            <div class="comment-text"><?= nl2br(htmlspecialchars((string) ($comment['comment_text'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
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

    <?php if ($viewerUserId > 0): ?>
      <form method="post" class="comment-form">
        <div class="me-avatar"><?= htmlspecialchars($commentAvatarInitial, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="comment-input">
          <input type="hidden" name="post_id" value="<?= $postId ?>">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
          <textarea name="comment_text" placeholder="Schreibe einen freundlichen Kommentar ..."
            required></textarea>
          <div class="comment-actions">
            <button type="submit" class="btn-send">Senden</button>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
