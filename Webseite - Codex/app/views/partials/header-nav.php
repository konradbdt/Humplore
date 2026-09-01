<?php
$active = $active ?? '';
$navPdo = (isset($pdo) && $pdo instanceof PDO) ? $pdo : null;
$routes = humplore_nav_routes($navPdo);
?>
<header>
  <a href="platform.php" class="brand" aria-label="Humplore - Startseite">
    <img src="/pic/humplore-logo.png" alt="humplore Logo">
  </a>
</header>

<nav class="bottom-nav" aria-label="Hauptnavigation">
  <a href="<?= e($routes['explore']) ?>" class="<?= humplore_nav_active_class($active, 'explore') ?>">Explore</a>
  <a href="<?= e($routes['saved']) ?>" class="<?= humplore_nav_active_class($active, 'saved') ?>">Gemerkt</a>
  <a href="<?= e($routes['post']) ?>" class="<?= humplore_nav_active_class($active, 'post') ?>">+</a>
  <a href="<?= e($routes['news']) ?>" class="<?= humplore_nav_active_class($active, 'news') ?>">News</a>
  <a href="<?= e($routes['profile']) ?>" class="<?= humplore_nav_active_class($active, 'profile') ?>">Profil</a>
</nav>
