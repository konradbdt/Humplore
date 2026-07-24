<div class="side-modal-overlay" id="categoriesOverlay" aria-hidden="true"></div>
<div class="side-modal" id="categoriesSidebarModal" role="dialog" aria-modal="true" aria-labelledby="categoriesSidebarTitle">
  <div class="modal__header">
    <div class="modal__title" id="categoriesSidebarTitle">Alle Beitrags-/Lebenskategorien</div>
    <button type="button" class="modal__close" id="categoriesSidebarClose" aria-label="Schlie&szlig;en">
      <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor">
        <path d="M18.3 5.7a1 1 0 0 0-1.4-1.4L12 9.17 7.1 4.3A1 1 0 1 0 5.7 5.7L10.6 10.6 5.7 15.5a1 1 0 1 0 1.4 1.4L12 12.03l4.9 4.87a1 1 0 0 0 1.4-1.42l-4.88-4.88 4.88-4.9Z" />
      </svg>
    </button>
  </div>
  <div class="modal__body">
    <div class="category-modal-list">
      <?php foreach ($sidebarCats as $item): ?>
        <?php require __DIR__ . '/profile-sidebar-category-link.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>
