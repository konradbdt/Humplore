# Change Proposal: Filter Discovery Facets

## Why

Humplore's Explore page shows category navigation but does not actually filter content. Users need two distinct ways to narrow discovery: by the creator's profile topic and by a post's contribution category.

## What Changes

- Add topic filtering based on creator profile topics.
- Add contribution category filtering based on post categories.
- Allow topic and category filters to be combined.
- Preserve active filters through search, feed mode, pagination, and clearing actions.
- Keep the first implementation on the existing PHP/SQLite data model.

## Impact

- Affects `platform.php`.
- Updates shared helpers in `app/support/platform-page.php` and `app/support/search-discovery.php`.
- Updates the shared category sidebar partial.
- No destructive schema migration.

## Rollback

Revert the helper changes and restore category links to their disabled state.
