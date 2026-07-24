# Implementation Tasks: Filter Discovery

## Phase 1: Foundation

- [x] 1.1 Create SpecKit and OpenSpec planning artifacts
  - Document topic/category distinction and implementation scope.
  - Requirement: Story 1, Story 2

- [x] 1.2 Extend platform filter state
  - Parse `topic`, `cat`, and `sort`.
  - Add helpers for URLs and active filter labels.
  - Requirement: Story 3

## Phase 2: Core Implementation

- [x] 2.1 Load filter options from existing data
  - Load creator topics and contribution categories.
  - Include counts where cheap and useful.
  - Requirement: Story 1, Story 2

- [x] 2.2 Filter platform feed
  - Apply topic and category filters to Discover and Following modes.
  - Preserve pagination and sort behavior.
  - Requirement: Story 1, Story 2, Story 3

- [x] 2.3 Filter platform search
  - Apply the same filters to profile and post search results.
  - Preserve typo-tolerant search behavior.
  - Requirement: Story 1, Story 2

## Phase 3: UI Integration

- [x] 3.1 Activate category and topic controls
  - Replace disabled category links with filter links.
  - Add topic links and active filter chips.
  - Requirement: Story 3

- [x] 3.2 Preserve filters in forms and pagination
  - Keep selected filters through search, mode changes, comments, and infinite scroll.
  - Requirement: Story 3

## Phase 4: Verification

- [x] 4.1 Run PHP syntax checks on changed files
  - Requirement: All

- [x] 4.2 Run focused smoke checks against SQLite data
  - Verify category-only, topic-only, combined, and search-with-filter behavior.
  - Requirement: All
