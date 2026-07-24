# Design: Topic Category Overview

## Technical Approach

Add a read-only Browse overview to the existing Explore page. The overview uses the same underlying topic and category axes that were introduced for filtering, but presents them as grouped discovery cards above the feed when the page is in the normal unfiltered state.

The first implementation remains inside `platform.php`, but grouped data loading should live in helper code so a later dedicated overview route can reuse the behavior.

## Architecture Decisions

### Decision: Extend Explore First

The overview will be rendered on `platform.php` instead of creating a new page.

**Rationale:**
- The user explicitly chose an Explore extension for the first iteration.
- Existing filter links already route through `platform.php`.
- Search and filter behavior should remain the primary continuation path.

**Alternatives considered:**
- Dedicated `topics.php` or `categories.php` page - deferred because it adds route and navigation scope before the overview behavior is proven.

### Decision: Keep Topic And Category Groups Separate

The overview will render creator topics and contribution categories in separate sections.

**Rationale:**
- The previous filter decision established topic and category as independent axes.
- Separate groups make it clear that topics describe creator experience areas while categories describe contribution type.

**Alternatives considered:**
- Mixed browse list - rejected because it would blur the existing filter model.

### Decision: Use Newest Matching Posts For Previews

Preview posts will be selected by newest matching content.

**Rationale:**
- Newest ordering is understandable to users.
- It avoids adding a new ranking model.
- It can be verified with existing timestamps.

**Alternatives considered:**
- Popularity ordering - deferred because it changes discovery semantics and depends on engagement data quality.
- Existing random Explore ordering - rejected for overview previews because it is less predictable.

### Decision: Link To Filtered Feed Instead Of Inline Expansion

"More" actions will navigate into the existing filtered Explore feed using `topic` or `cat`.

**Rationale:**
- The filtered feed already owns the full result list behavior.
- Inline expansion would duplicate feed behavior inside the overview.
- This keeps the Browse area compact.

**Alternatives considered:**
- Inline group expansion - deferred because it increases UI and pagination complexity.

## Data Flow

```text
platform.php page state
  -> check no search and no topic/category filter
  -> load overview groups
       -> topics from creator topic fields
       -> categories from post/category data
       -> newest post previews per group
       -> creator previews per topic
  -> render Browse area above feed
  -> "more" links use existing topic/cat filter URLs
```

## File Changes

- `Webseite - Codex/app/support/platform-page.php` - modified: add reusable overview visibility and grouped data loading helpers.
- `Webseite - Codex/platform.php` - modified: render Browse area above the feed only in the normal unfiltered state.
- `Webseite - Codex/css/styles.css` or page-local styles - modified if needed: style compact overview cards responsively.

## Data Rules

- Topic groups come from `COALESCE(CreatorDetails.main_topic, Users.main_topic)`.
- Category groups come from existing contribution category data, preserving compatibility with `Posts.category`, `Categories`, and `PostCategories`.
- Show at most 4 topic groups.
- Show at most 4 category groups.
- Show at most 2 newest posts per group.
- Show at most 2 creators per topic group.

## Risks

- Category data may contain free-text duplicates. Mitigation: group labels case-insensitively, matching the filter implementation's compatibility approach.
- Some groups may have sparse preview content. Mitigation: still render the group link when the group itself is valid.
- Later extraction to a separate page could be harder if HTML and data loading are coupled. Mitigation: keep grouped data assembly in helper functions.
