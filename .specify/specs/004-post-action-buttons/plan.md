# Implementation Plan: Post Action Buttons

## Technology Stack

### Frontend
- Existing PHP-rendered post card partials.
- Existing post action CSS classes where possible.
- Existing JavaScript share and reaction handlers where possible.

### Backend
- Existing PHP route/helper structure.
- SQLite-compatible persistence for saved posts.
- Prepared statements for viewer/post save state reads and writes.

## Architecture

### Affected UI Surfaces

- `Webseite - Codex/app/views/partials/platform-post-card.php`
- `Webseite - Codex/app/views/partials/profile-post-card.php`
- Any shared or page-local CSS that styles `.post-actions` and `.action-button`
- Existing reaction/share JavaScript handlers

### Components

#### Post Action Row
- Responsibility: Render the ordered action list under each post.
- Required actions: `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`.
- Required icons: lightbulb, speech bubble, bookmark, molecule-style share.
- Future action: post-level Donation, not rendered until a later change defines behavior.

#### Saved State Loader
- Responsibility: Load saved state for all rendered post IDs in bulk.
- Inputs: current viewer ID, rendered post IDs.
- Output: map of post ID to saved state.

#### Merken Toggle Handler
- Responsibility: Toggle marked/unmarked state for the current viewer and post.
- Validation: viewer must be logged in, post must exist, duplicate saves must be prevented.
- Transport: Ajax/Fetch request from the post action button.
- Location: dedicated handler `save_post_handler.php`, separate from `like_handler.php`.

## Data Model

### Remembered Post Relationship

Use a dedicated viewer/post relationship table named `SavedPosts` if no existing compatible table is present.

```sql
CREATE TABLE IF NOT EXISTS SavedPosts (
  user_id INTEGER NOT NULL,
  post_id INTEGER NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id)
);
```

The table name for this change is `SavedPosts` unless an existing compatible table is already present.
Do not add collection, folder, source context, note, or ranking fields in this change.

The schema should be documented in SQL and also defensively ensured through the existing `humplore_ensure_database_schema()` pattern, because the project already uses runtime schema checks for brownfield compatibility.

## Behavior

### Reaction Rename

The existing `Wissenswert` action keeps its current backend behavior, paid/locked post behavior, and public count, but its visible label and accessible label become `Neues gelernt!`.

### Comment Action

The visible label changes from `Kommentar` to `Kommentieren`. The action should keep the existing comment count and focus or open the existing post comment area. Posts do not get a separate question mode in this change.

### Merken Action

The `Merken` action uses a bookmark/lesezeichen icon and toggles per viewer and post via Ajax/Fetch, matching the existing learning reaction pattern. The UI should reflect marked state immediately and remain correct after reload. Successful toggles use button state as feedback, update all rendered `Merken` buttons for the returned `post_id`, and do not show a toast. `Merken` is private to the viewer, does not show a public count, and is only part of the logged-in post-card experience. It may be used on visible paid/locked post cards, but it never grants access to locked content.

### Share Action

The share action keeps existing URL and Web Share API/copy-link behavior. On mobile it is icon-only and has no count.

## Security Considerations

- `Merken` toggles MUST validate the logged-in viewer.
- `Merken` toggles MUST validate CSRF using the existing CSRF mechanism.
- `Merken` toggles MUST verify that the target post exists.
- `Merken` state MUST only be exposed to the current viewer.
- `Merken` MUST NOT alter paid/locked content authorization.
- `Merken` MUST use `save_post_handler.php` and MUST NOT be implemented inside `like_handler.php`.
- Runtime schema creation MUST use `CREATE TABLE IF NOT EXISTS` and avoid destructive migrations.
- Ajax/Fetch requests to `save_post_handler.php` MUST include a CSRF token.
- Unauthenticated Ajax/Fetch requests MUST return JSON with HTTP 401 and MUST NOT redirect to login.
- Successful Ajax/Fetch responses MUST return minimal JSON with `post_id` and `saved: true` or `saved: false`.
- Successful Ajax/Fetch responses MUST NOT include public counts or HTML fragments.
- User-provided IDs MUST be cast/validated and used with prepared statements.
- Output labels and dynamic values MUST remain escaped.

## Accessibility Considerations

- Each action MUST expose a clear `aria-label`.
- Icon-only fallback states MUST still be understandable by screen readers.
- Mobile layout uses icon-only actions when labels would crowd the row, while keeping counts visible for `Neues gelernt!` and `Kommentieren`.
- Focus and hover states MUST remain visible.
- Mobile tap targets SHOULD remain at least 44px high where practical.

## Verification Strategy

- PHP syntax checks for changed PHP files.
- Manual checks on Explore/search and profile post cards.
- `Merken` toggle checks for Ajax success/failure, first mark, unmark, duplicate prevention, and reload persistence.
- Regression check that reaction, comment, and share still work.
