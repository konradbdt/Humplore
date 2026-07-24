<?php
$catName = (string) $item['name'];
$catIcon = (string) ($item['icon'] ?? '#');
$activeCategories = isset($activeCategories) && is_array($activeCategories) ? $activeCategories : [];
$activeCategory = (string) ($activeCategories[0] ?? ($activeCategory ?? ''));
$isActive = $activeCategories !== []
  ? in_array(txt_lower($catName), array_map('txt_lower', $activeCategories), true)
  : ($currentQ !== '' && txt_pos($currentQ, txt_lower($catName)) !== false);
$catHref = '#';
$isDisabled = true;
if (isset($categoryFilterPageState) && is_array($categoryFilterPageState) && function_exists('humplore_platform_url')) {
  $nextCategories = function_exists('humplore_platform_values_toggle')
    ? humplore_platform_values_toggle($activeCategories, $catName)
    : [$catName];
  $catHref = humplore_platform_url($categoryFilterPageState, ['cat' => $nextCategories, 'page' => null]);
  $isDisabled = false;
}
?>
<a class="cat-item <?= $isActive ? 'is-active' : '' ?>" href="<?= e($catHref) ?>" <?= $isDisabled ? 'onclick="return false;" aria-disabled="true"' : '' ?>>
  <span class="cat-left">
    <span class="cat-icon" aria-hidden="true"><?= e($catIcon) ?></span>
    <span class="cat-name"><?= e($catName) ?></span>
  </span>
  <span class="cat-go" aria-hidden="true"><?= isset($item['post_count']) ? (int) $item['post_count'] : '&rarr;' ?></span>
</a>
