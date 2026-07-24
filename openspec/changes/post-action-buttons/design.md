# Design: Post Action Buttons

## Current State

Post cards currently render a compact action row with learning reaction, comment, and share actions. The learning reaction is labeled `Wissenswert`, comments are presented as a comment action/surface, and share uses the existing share handler.

## Target State

The post action row renders the following first-iteration actions in order:

1. `Neues gelernt!` with lightbulb icon
2. `Kommentieren` with speech bubble icon
3. `Merken` with bookmark/lesezeichen icon
4. `Teilen` with molecule-style icon

Donation is a documented later post-level action and is not shown until donation behavior is defined.

## Technical Approach

### Reaction Rename

Keep existing reaction storage, toggle behavior, paid/locked post behavior, and public count. Update only the visible label, accessible label, and any UI copy from `Wissenswert` to `Neues gelernt!`.

### Comment Action

Change the visible label from `Kommentar` to `Kommentieren`. Use the existing comment area as the implementation target. The action should keep the existing comment count and focus or open that surface. Posts do not get a separate question mode in this change.

### Merken Persistence

`Merken` should use a bookmark/lesezeichen icon and real persistence, not a placeholder. Add or reuse a viewer/post relationship table with a unique pair constraint. The table name for this change is `SavedPosts` unless an existing compatible table is already present. The state is private to the current viewer, does not expose public counts, and is only part of the logged-in post-card experience.

Schema if no existing compatible table is present:

```sql
CREATE TABLE IF NOT EXISTS SavedPosts (
  user_id INTEGER NOT NULL,
  post_id INTEGER NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id)
);
```

Keep `SavedPosts` minimal in this change: no collection, folder, source context, note, or ranking fields.

Load remembered states in bulk for all post IDs rendered on a page. Avoid per-card database queries.

Schema handling should follow the project's brownfield pattern: keep the SQL documented and add defensive `CREATE TABLE IF NOT EXISTS` behavior through the existing `humplore_ensure_database_schema()` helper. Runtime schema creation must be additive only.

### Merken Toggle

The toggle handler validates the logged-in viewer and target post. It inserts a remembered row when absent and deletes the remembered row when present. Duplicate remembered records are prevented by the unique pair constraint. The button should call the handler via Ajax/Fetch, matching the existing learning reaction pattern.

Use a dedicated handler named `save_post_handler.php` instead of extending `like_handler.php`. `like_handler.php` remains scoped to public learning reactions/counts; `Merken` is private and countless.

Successful `Merken` toggles should update the bookmark's active/inactive state and should not show a toast. Failure states may use existing error feedback patterns if needed.

When a successful response includes `post_id`, the frontend should update all rendered `Merken` buttons with that `data-post-id`, including duplicated feed/modal instances.

`save_post_handler.php` must validate CSRF for write requests using the existing CSRF helper. The Ajax/Fetch call must send the token with the request.

Unauthenticated requests to `save_post_handler.php` should return JSON with HTTP 401 instead of redirecting to login, because the caller is a Fetch workflow.

Successful responses from `save_post_handler.php` should be minimal JSON: `{ "post_id": 123, "saved": true }` after marking and `{ "post_id": 123, "saved": false }` after unmarking. Including `post_id` lets the UI synchronize multiple rendered instances of the same post. The handler should not return HTML fragments or public counts.

### Share

Keep existing share behavior and molecule-style icon. The row composition change must not alter link generation, Web Share API use, or copy-link fallback behavior.

## Files Likely Affected

- `Webseite - Codex/app/views/partials/platform-post-card.php`
- `Webseite - Codex/app/views/partials/profile-post-card.php`
- `Webseite - Codex/app/support/content.php` or another shared support helper for bulk save state
- A save toggle route/handler if no suitable existing action handler exists
- Page-local or shared CSS for `.post-actions` and `.action-button`

## UX Constraints

- Four actions must fit on mobile as icon-only controls when labels would crowd the row.
- Mobile icon-only controls must keep accessible labels.
- Mobile icon-only layout keeps counts visible for `Neues gelernt!` and `Kommentieren`.
- Action order must stay stable across post-card contexts.
- Explore, search results, profile post cards, and post modal contexts must use the same action set and order when actions are rendered.
- Saved and learned selected states must be visually distinguishable enough.
- No disabled Donation placeholder should be shown.

## Resolved Product Decision

Remembered posts will not get a dedicated library page in this change. This change only requires marking/unmarking with `Merken` and persistent state; a remembered-post library page or `Gemerkt` view will be specified later as its own change.

Post responses remain comments only. A separate question flow for posts is out of scope and would need its own change because it affects data model, notifications, and presentation.

`Merken` remains private. Public counts or ranking based on remembered posts are out of scope for this change.

`Merken` is scoped to logged-in users. Logged-out post-card behavior and login prompts are out of scope.

Later Donation is intended to be per post, not only per creator. Payment, payout, eligibility, moderation, and refund behavior remain out of scope for this change.

The action row should remain identical across Explore, search results, profile post cards, and modal contexts where post actions appear. If a modal currently reuses a card partial, preserve that reuse; if it has separate markup, update it to match.

`Merken` should use Ajax/Fetch rather than a full form reload because the existing learning reaction in the same row already uses `fetch('like_handler.php')`.

Mobile action rows should use icons only while keeping counts visible for `Neues gelernt!` and `Kommentieren`. `Merken` and `Teilen` remain countless on mobile. Desktop/tablet can keep visible labels where the layout supports them.

`Merken` can be used on visible paid/locked post cards. This must not unlock content or bypass existing access checks.
