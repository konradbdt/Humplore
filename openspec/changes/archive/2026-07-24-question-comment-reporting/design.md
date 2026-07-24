# Design: Question and Comment Reporting

## Current State

Humplore has separate routes and partials for profile pages, Explore/search post cards, comments, and question displays. Runtime schema changes are handled defensively through `humplore_ensure_database_schema()`. Ajax handlers in the webroot include JSON responses, login checks, CSRF checks, and minimal payloads.

## Data Model

Add the additive `Reports` table:

```sql
CREATE TABLE IF NOT EXISTS Reports (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  reporter_id INTEGER NOT NULL,
  target_type TEXT NOT NULL,
  target_id INTEGER NOT NULL,
  reason TEXT NOT NULL,
  note TEXT NULL,
  status TEXT NOT NULL DEFAULT 'open',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (reporter_id, target_type, target_id)
);
```

The schema intentionally avoids foreign keys because `target_id` references either `Questions.id` or `Comments.id` depending on `target_type`.

## Backend

Add shared report helpers under `app/support` and include them from bootstrap:

- allowed reason map
- target-type validation
- target existence lookup for `Questions` and `Comments`
- note normalization and bounded length
- bulk reported-state lookup keyed by target ID

The dedicated handler `report_handler.php` is POST-only and returns JSON. It validates authentication, CSRF, target type, target ID existence, and reason. It inserts with `INSERT OR IGNORE` so the unique constraint prevents duplicate rows without treating duplicates as UI failures. Successful responses include target identity and `reported: true`.

## UI

Report controls are rendered as a secondary object-level control directly inside question and comment blocks, outside the post action row. The visible initial action is `Melden`. Clicking it reveals a small inline form with reason selection and optional note. After success or known reported state, the button label is `Gemeldet`, the button is disabled, and the inline confirmation reads `Danke, wir haben deine Meldung erfasst.`

Comments rendered in duplicated contexts use the same target data attributes, so JavaScript can synchronize all controls for the same comment or question after one successful response.

## Non-Goals

No content is hidden, removed, collapsed, or re-sorted after reporting. No public count is rendered. No moderation UI, queue, or role model is added.

## Verification

Run syntax checks on changed PHP files. Run focused smoke checks for logged-in reporting of a question and comment, duplicate prevention, content visibility, button state, and unchanged comment/question/post action flows.
