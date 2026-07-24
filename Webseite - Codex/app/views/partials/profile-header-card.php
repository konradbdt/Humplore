<div class="profile-card-top">
  <div class="header-flex-one">
    <div class="header-flex-two-left">
      <?php if ($isCreator): ?>
        <div class="profile-header">
          <div class="profile-content-grid">
            <div class="profile-left-stack">
              <div class="profile-identity">
                <div class="profile-avatar-stack">
                  <div class="arround-profile-img-wrapper">
                    <div class="profile-img-wrapper">
                      <?php if (!empty($user['has_profile_image'])): ?>
                        <img src="<?= htmlspecialchars(profile_img_src((int) $profile_user_id), ENT_QUOTES, 'UTF-8') ?>" loading="lazy" decoding="async" alt="Profilbild">
                      <?php else: ?>
                        <div class="profile-initials"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                  <?php if ($viewerUserId > 0 && !$is_own_profile): ?>
                    <form method="post" class="follow-form profile-avatar-follow-form">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                      <?php if ($isFollowing): ?>
                        <button type="submit" name="follow_action" value="unfollow" class="follow-btn is-active" aria-label="Entfolgen">
                          <span>Entfolgen</span>
                        </button>
                      <?php else: ?>
                        <button type="submit" name="follow_action" value="follow" class="follow-btn" aria-label="Folgen">
                          <span>Folgen</span>
                        </button>
                      <?php endif; ?>
                    </form>
                  <?php endif; ?>
                </div>
                <div class="profile-primary">
                  <h3 class="profile-topic-label">Thema:</h3>
                  <h2 class="profile-title"><?= htmlspecialchars($profileTitle) ?></h2>
                  <p class="profile-username"><?= htmlspecialchars($profileUsername) ?></p>
                  <div class="profile-meta-text profile-identity-meta profile-primary-meta">
                    <p><strong>Ort:</strong> <?= htmlspecialchars($profileLocation) ?></p>
                    <p><strong>Sprache:</strong> <?= htmlspecialchars($profileLanguages) ?></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="profile-info">
              <p class="profile-tagline"><?= htmlspecialchars($profileTagline) ?></p>

              <div class="profile-meta">
                <p class="bio"><?= htmlspecialchars($profileBio) ?></p>
                <?php if ($is_own_profile): ?>
                  <div class="profile-bio-actions">
                    <button type="button" class="edit-profile-btn" onclick="openModal()" aria-haspopup="dialog"
                      aria-controls="settingsModal" aria-label="Profil bearbeiten">
                      <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                        <path fill="currentColor"
                          d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm8.94-2.88-1.7-.98c.08-.5.08-1.05 0-1.55l1.7-.98a.75.75 0 0 0 .27-1.02l-1.6-2.77a.75.75 0 0 0-.95-.33l-1.9.77a6.9 6.9 0 0 0-1.35-.78l-.29-2.05A.75.75 0 0 0 12.5 1h-3a.75.75 0 0 0-.74.64l-.29 2.05c-.47.2-.92.46-1.35.78l-1.9-.77a.75.75 0 0 0-.95.33L2.38 6.8a.75.75 0 0 0 .27 1.02l1.7.98c-.08.5-.08 1.05 0 1.55l-1.7.98a.75.75 0 0 0-.27 1.02l1.6 2.77c.2.35.62.5.98.35l1.9-.77c.43.32.88.58 1.35.78l.29 2.05c.06.37.37.64.74.64h3c.37 0 .68-.27.74-.64l.29-2.05c.47-.2.92-.46 1.35-.78l1.9.77c.36.15.78 0 .98-.35l1.6-2.77a.75.75 0 0 0-.27-1.02Z" />
                      </svg>
                      <span>Profil bearbeiten</span>
                    </button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="header-flex-two-right">
      <div class="stats-panel">
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-left">
                <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.57 3-3.5S17.66 4 16 4s-3 1.57-3 3.5S14.34 11 16 11zm-8 0c1.66 0 3-1.57 3-3.5S9.66 4 8 4 5 5.57 5 7.5 6.34 11 8 11zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45v2h7v-2c0-2.66-5.33-4-8-4z"/></svg></span>
              <div class="stat-text">
                <div class="stat-label">Follower</div>
              </div>
            </div>
            <div class="stat-value"><?= (int) $followerCount ?></div>
          </div>
          <div class="stat-card">
            <div class="stat-left">
              <span class="stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM8 12h8v2H8v-2zm0 4h8v2H8v-2zm6-10 4 4h-4V6z"/></svg></span>
              <div class="stat-text">
                <div class="stat-label">Beitraege</div>
              </div>
            </div>
            <div class="stat-value"><?= (int) $postsCount ?></div>
          </div>
        </div>
        <div class="stats-share">
          <button type="button" class="stats-share-button" data-profile-link="<?= htmlspecialchars($profileLink, ENT_QUOTES, 'UTF-8') ?>" onclick="copyProfileLink(this)">
            Profil teilen
          </button>
          <p id="copyConfirmation" class="copy-confirmation stats-share-confirmation">Link kopiert!</p>
        </div>
      </div>
    </div>
  </div>
</div>
