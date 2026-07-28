# Proposal: Post Action Buttons

## Why

Humplore's post action row should express the platform's learning intent more clearly and support a stronger return-later workflow. The current actions cover learning reaction, commenting, and sharing, but the labels and action set do not yet communicate `Neues gelernt!`, focused post comments, or persistent remembering.

## What Changes

- Rename the existing `Wissenswert` reaction to `Neues gelernt!`.
- Keep the lightbulb icon for the learning reaction.
- Keep the response action as `Kommentieren` for post comments only.
- Add a persistent `Merken` action with marked/unmarked state.
- Keep the existing share action with its molecule-style icon.
- Preserve space in the action model for a later post-level Donation action without rendering Donation now.
- Keep post action rows consistent across Explore/search cards and profile cards.
- Keep post action rows identical across Explore, search results, profile post cards, and post modal contexts where actions are rendered.

## Capabilities

### New Capabilities

- **engagement** - Post action toolbar behavior for learning reactions, comment entry, saving, sharing, and future donation extension.

### Modified Capabilities

- Existing post reaction and sharing behavior are preserved while labels and row composition change.

## Impact

- Affects post card partials under `Webseite - Codex/app/views/partials`.
- May add a saved-post relationship table or reuse an existing saved-post persistence concept.
- May add a save toggle route or handler.
- Requires bulk remembered-state loading for rendered posts.
- Does not implement donation, payment, payout, or creator support behavior.
- Later Donation is intended to be per post.

## Rollback

Remove the save action and persistence handler, restore the previous visible reaction/comment labels, and keep the existing share action unchanged.
