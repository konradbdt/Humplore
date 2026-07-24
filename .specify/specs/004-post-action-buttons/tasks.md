# Implementation Tasks: Post Action Buttons

## Phase 1: Foundation

- [ ] 1.1 Confirm current post action rendering locations
  - Review `platform-post-card.php` and `profile-post-card.php`.
  - Check whether post modal contexts render their own action row or reuse a post card partial.
  - Identify shared CSS/JS hooks for action buttons.
  - Requirement: All stories

- [ ] 1.2 Define remembered-post persistence
  - Reuse an existing saved-post table if present.
  - Otherwise add a `SavedPosts` viewer/post relationship with a unique pair constraint.
  - Use `SavedPosts` as the table name for this change.
  - Document the schema in SQL.
  - Add defensive `CREATE TABLE IF NOT EXISTS` through the existing schema-helper pattern.
  - Requirement: Story 3

- [ ] 1.3 Add bulk remembered-state loading
  - Load saved state for rendered post IDs in one query per page render.
  - Pass saved-state maps into post-card partials.
  - Requirement: Story 3

## Phase 2: Backend Behavior

- [ ] 2.1 Add `Merken` toggle handling
  - Add `save_post_handler.php` instead of extending `like_handler.php`.
  - Validate logged-in viewer and target post.
  - Return JSON with HTTP 401 for unauthenticated requests.
  - Validate CSRF with the existing CSRF helper.
  - Insert save when absent and delete save when present.
  - Return `{ "post_id": 123, "saved": true }` or `{ "post_id": 123, "saved": false }` for Ajax/Fetch UI updates.
  - Do not return HTML fragments or public counts.
  - Requirement: Story 3

- [ ] 2.2 Preserve existing reaction behavior
  - Keep current `Wissenswert` data semantics.
  - Change only visible and accessible label to `Neues gelernt!`.
  - Requirement: Story 1

- [ ] 2.3 Preserve existing share behavior
  - Keep existing share handler and link generation.
  - Ensure action row changes do not break sharing.
  - Requirement: Story 4

## Phase 3: UI Integration

- [ ] 3.1 Update post action order and labels
  - Render `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`.
  - Keep Donation out of the rendered row for this iteration.
  - Requirement: Story 1, Story 2, Story 3, Story 4, Story 5

- [ ] 3.2 Update action icons
  - Keep lightbulb for `Neues gelernt!`.
  - Use speech bubble icon for `Kommentieren`.
  - Use bookmark icon for `Merken`.
  - Keep molecule-style icon for `Teilen`.
  - Requirement: Story 1, Story 2, Story 3, Story 4

- [ ] 3.3 Wire comment action
  - Focus or open the existing comment area for the post.
  - Do not introduce a separate question mode for posts.
  - Keep behavior consistent across Explore/search and profile cards.
  - Requirement: Story 2

- [ ] 3.4 Style remembered state
  - Show a clear selected state for saved posts.
  - Update the button state after a successful Ajax/Fetch response.
  - Update all rendered `Merken` buttons with the same returned `post_id`.
  - Do not show success toast notifications for normal `Merken` toggles.
  - Use icon-only mobile layout for the four actions.
  - Keep counts visible for `Neues gelernt!` and `Kommentieren` on mobile.
  - Keep mobile layout tappable without relying on visible labels.
  - Requirement: Story 3, Non-functional mobile layout

## Phase 4: Verification

- [ ] 4.1 Run PHP syntax checks on changed PHP files
  - Requirement: All

- [ ] 4.2 Verify action row behavior
  - Check Explore feed, search results, profile posts, and modal contexts where post actions render.
  - Verify action order, labels, icons, and selected states.
  - Requirement: All

- [ ] 4.3 Verify `Merken` persistence
  - Mark a post with `Merken`, reload, confirm state remains.
  - Unmark a post, reload, confirm state clears.
  - Try saving the same post twice and confirm no duplicate records.
  - Confirm unauthenticated requests return JSON 401 without redirect.
  - Confirm invalid/missing CSRF is rejected.
  - Confirm successful responses include `post_id` and only the updated saved state needed by the UI.
  - Confirm failed Ajax/Fetch requests do not leave the UI in a misleading state.
  - Requirement: Story 3

- [ ] 4.4 Verify regressions
  - Confirm learning reaction still toggles and counts correctly.
  - Confirm comment action reaches the comment surface.
  - Confirm share still uses the existing share behavior.
  - Confirm mobile action buttons are icon-only, keep counts visible where required, and have accessible labels.
  - Requirement: Story 1, Story 2, Story 4
