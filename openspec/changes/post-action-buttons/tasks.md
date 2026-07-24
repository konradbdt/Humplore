# Tasks: Post Action Buttons

## 1. Foundation

- [ ] 1.1 Locate all active post-card action row partials.
- [ ] 1.2 Locate any post modal action row markup or confirm it reuses card markup.
- [ ] 1.3 Confirm whether remembered-post persistence already exists.
- [ ] 1.4 Document `SavedPosts` SQL schema.
- [ ] 1.5 Add or reuse a `SavedPosts` relationship with duplicate prevention.
- [ ] 1.6 Add defensive `CREATE TABLE IF NOT EXISTS` through the existing schema-helper pattern.
- [ ] 1.7 Keep `SavedPosts` limited to `user_id`, `post_id`, and `created_at`.

## 2. Backend

- [ ] 2.1 Load remembered state in bulk for post cards rendered on Explore/search and profile pages.
- [ ] 2.2 Add `save_post_handler.php` as the dedicated Ajax/Fetch `Merken` handler for the current viewer and target post.
- [ ] 2.3 Keep `Merken` handling separate from `like_handler.php`.
- [ ] 2.4 Validate `Merken` requires a logged-in viewer before mutating state.
- [ ] 2.5 Return JSON with HTTP 401 for unauthenticated `save_post_handler.php` requests, with no login redirect.
- [ ] 2.6 Validate CSRF in `save_post_handler.php`.
- [ ] 2.7 Send CSRF token with `Merken` Ajax/Fetch requests.
- [ ] 2.8 Return minimal success JSON from `save_post_handler.php`: `{ "post_id": 123, "saved": true }` or `{ "post_id": 123, "saved": false }`.
- [ ] 2.9 Preserve existing learning reaction toggle behavior, paid/locked behavior, and public count while renaming the UI label.
- [ ] 2.10 Preserve existing share behavior.

## 3. UI

- [ ] 3.1 Render action order: `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`.
- [ ] 3.2 Use lightbulb, speech bubble, bookmark, and molecule-style icons.
- [ ] 3.3 Change visible `Kommentar` labels to `Kommentieren` and wire the action to open or focus the existing comment surface.
- [ ] 3.4 Preserve the existing comment count on `Kommentieren`.
- [ ] 3.5 Show marked/unmarked state clearly.
- [ ] 3.6 Update all rendered `Merken` buttons for the returned `post_id` after successful Ajax/Fetch responses without showing success toasts, and handle failures without misleading state.
- [ ] 3.7 Keep Donation out of the rendered row for this change.
- [ ] 3.8 Do not render public `Merken` counts.
- [ ] 3.9 Add icon-only mobile layout while preserving accessible labels, visible counts for `Neues gelernt!` and `Kommentieren`, and no counts for `Merken` or `Teilen`.

## 4. Verification

- [ ] 4.1 Run PHP syntax checks on changed PHP files.
- [ ] 4.2 Verify action order and labels on Explore/search, profile cards, and modal contexts.
- [ ] 4.3 Verify mark, unmark, reload persistence, and duplicate prevention.
- [ ] 4.4 Verify Ajax/Fetch success and failure behavior for `Merken`.
- [ ] 4.5 Verify unauthenticated handler requests return JSON 401 without redirect.
- [ ] 4.6 Verify missing/invalid CSRF is rejected without state mutation.
- [ ] 4.7 Verify success responses include `post_id` and `saved: true/false`, and do not include counts or HTML.
- [ ] 4.8 Verify duplicate rendered `Merken` buttons for the same `post_id` synchronize state.
- [ ] 4.9 Verify learning reaction, comment entry, and share regressions.
- [ ] 4.10 Verify `Merken` state is visible only for the current viewer and no public count appears.
- [ ] 4.11 Verify logged-out post-card contexts do not expose actionable `Merken` behavior.
- [ ] 4.12 Verify `Merken` on visible paid/locked post cards does not unlock content.
- [ ] 4.13 Inspect mobile layout for icon-only actions, visible counts, tappable targets, and no overlap.
