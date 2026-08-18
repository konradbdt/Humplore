<div class="questions-card<?= $is_own_profile ? ' questions-card--owner' : ' questions-card--visitor' ?>">
  <?php if ($is_own_profile): ?>
    <h3>Fragen an dich</h3>
    <?php if (!empty($answer_error)): ?>
      <div class="flash-err"><?= htmlspecialchars($answer_error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($answer_success)): ?>
      <div class="flash-ok"><?= htmlspecialchars($answer_success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <div class="q-scroll">
      <?php if (empty($questions)): ?>
        <div class="qa-empty">
          <div class="qa-empty-icon" aria-hidden="true">&#10067;</div>
          <div class="qa-empty-title">Noch keine Fragen</div>
          <div class="qa-empty-sub">Sobald dir jemand schreibt, erscheinen die Fragen hier.</div>
        </div>
      <?php else: ?>
        <?php foreach ($questions as $q): ?>
          <?php
          $questionId = (int) ($q['id'] ?? 0);
          $questionText = (string) ($q['question_text'] ?? '');
          $isAnonymous = ((int) ($q['is_anonymous'] ?? 0)) === 1;
          ?>
          <div class="qa-item">
            <div class="meta">
              Von <strong><?= $isAnonymous
                  ? 'Anonym'
                  : '@' . htmlspecialchars((string) ($q['author_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
              &middot; <?= htmlspecialchars(date('d.m.Y H:i', strtotime((string) ($q['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?>
              &middot; <?= (int) ($q['like_count'] ?? 0) ?>
            </div>
            <div class="q">Q: <?= htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($q['answer_text'])): ?>
              <div class="a">A: <?= nl2br(htmlspecialchars((string) $q['answer_text'], ENT_QUOTES, 'UTF-8')) ?></div>
              <?php if (!empty($q['answered_at'])): ?>
                <div class="meta">beantwortet am <?= htmlspecialchars(date('d.m.Y H:i', strtotime((string) $q['answered_at'])), ENT_QUOTES, 'UTF-8') ?></div>
              <?php endif; ?>
            <?php else: ?>
              <div class="qa-mode-chooser" data-question-id="<?= $questionId ?>">
                <button
                  type="button"
                  class="qa-mode-button"
                  data-answer-mode="reply"
                  data-question-id="<?= $questionId ?>">
                  Als Antwort
                </button>
                <button
                  type="button"
                  class="qa-mode-button qa-mode-button--post"
                  data-answer-mode="post"
                  data-question-id="<?= $questionId ?>"
                  data-question-text="<?= htmlspecialchars($questionText, ENT_QUOTES, 'UTF-8') ?>">
                  Als Beitrag
                </button>
              </div>

              <form method="post" class="qa-reply-editor" data-question-id="<?= $questionId ?>" hidden>
                <input type="hidden" name="action" value="answer_question">
                <input type="hidden" name="question_id" value="<?= $questionId ?>">
                <input type="hidden" name="answer_mode" value="reply">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <textarea name="answer_text" rows="4" placeholder="Antwort eingeben ..." required></textarea>
                <div class="qa-inline-actions">
                  <button type="button" class="qa-inline-button qa-inline-button--ghost" data-qa-cancel="<?= $questionId ?>">Zurueck</button>
                  <button type="submit" class="qa-inline-button qa-inline-button--send">Senden</button>
                </div>
              </form>
            <?php endif; ?>
            <?php
            $reportTargetType = 'question';
            $reportTargetId = $questionId;
            $reportIsReported = !empty($q['is_reported']);
            require __DIR__ . '/report-control.php';
            ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="qa-post-modal-overlay" id="qaPostModalOverlay" aria-hidden="true">
      <div class="qa-post-modal" role="dialog" aria-modal="true" aria-labelledby="qaPostModalTitle">
        <div class="qa-post-modal__header">
          <div>
            <div class="qa-post-modal__title" id="qaPostModalTitle">Als Beitrag posten</div>
            <div class="qa-post-modal__sub">Die Frage wird als Titel verwendet. Inhalt und Kategorie legst du hier fest.</div>
          </div>
          <button type="button" class="qa-post-modal__close" id="qaPostModalClose" aria-label="Schliessen">Schliessen</button>
        </div>

        <form method="post" class="qa-post-modal__form">
          <input type="hidden" name="action" value="answer_question">
          <input type="hidden" name="question_id" id="qaPostModalQuestionId" value="">
          <input type="hidden" name="answer_mode" value="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

          <div class="qa-post-modal__question">
            <div class="qa-post-modal__eyebrow">Frage</div>
            <div class="qa-post-modal__question-text" id="qaPostModalQuestionText"></div>
          </div>

          <label for="qaPostModalAnswer">Beitragstext</label>
          <textarea id="qaPostModalAnswer" name="answer_text" rows="7" placeholder="Schreibe deinen Beitrag ..." required></textarea>

          <label for="qaPostModalCategory">Kategorie</label>
          <input
            type="text"
            id="qaPostModalCategory"
            name="post_category"
            maxlength="40"
            placeholder="z. B. Alltag, Familie, Mindset ..."
            required>
          <div class="qa-post-modal__hint">2-40 Zeichen; erlaubt: Buchstaben, Zahlen, Leerzeichen, - _ &amp; /</div>

          <div class="qa-post-modal__actions">
            <button type="button" class="qa-inline-button qa-inline-button--ghost" id="qaPostModalCancel">Abbrechen</button>
            <button type="submit" class="qa-inline-button qa-inline-button--post-submit">Beitrag senden</button>
          </div>
        </form>
      </div>
    </div>
  <?php else: ?>
    <h3 style="color: black;">Frage an <?= htmlspecialchars((string) ($user['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
    <?php if (!empty($ask_error)): ?>
      <div class="flash-err"><?= htmlspecialchars($ask_error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($ask_success)): ?>
      <div class="flash-ok"><?= htmlspecialchars($ask_success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="profile.php?user_id=<?= (int) $profile_user_id ?>">
      <input type="hidden" name="action" value="ask_question">
      <input type="hidden" name="creator_id" value="<?= (int) $profile_user_id ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
      <textarea name="question_text" rows="4" placeholder="Stell deine Frage ..." required></textarea>
      <label class="qa-anonymous-control">
        <input type="checkbox" name="is_anonymous" value="1">
        <span>Anonym fragen</span>
      </label>
      <div class="qa-anonymous-hint">Dein Name wird weder dem Creator noch anderen Nutzern angezeigt.</div>
      <button type="submit">Absenden</button>
    </form>
    <?php
    $answeredPreview = array_values(array_filter($questions, static function ($q) {
        return !empty($q['answer_text']);
    }));
    ?>
    <?php if (!empty($answeredPreview)): ?>
      <?php $previewSlice = array_slice($answeredPreview, 0, 5); ?>
      <div class="q-scroll" style="margin-top:8px;">
        <div style="font-weight:600; margin-bottom:6px;">Kuerzlich beantwortet</div>
        <?php foreach ($previewSlice as $q): ?>
          <div class="qa-item">
            <?php
            $questionId = (int) ($q['id'] ?? 0);
            ?>
            <div class="q">Q: <?= htmlspecialchars((string) ($q['question_text'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="a">A: <?= nl2br(htmlspecialchars((string) ($q['answer_text'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
            <?php
            $reportTargetType = 'question';
            $reportTargetId = $questionId;
            $reportIsReported = !empty($q['is_reported']);
            require __DIR__ . '/report-control.php';
            ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="qa-empty" style="margin-top:10px;">
        <div class="qa-empty-icon" aria-hidden="true">&#128172;</div>
        <div class="qa-empty-title">Noch keine beantworteten Fragen</div>
        <div class="qa-empty-sub">Stell die erste Frage und starte den Austausch.</div>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
