<?php
$active = $active ?? '';
$navPdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : null;
$routes = humplore_nav_routes($navPdo);
$showCreatorItems = humplore_show_creator_nav($navPdo);
?>
<nav class="bottom-nav" aria-label="Hauptnavigation">
  <a href="<?= e($routes['explore']) ?>" class="<?= humplore_nav_active_class($active, 'explore') ?>">Explore</a>

  <a href="<?= e($routes['saved']) ?>" class="<?= humplore_nav_active_class($active, 'saved') ?>">Gemerkt</a>

  <?php if ($showCreatorItems): ?>
    <a href="<?= e($routes['post']) ?>" class="<?= humplore_nav_active_class($active, 'post') ?>">+</a>
  <?php endif; ?>

  <a href="<?= e($routes['news']) ?>" class="<?= humplore_nav_active_class($active, 'news') ?>">News</a>

  <?php if ($showCreatorItems): ?>
    <a href="<?= e($routes['profile']) ?>" class="<?= humplore_nav_active_class($active, 'profile') ?>">Profil</a>
  <?php endif; ?>
</nav>
