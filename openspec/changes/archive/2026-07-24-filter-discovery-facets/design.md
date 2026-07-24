# Design: Filter Discovery Facets

## Approach

Use URL query parameters as the public interface:

- `topic`: creator profile topic such as AIDS or ADHS.
- `cat`: contribution category such as Alltag or Familie.
- `sort`: `discover`, `latest`, or `popular`.

The first version keeps existing storage:

- Creator topic comes from `COALESCE(CreatorDetails.main_topic, Users.main_topic)`.
- Contribution category comes from `Posts.category`, with `Categories` and `PostCategories` used where present.

## File Changes

- Update `Webseite - Codex/app/support/platform-page.php`.
- Update `Webseite - Codex/app/support/search-discovery.php`.
- Update `Webseite - Codex/app/views/partials/profile-sidebar-category-link.php`.
- Update `Webseite - Codex/platform.php`.

## Query Behavior

- Topic filter limits posts to creators whose profile topic equals the selected topic.
- Category filter limits posts to the selected contribution category.
- Combined filters apply with AND semantics.
- Following mode keeps its existing follower restriction and applies filters on top.

## UI Behavior

- The existing sidebar category area becomes clickable.
- A topic section is added using live creator topic values.
- Active filters are shown as removable chips.
- Search and mode controls preserve active filters.

## Risks

- Existing category data is partly free-text and may contain duplicates. Mitigation: group options case-insensitively and keep exact labels for filtering.
- Later Rabbithole browsing may require a normalized topic/category schema. Mitigation: keep this change additive and preserve separate filter axes.
