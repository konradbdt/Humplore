# Implementation Plan: Question and Comment Reporting

## Technical Approach

- Add the `Reports` table to documented SQL and defensive runtime schema creation.
- Add shared report helpers for reasons, validation, note normalization, and bulk reported-state lookup.
- Add `report_handler.php` as a dedicated JSON POST handler.
- Render object-level report controls in question and comment partials.
- Add page-local Fetch behavior in `platform.php` and `profile.php`.
- Keep the report UI outside post action rows.

## Files

- `Webseite - Codex/create_tables.sql`
- `Webseite - Codex/app/bootstrap.php`
- `Webseite - Codex/app/support/helpers.php`
- `Webseite - Codex/app/support/reports.php`
- `Webseite - Codex/report_handler.php`
- `Webseite - Codex/platform.php`
- `Webseite - Codex/profile.php`
- `Webseite - Codex/app/views/partials/profile-questions-card.php`
- `Webseite - Codex/app/views/partials/platform-post-card.php`
- `Webseite - Codex/app/views/partials/profile-post-card.php`
- `Webseite - Codex/css/styles.css`

## Verification

- PHP syntax checks on changed PHP files.
- Focused handler smoke checks for authenticated question/comment reports and duplicates.
- Render smoke checks for visible content and unchanged adjacent actions.
