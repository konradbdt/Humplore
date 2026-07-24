# Feature Specification: Filter Discovery

## Problem Statement

Humplore users can search, but they cannot narrow discovery by creator topic or by the contribution category of posts. The existing category sidebar is visually present but not functional, which blocks browsing when users do not know an exact search term.

## User Stories

### Story 1: Filter By Creator Topic

As a logged-in user, I want to filter discovery by a creator profile topic such as AIDS or ADHS, so that I can find experiences from creators who write about that topic.

**Acceptance Criteria:**
- [ ] Available topics are derived from creator profile topic fields.
- [ ] Selecting a topic limits feed posts to creators with that topic.
- [ ] Topic filtering can be combined with text search.

### Story 2: Filter By Contribution Category

As a logged-in user, I want to filter posts by contribution categories such as Alltag, Familie, or Beruf, so that I can browse a type of experience across all creator topics.

**Acceptance Criteria:**
- [ ] Available categories are derived from existing categories and post categories.
- [ ] Selecting a category limits posts to that contribution category.
- [ ] Category filtering can be combined with topic filtering.

### Story 3: Keep Discovery Navigable

As a logged-in user, I want active filters to be visible and removable, so that I understand why I am seeing a narrowed result set.

**Acceptance Criteria:**
- [ ] Active topic and category filters are shown near the results.
- [ ] Users can clear individual filters or all filters.
- [ ] Pagination and mode links preserve active filters.

## Non-Functional Requirements

- Security: Filter values MUST be bound through prepared statements and escaped in HTML.
- Compatibility: Existing routes and `Posts.category` data MUST continue to work.
- Incremental Data Model: The first implementation MAY use existing text fields; later schema work SHOULD keep topic and contribution category as separate axes.

## Clarifications

### Q1: Are creator topic and post category the same concept?

**Recommended Answer**: No. Creator topic is the profile-level experience area, while post category is the type of contribution inside or across topics.

**Answer**: Use two independent filter axes: `topic` from creator profile data and `cat` from post contribution categories.

## Success Metrics

- Users can click category filters in Explore and see narrowed posts.
- Users can combine a creator topic with a contribution category.
- Search and feed views preserve the selected filters.

## Out Of Scope

- A new normalized topics table.
- A Rabbithole-specific page or deep guided browsing UI.
- Admin workflows for curating allowed topics and categories.
