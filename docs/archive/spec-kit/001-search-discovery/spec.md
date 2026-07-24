# Feature Specification: Search Discovery Foundation

## Problem Statement

Humplore currently searches profiles and posts with exact `LIKE` matching. Users who mistype a term or use a nearby phrase get no useful path forward, even when related creators, topics, categories, or posts exist.

## User Stories

### Story 1: Search With Imperfect Terms

As a logged-in user, I want search to still surface related profiles and posts when my term is slightly misspelled, so that I do not need to know the exact wording.

**Acceptance Criteria:**
- [ ] Searching with an exact term returns matching profiles and posts.
- [ ] Searching with a near miss returns related profiles or posts when a close indexed term exists.
- [ ] Results indicate when they came from related terms rather than direct matches.

### Story 2: Suggested Terms

As a logged-in user, I want suggestions when my query has few or no results, so that I can continue discovery without guessing.

**Acceptance Criteria:**
- [ ] Suggestions are built from existing usernames, creator topics, post titles, post categories, and category names.
- [ ] Suggestions link back to search using the suggested term.
- [ ] Suggestions are limited to a small, readable set.

### Story 3: Reusable Search Behavior

As a maintainer, I want shared search logic, so that `search.php` and `platform.php` do not diverge.

**Acceptance Criteria:**
- [ ] Search logic lives in a shared helper under `app/support`.
- [ ] `search.php` uses the shared helper.
- [ ] `platform.php` uses the same shared helper for its search data.

## Non-Functional Requirements

- Performance: The first implementation MAY use bounded in-process fuzzy matching for the current SQLite dataset; it MUST cap candidate and result counts.
- Security: All user input MUST be parameterized in SQL and escaped in HTML output.
- Compatibility: Existing database schema and routes MUST continue to work.

## Clarifications

### Q1: Should this first step use a full search engine?

**Recommended Answer**: No. Use a lightweight SQLite/PHP implementation first because the current project is a small brownfield PHP app and has no package manager or service runtime.

**Answer**: Apply the recommended lightweight approach.

## Success Metrics

- A typo-prone query can produce either related results or suggested terms.
- Exact search behavior remains available.
- Both search entry points share one implementation.

## Out Of Scope

- Advanced ranked search infrastructure.
- Autocomplete API endpoint.
- Faceted filters. This follows as the next prioritized change.
