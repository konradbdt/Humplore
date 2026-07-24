<?php
$reportTargetType = (string) ($reportTargetType ?? '');
$reportTargetId = (int) ($reportTargetId ?? 0);
$reportIsReported = !empty($reportIsReported);
$reportReasons = humplore_report_reasons();
$reportButtonLabel = $reportIsReported ? 'Gemeldet' : 'Melden';
?>
<?php if ($reportTargetId > 0 && humplore_report_target_type_is_valid($reportTargetType)): ?>
  <div
    class="report-control<?= $reportIsReported ? ' is-reported' : '' ?>"
    data-report-control
    data-report-target-type="<?= htmlspecialchars($reportTargetType, ENT_QUOTES, 'UTF-8') ?>"
    data-report-target-id="<?= $reportTargetId ?>">
    <button
      type="button"
      class="report-toggle"
      data-report-toggle
      <?= $reportIsReported ? 'disabled aria-disabled="true"' : '' ?>>
      <?= htmlspecialchars($reportButtonLabel, ENT_QUOTES, 'UTF-8') ?>
    </button>
    <form class="report-form" data-report-form hidden>
      <input type="hidden" name="target_type" value="<?= htmlspecialchars($reportTargetType, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="target_id" value="<?= $reportTargetId ?>">
      <label class="report-label">
        Grund
        <select name="reason" required>
          <?php foreach ($reportReasons as $reasonValue => $reasonLabel): ?>
            <option value="<?= htmlspecialchars($reasonValue, ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($reasonLabel, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="report-label">
        Hinweis optional
        <textarea name="note" maxlength="500" rows="2"></textarea>
      </label>
      <div class="report-actions">
        <button type="submit" class="report-submit">Senden</button>
        <button type="button" class="report-cancel" data-report-cancel>Abbrechen</button>
      </div>
    </form>
    <div class="report-message" data-report-message <?= $reportIsReported ? '' : 'hidden' ?>>
      Danke, wir haben deine Meldung erfasst.
    </div>
  </div>
<?php endif; ?>
