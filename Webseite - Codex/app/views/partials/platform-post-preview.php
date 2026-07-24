<?php
$previewLimit = isset($previewLimit) ? (int) $previewLimit : 160;
$previewChars = 0;
$previewParagraphs = [];
$remainingParagraphs = [];

foreach ($paragraphs as $paragraph) {
    $paragraphLength = mb_strlen($paragraph);
    if ($previewChars < $previewLimit) {
        $previewParagraphs[] = $paragraph;
        $previewChars += $paragraphLength + 1;
        continue;
    }

    $remainingParagraphs[] = $paragraph;
}

$hasMorePreview = $remainingParagraphs !== [];
?>
<div class="post-content">
  <?php foreach ($previewParagraphs as $paragraph): ?>
    <p><?= e($paragraph) ?></p>
  <?php endforeach; ?>

  <?php if ($hasMorePreview): ?>
    <div class="more-content" id="more-<?= (int) $postId ?>" style="display:none">
      <?php foreach ($remainingParagraphs as $paragraph): ?>
        <p><?= e($paragraph) ?></p>
      <?php endforeach; ?>
    </div>

    <div class="post-readmore" id="more-row-<?= (int) $postId ?>">
      ... <a href="#" class="more-link" onclick="toggleMore('<?= (int) $postId ?>', event)">mehr lesen</a>
    </div>

    <div class="post-readless" id="less-row-<?= (int) $postId ?>" style="display:none">
      <a href="#" class="more-link" onclick="toggleMore('<?= (int) $postId ?>', event)">weniger lesen</a>
    </div>
  <?php endif; ?>
</div>
