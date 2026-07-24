# Feature Specification: Post Action Buttons

## Problem Statement

Post cards currently expose only a small action row: `Wissenswert`, comment, and share. The interaction model should better match Humplore's learning-oriented positioning by renaming the learning reaction, keeping contribution discussion focused on comments, adding a persistent `Merken` action, and preserving space for a later donation action without mixing it into the first implementation.

## User Stories

### Story 1: Mark A Post As Newly Learned

As a logged-in reader, I want to mark a post with `Neues gelernt!`, so that my reaction signals that I learned something from the contribution.

**Acceptance Criteria:**
- [ ] The existing `Wissenswert` action is renamed to `Neues gelernt!`.
- [ ] The action keeps the lightbulb icon.
- [ ] The action preserves the existing toggle behavior and count semantics unless a later change explicitly redefines them.
- [ ] The action continues to show the existing public count.
- [ ] The action preserves existing paid/locked post behavior.
- [ ] The selected state remains visually clear on desktop and mobile.
- [ ] The action appears consistently across Explore, search results, profile post cards, and post modal contexts.
- [ ] On mobile, the visible label MAY be hidden for icon-only layout.
- [ ] On mobile, the public count remains visible.

### Story 2: Comment On A Post

As a logged-in reader, I want a comment action on posts, so that I can respond to the contribution in the post's comment area.

**Acceptance Criteria:**
- [ ] Every post action row shows `Kommentieren`.
- [ ] The visible label is `Kommentieren`, replacing the current `Kommentar` label.
- [ ] The action uses a speech bubble icon.
- [ ] The action continues to show the existing comment count.
- [ ] The action opens or focuses the existing comment surface for the post.
- [ ] The action does not introduce a separate question mode for posts.
- [ ] The label is consistent across Explore/search post cards and profile post cards.
- [ ] The label is also consistent in post modal contexts when the modal renders post actions.
- [ ] On mobile, the visible label MAY be hidden for icon-only layout.
- [ ] On mobile, the comment count remains visible.

### Story 3: Remember A Post

As a logged-in reader, I want to mark posts with `Merken`, so that I can return to useful contributions later.

**Acceptance Criteria:**
- [ ] Every post action row shows a `Merken` action with a bookmark-style icon.
- [ ] The icon is a bookmark/lesezeichen symbol, not a disk/save-file icon.
- [ ] The action toggles marked/unmarked state for the current viewer.
- [ ] The action toggles via Ajax/Fetch to match the existing learning reaction behavior.
- [ ] The saved state is persistent across page reloads.
- [ ] The marked state is private to the current viewer.
- [ ] The action does not show a public count.
- [ ] Successful toggles update the active bookmark state visually.
- [ ] Successful toggles update all rendered `Merken` buttons for the returned `post_id`.
- [ ] Successful toggles do not show a toast notification.
- [ ] The action is available only in the logged-in post-card experience.
- [ ] The action works on paid/locked post cards when the post card itself is visible.
- [ ] Marking a paid/locked post does not grant access to locked content.
- [ ] Saving the same post multiple times does not create duplicate saved records.
- [ ] The saved state is independent from profile settings or other form `Speichern` buttons.
- [ ] On mobile, the visible label MAY be hidden for icon-only layout.

### Story 4: Share A Post

As a logged-in reader, I want the existing share action to remain available, so that I can copy or share a post link.

**Acceptance Criteria:**
- [ ] The post action row continues to show share as a separate action.
- [ ] The share action keeps the molecule-style icon.
- [ ] Existing share behavior and post-link generation continue to work.
- [ ] The share action remains visually separate from save and future donation.
- [ ] The share action is consistent across Explore, search results, profile post cards, and post modal contexts.
- [ ] On mobile, the visible label MAY be hidden for icon-only layout.
- [ ] On mobile, share remains icon-only without a count.

### Story 5: Leave Room For Later Donation

As a product owner, I want the first implementation to leave a clean extension point for a later donation action, so that donation can be added without redesigning the post action row.

**Acceptance Criteria:**
- [ ] Post-level Donation is documented as a later action but is not implemented in this iteration.
- [ ] The action row layout can fit a future donation action without hiding the core actions.
- [ ] No disabled or misleading donation button is shown before donation behavior exists.

## Non-Functional Requirements

- Consistency: The action row MUST be consistent across `platform-post-card.php` and `profile-post-card.php`.
- Consistency: The action row MUST remain identical across Explore, search results, profile post cards, and post modal contexts when those contexts render post actions.
- Accessibility: Every icon action MUST have an accessible label that matches the action intent, especially when mobile hides visible labels.
- Mobile Layout: The row MUST use icon-only actions on small screens where labels would crowd the row.
- Mobile Layout: Mobile icon-only layout MUST keep counts visible for `Neues gelernt!` and `Kommentieren`.
- Mobile Layout: Icon-only mobile actions MUST remain tappable and MUST NOT rely on visible text.
- Compatibility: Existing like, comment, and share behavior MUST continue to work while labels and layout change.
- Compatibility: `Merken` MUST NOT change paid/locked post access rules.
- Separation: `Merken` MUST use its own handler instead of being added to `like_handler.php`.
- Separation: The dedicated handler SHOULD be named `save_post_handler.php`.
- Security: `save_post_handler.php` MUST validate CSRF for write requests.
- Error Handling: Unauthenticated `save_post_handler.php` requests MUST return JSON with HTTP 401 rather than redirecting.
- API Response: Successful `save_post_handler.php` responses MUST return minimal JSON with `post_id` and `saved: true` or `saved: false`.
- API Response: Successful `save_post_handler.php` responses MUST NOT return HTML fragments or public counts.
- Persistence: `Merken` state MUST use a viewer/post relationship that prevents duplicate saves.
- Persistence: The viewer/post relationship table MUST be named `SavedPosts` unless an existing compatible table is already present.
- Persistence: `SavedPosts` MUST remain minimal with `user_id`, `post_id`, and `created_at` only in this change.
- Persistence: `Merken` schema SHOULD be documented in SQL and MAY also be defensively ensured in the existing schema helper pattern.
- Privacy: `Merken` state MUST remain private to the viewer and MUST NOT expose public counts in this change.
- Authentication: `Merken` MUST be available only for logged-in users in this change.
- Future Extension: Donation SHOULD be introduced later as its own change with behavior, persistence, and payment/support decisions.

## Clarifications

### Q1: Is `Neues gelernt!` a new reaction type or the new label for the existing `Wissenswert` action?

**Recommended Answer**: Treat it as the new label for the existing reaction in the first iteration, so existing counts and data stay compatible.

**Answer**: Use the existing reaction behavior and rename the visible label to `Neues gelernt!`.

### Q2: Should `Merken` be implemented as a real persistent feature or only as a visual button?

**Recommended Answer**: Implement real persistence because a visual-only save action would create a broken user expectation.

**Answer**: `Merken` MUST persist per viewer and post.

### Q3: Should Donation appear now as a disabled placeholder?

**Recommended Answer**: No. Disabled donation UI creates confusion before support/payment behavior is defined.

**Answer**: Document Donation as a later action, but do not render it in this iteration.

### Q4: What is the intended action order?

**Recommended Answer**: Use learning reaction first, then response, then save, then share; reserve donation for a later extension.

**Answer**: `Neues gelernt!`, `Kommentieren`, `Merken`, `Teilen`, later `Donation`.

### Q5: Should posts support a separate question mode from the action row?

**Recommended Answer**: Keep post responses as comments only. A separate question flow would affect data model and notification behavior and should be specified separately if needed.

**Answer**: Beiträgen haben nur Kommentare; no separate post question mode in this change.

### Q6: Should the saved-post action be labeled `Save`, `Speichern`, or `Merken`?

**Recommended Answer**: Use `Merken` because the rest of the action row is German and `Speichern` is already used for form/profile saving.

**Answer**: Use `Merken`.

### Q7: Should `Neues gelernt!` keep the existing public count?

**Recommended Answer**: Keep the count for now because the existing `Wissenswert` action already has count semantics and this change should not redefine reaction visibility.

**Answer**: Keep the existing public count.

### Q8: Should `Merken` have a public count?

**Recommended Answer**: Keep `Merken` private because it is a personal return-later signal, not social proof.

**Answer**: `Merken` is private to the viewer and has no public count.

### Q9: Should `Merken` be visible to logged-out visitors?

**Recommended Answer**: Keep `Merken` to the logged-in state only because it depends on a viewer/post relationship.

**Answer**: `Merken` is only part of the logged-in post-card experience.

### Q10: Should later Donation be per post or per creator?

**Recommended Answer**: Per creator would be simpler, but the product decision is to make Donation post-level.

**Answer**: Later Donation should be per Beitrag/post.

### Q11: Should the action row be identical across post contexts?

**Recommended Answer**: Keep it identical across Explore, search results, profile post cards, and modals to avoid inconsistent behavior and duplicated UI drift.

**Answer**: The action row should stay identical across all post contexts.

### Q12: Should `Merken` toggle via Ajax/Fetch or normal form submit?

**Recommended Answer**: Use Ajax/Fetch because the existing `Wissenswert` reaction already toggles via `fetch('like_handler.php')`, and `Merken` should feel consistent in the same action row.

**Answer**: `Merken` should toggle via Ajax/Fetch.

### Q13: Should the comment action keep the comment count?

**Recommended Answer**: Keep the comment count because it already exists and helps users see whether discussion is happening under the post.

**Answer**: Keep the existing comment count on `Kommentieren`.

### Q14: Should the visible comment label be `Kommentieren` or stay `Kommentar`?

**Recommended Answer**: Use visible `Kommentieren` because the action row should use action-oriented labels.

**Answer**: The visible label should be `Kommentieren`.

### Q15: Should `Merken` use a bookmark icon or disk/save icon?

**Recommended Answer**: Use a bookmark/lesezeichen icon because `Merken` means saving something for later, not saving a file or form.

**Answer**: Use a bookmark icon.

### Q16: Should mobile show all labels or icons only?

**Recommended Answer**: Keep labels where space allows, but the product decision is mobile icon-only. This requires strong accessible labels for all actions.

**Answer**: Mobile should use icons only.

### Q17: Should counts remain visible on mobile icon-only actions?

**Recommended Answer**: Keep counts visible for `Neues gelernt!` and `Kommentieren`; only text labels are hidden.

**Answer**: Counts remain visible on mobile.

### Q18: Should `Merken` schema be added through SQL migration or defensive helper creation?

**Recommended Answer**: Use both where consistent: document the SQL schema and defensively ensure the table through the existing `humplore_ensure_database_schema()` pattern.

**Answer**: Add documented SQL plus defensive `CREATE TABLE IF NOT EXISTS` in the existing schema-helper pattern.

### Q19: What should the `Merken` table be named?

**Recommended Answer**: Use `SavedPosts` as the technical table name while keeping the UI label `Merken`.

**Answer**: Use `SavedPosts`.

### Q20: Should `SavedPosts` include future collection/context fields now?

**Recommended Answer**: Keep the schema minimal with `user_id`, `post_id`, and `created_at`; collections or folders can be specified later.

**Answer**: Keep `SavedPosts` minimal.

### Q21: Should `Merken` work for paid or locked posts?

**Recommended Answer**: Yes, if the post card is visible. Marking a post should not grant access to locked content.

**Answer**: `Merken` works on visible paid/locked post cards but does not unlock content.

### Q22: Should `Neues gelernt!` behavior change for paid or locked posts?

**Recommended Answer**: Keep existing `Wissenswert` behavior because this change should not redefine reaction authorization.

**Answer**: Preserve existing behavior.

### Q23: Should `Teilen` be icon-only without a count on mobile?

**Recommended Answer**: Yes. Keep the existing share behavior, hide the visible label on mobile, and do not add a count.

**Answer**: `Teilen` is icon-only on mobile and has no count.

### Q24: Should `Merken` show a toast after successful toggles?

**Recommended Answer**: No toast for normal success; the active bookmark state is enough feedback.

**Answer**: Show visual active/inactive state, no success toast.

### Q25: Should `Merken` use its own handler or be added to `like_handler.php`?

**Recommended Answer**: Use a dedicated handler because `Merken` is private, countless, and uses different persistence from public learning reactions.

**Answer**: Use a dedicated handler for `Merken`.

### Q26: What should the dedicated `Merken` handler be named?

**Recommended Answer**: Use the English technical name `save_post_handler.php`, matching `SavedPosts` and the existing `like_handler.php` naming style.

**Answer**: Use English naming: `save_post_handler.php`.

### Q27: Should `save_post_handler.php` validate CSRF?

**Recommended Answer**: Yes. New write handlers should use the existing `humplore_require_csrf()` mechanism even if older handlers do not yet do so.

**Answer**: `save_post_handler.php` MUST validate CSRF.

### Q28: How should unauthenticated `Merken` Ajax requests be handled?

**Recommended Answer**: Return JSON with HTTP 401 because `save_post_handler.php` is a Fetch handler and should not return login HTML via redirect.

**Answer**: Return JSON with HTTP 401, no redirect.

### Q29: What should `save_post_handler.php` return on success?

**Recommended Answer**: Return minimal JSON with `saved: true` or `saved: false`; no count and no HTML.

**Answer**: Return `{ "saved": true }` or `{ "saved": false }`.

### Q30: Should success JSON include `post_id`?

**Recommended Answer**: Include `post_id` so the UI can synchronize multiple rendered instances of the same post.

**Answer**: Include `post_id`.

### Q31: Should duplicate rendered `Merken` buttons for the same post synchronize?

**Recommended Answer**: Yes. Use the returned `post_id` to update all matching `Merken` buttons in the DOM.

**Answer**: Update all rendered `Merken` buttons for the same `post_id`.

## Success Metrics

- Users understand the first reaction as a learning signal instead of a generic like.
- Users can access comment behavior from every post card.
- Users can mark and unmark posts with `Merken` without losing state on reload.
- `Merken` stays a private user utility rather than a public popularity metric.
- Existing share behavior remains unchanged.
- Donation can be added later without reworking the documented action model.

## Out Of Scope

- Implementing donation, payments, tips, payouts, or creator support flows.
- Adding a dedicated saved-posts library page unless a later change defines it.
- Redesigning the entire post card outside the action row.
- Changing feed ranking based on saved posts or reactions.
