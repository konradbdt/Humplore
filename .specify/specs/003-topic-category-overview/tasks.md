# Implementation Tasks: Topic And Category Overview

## Phase 1: Foundation

- [x] 1.1 Add overview visibility state
  - Derive a boolean for normal unfiltered Explore browsing.
  - Hide the overview when search, topic filter, or category filter is active.
  - Requirement: Story 3

- [x] 1.2 Add grouped overview data helper
  - Create a reusable helper under `app/support`.
  - Return separate topic and category groups.
  - Keep the helper independent from `platform.php` markup for future extraction.
  - Requirement: Story 1, Story 2, Story 4

## Phase 2: Data Loading

- [x] 2.1 Load topic overview groups
  - Select at most 4 creator topics from existing creator topic fields.
  - Attach up to 2 newest matching posts per topic.
  - Attach up to 2 matching creators per topic.
  - Requirement: Story 1

- [x] 2.2 Load category overview groups
  - Select at most 4 contribution categories from existing category data.
  - Attach up to 2 newest matching posts per category.
  - Preserve compatibility with `Posts.category`, `Categories`, and `PostCategories`.
  - Requirement: Story 2

- [x] 2.3 Build filtered feed links
  - Generate topic links with the existing `topic` query parameter.
  - Generate category links with the existing `cat` query parameter.
  - Keep links on `platform.php`.
  - Requirement: Story 4

## Phase 3: UI Integration

- [x] 3.1 Render Browse area above the feed
  - Add a compact section in the main column above feed results.
  - Render topics and categories as separate groups.
  - Ensure the section remains visible on mobile.
  - Requirement: Story 1, Story 2, Story 3

- [x] 3.2 Render preview content
  - Show up to 2 newest post previews per group.
  - Show up to 2 creator previews on topic groups.
  - Add empty-state copy only when a valid group has no previews.
  - Requirement: Story 1, Story 2

- [x] 3.3 Wire "more" actions
  - Link each topic group into the topic-filtered feed.
  - Link each category group into the category-filtered feed.
  - Do not implement inline expansion in this iteration.
  - Requirement: Story 4

## Phase 4: Verification

- [x] 4.1 Run PHP syntax checks on changed PHP files
  - Requirement: All

- [x] 4.2 Run focused data smoke checks
  - Verify group limits, preview limits, and newest-post ordering.
  - Verify visibility rules for unfiltered, search, topic-filtered, and category-filtered states.
  - Requirement: All

- [ ] 4.3 Manually inspect desktop and mobile layouts
  - Confirm the Browse area appears above the feed and does not replace the feed.
  - Confirm sidebars remain unchanged as quick filters.
  - Requirement: Story 3
