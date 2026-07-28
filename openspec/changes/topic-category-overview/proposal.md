# Proposal: Topic Category Overview

## Why

Humplore's search and filters now support discovery, but users still need an overview that shows which topics and contribution categories exist and what content belongs to them. The current sidebar is a quick filter surface, not a browse-oriented overview with matching posts and creators.

## What Changes

- Add a compact Browse area above the normal Explore feed.
- Show creator topics and contribution categories as two separate groups.
- Show up to 4 topic groups and 4 category groups.
- Show up to 2 newest matching posts per group.
- Show up to 2 matching creators on topic groups.
- Link "more" actions into the existing filtered `platform.php` feed.
- Hide the overview during active search or active topic/category filtering.
- Keep the design extractable so the overview can later move to a dedicated page.

## Capabilities

### New Capabilities

- **discovery** - Topic and category overview behavior for browsing available experience areas inside Explore.

### Modified Capabilities

- None. Existing search and filter behavior remains unchanged.

## Impact

- Affects `platform.php` rendering.
- Adds or extends helper behavior under `Webseite - Codex/app/support`.
- Reuses existing `topic` and `cat` filter URL behavior.
- Does not require a database migration.
- Does not add a new public route in the first implementation.

## Rollback

Remove the overview loader and rendering changes while keeping existing search and filter functionality intact.
