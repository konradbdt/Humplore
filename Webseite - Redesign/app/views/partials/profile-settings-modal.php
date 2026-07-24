<div class="modal-overlay" id="overlay" aria-hidden="true"></div>

<div class="modal" id="settingsModal" role="dialog" aria-modal="true" aria-labelledby="settingsTitle">
  <div class="modal__header">
    <div class="modal__title" id="settingsTitle"> Profil bearbeiten</div>
    <button type="button" class="modal__close" id="modalCloseBtn" aria-label="Schlie&szlig;en">
      <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor">
        <path
          d="M18.3 5.7a1 1 0 0 0-1.4-1.4L12 9.17 7.1 4.3A1 1 0 1 0 5.7 5.7L10.6 10.6 5.7 15.5a1 1 0 1 0 1.4 1.4L12 12.03l4.9 4.87a1 1 0 0 0 1.4-1.42l-4.88-4.88 4.88-4.9Z" />
      </svg>
    </button>
  </div>

  <div class="modal__body">
    <form method="post" action="profile.php?user_id=<?= (int) $profile_user_id ?>" enctype="multipart/form-data"
      id="profileForm">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

      <div class="settings-grid">
        <div class="avatar-card">
          <div class="avatar-preview" id="avatarPreview">
            <?php if (!empty($user['has_profile_image'])): ?>
              <img src="<?= htmlspecialchars(profile_img_src((int) $profile_user_id), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilvorschau">
            <?php else: ?>
              <div class="avatar-initials" style="font-size:2.2rem;">
                <?= strtoupper(substr($user['username'], 0, 1)) ?>
              </div>
            <?php endif; ?>
          </div>

          <label for="imageUpload">Profilbild</label>
          <input type="file" id="imageUpload" name="profile_image" accept="image/*" style="width:100%;">

          <div class="dropzone" id="dropzone">Bild hierher ziehen oder klicken</div>
          <div class="upload-hint">Unterst&uuml;tzt: JPG/PNG/WebP/GIF &middot; max. 5&nbsp;MB</div>
        </div>

        <div>
          <div class="form-field">
            <label for="bioInput">Bio</label>
            <textarea id="bioInput" name="bio" rows="6" maxlength="300"
              placeholder="Erz&auml;hl etwas &uuml;ber dich..."><?= htmlspecialchars($profileBio) ?></textarea>
            <div class="bio-meta">
              <span>Max. 300 Zeichen</span>
              <span id="bioCount">0/300</span>
            </div>
            <div class="bio-progress"><span id="bioProgressBar"></span></div>
          </div>

          <!-- Weitere Felder koennen spaeter folgen. -->
        </div>
      </div>

      <div class="modal__footer">
        <button type="button" class="close-btn" id="modalCloseBtn2">Schlie&szlig;en</button>
        <button type="submit" name="save_profile" class="save-btn">Speichern</button>
      </div>
    </form>
  </div>
</div>
