# Tasks: Post Action Buttons

## 1. Foundation

- [x] 1.1 Locate all active post-card action row partials.
- [x] 1.2 Locate any post modal action row markup or confirm it reuses card markup.
- [x] 1.3 Confirm whether remembered-post persistence already exists.
- [x] 1.4 Document `SavedPosts` SQL schema.
- [x] 1.5 Add or reuse a `SavedPosts` relationship with duplicate prevention.
- [x] 1.6 Add defensive `CREATE TABLE IF NOT EXISTS` through the existing schema-helper pattern.
- [x] 1.7 Keep `SavedPosts` limited to `user_id`, `post_id`, and `created_at`.

## 2. Backend

- [x] 2.1 Load remembered state in bulk for post cards rendered on Explore/search and profile pages.
- [x] 2.2 Add `save_post_handler.php` as the dedicated Ajax/Fetch `Merken` handler for the current viewer and target post.
- [x] 2.3 Keep `Merken` handling separate from `like_handler.php`.
- [x] 2.4 Validate `Merken` requires a logged-in viewer before mutating state.
- [x] 2.5 Return JSON with HTTP 401 for unauthenticated `save_post_handler.php` requests, with no login redirect.
- [x] 2.6 Validate CSRF in `save_post_handler.php`.
- [x] 2.7 Send CSRF token with `Merken` Ajax/Fetch requests.
- [x] 2.8 Return minimal success JSON from `save_post_handler.php`: `{ "post_id": 123, "saved": true }` or `{ "post_id": 123, "saved": false }`.
- [x] 2.9 Preserve existing learning reaction toggle behavior, paid/locked behavior, and public count while renaming the UI label.
- [x] 2.10 Preserve existing share behavior.

## 3. UI

- [x] 3.1 Render action order: `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`.
- [x] 3.2 Use lightbulb, speech bubble, bookmark, and molecule-style icons.
- [x] 3.3 Change visible `Kommentar` labels to `Kommentieren` and wire the action to open or focus the existing comment surface.
- [x] 3.4 Preserve the existing comment count on `Kommentieren`.
- [x] 3.5 Show marked/unmarked state clearly.
- [x] 3.6 Update all rendered `Merken` buttons for the returned `post_id` after successful Ajax/Fetch responses without showing success toasts, and handle failures without misleading state.
- [x] 3.7 Keep Donation out of the rendered row for this change.
- [x] 3.8 Do not render public `Merken` counts.
- [x] 3.9 Add icon-only mobile layout while preserving accessible labels, visible counts for `Neues gelernt!` and `Kommentieren`, and no counts for `Merken` or `Teilen`.

## 4. Verification

- [x] 4.1 Run PHP syntax checks on changed PHP files.
- [x] 4.2 Verify action order and labels on Explore/search, profile cards, and modal contexts.
- [x] 4.3 Verify mark, unmark, reload persistence, and duplicate prevention.
- [x] 4.4 Verify Ajax/Fetch success and failure behavior for `Merken`.
- [x] 4.5 Verify unauthenticated handler requests return JSON 401 without redirect.
- [x] 4.6 Verify missing/invalid CSRF is rejected without state mutation.
- [x] 4.7 Verify success responses include `post_id` and `saved: true/false`, and do not include counts or HTML.
- [x] 4.8 Verify duplicate rendered `Merken` buttons for the same `post_id` synchronize state.
- [x] 4.9 Verify learning reaction, comment entry, and share regressions.
- [x] 4.10 Verify `Merken` state is visible only for the current viewer and no public count appears.
- [x] 4.11 Verify logged-out post-card contexts do not expose actionable `Merken` behavior.
- [x] 4.12 Verify `Merken` on visible paid/locked post cards does not unlock content.
- [x] 4.13 Inspect mobile layout for icon-only actions, visible counts, tappable targets, and no overlap.

Verification note (2026-08-11): The standalone `search.php` now renders both
search results and latest posts through `platform-post-card.php`, using bulk
lookups for reactions, comments, and saved state. An isolated copy of the
SQLite database with two dedicated test accounts and free/locked test posts was
used for all mutating checks; `Webseite - Codex/data/database.db` was not
changed by the verification.

- PHP lint passed for `search.php`, `platform.php`, `profile.php`, and
  `app/views/partials/platform-post-card.php`; `node --check` passed for
  `js/post-actions.js`.
- Browser checks passed for standalone search results, standalone latest-post
  cards, Explore search/feed cards, profile cards, and the cloned post modal.
  Every context showed `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen` in
  that order.
- Mark, reload persistence, unmark, one-row duplicate protection, and
  synchronization across two cards plus a modal clone passed. A forced network
  failure kept the previous state, re-enabled the buttons, and exposed an
  `aria-live` error message.
- Handler checks returned JSON 401 without redirect for guests, JSON 400 for
  missing and invalid CSRF without mutation, JSON 404 for a missing post, and
  exact two-field success bodies for both `{post_id, saved:true}` and
  `{post_id, saved:false}`.
- A second logged-in account did not receive the first account's saved state;
  no save count appeared. Logged-out search redirected before cards rendered,
  and a viewer-id-zero partial render contained no actionable save control.
- Learning reaction count/state, comment focus and comment submission/count,
  and share-link fallback passed. Saving a locked post kept the card locked,
  its banner present, and its content blurred.
- At 1440x1000 the desktop action labels and controls rendered without overlap.
  At 390x844 labels were icon-only, reaction/comment counts stayed visible,
  all four controls were 44px high, and their bounds did not overlap.
