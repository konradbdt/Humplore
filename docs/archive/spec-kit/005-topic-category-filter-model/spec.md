# Feature Specification: Topic Category Filter Model

## Problem Statement

Humplore currently exposes concrete creator topics as the primary Explore topic filter. This conflicts with the product model: users should filter by broad topic categories such as Krankheit, Religion, Beruf, Herkunft, Alter, or Geschlecht/Identitaet, while concrete topics such as ADHS remain searchable or available as subfilters.

## User Stories

### Story 1: Filter By Topic Category

As a logged-in user, I want to filter by a broad topic category such as Krankheit, so that I can discover creators and posts whose concrete topics belong to that broader area.

**Acceptance Criteria:**
- [ ] The primary topic filter shows topic categories, not arbitrary concrete topic strings.
- [ ] Selecting Krankheit includes creators whose concrete topic maps to Krankheit, such as ADHS.
- [ ] Topic-category filtering can be combined with text search.

### Story 2: Keep Concrete Topics Searchable

As a logged-in user, I want concrete topics such as ADHS to remain searchable, so that exact discovery still works when I know what I am looking for.

**Acceptance Criteria:**
- [ ] Text search still matches concrete creator topics.
- [ ] Existing `topic=...` URLs continue to narrow results as a legacy/subfilter behavior.
- [ ] The main Explore filter does not list concrete topics as the primary filter choices.

### Story 3: Keep Contribution Categories Separate

As a logged-in user, I want categories such as Alltag, Familie, Schule & Studium, or Hobbys to stay separate from topic categories, so that I can filter the life context of posts independently.

**Acceptance Criteria:**
- [ ] Contribution categories continue to use `cat`.
- [ ] Contribution category filtering is independent from topic-category filtering.
- [ ] UI labels distinguish "Themenkategorie", "Thema", and "Kategorie".

## Non-Functional Requirements

- Compatibility: Existing search behavior and existing `topic`/`cat` links MUST continue to work.
- Incremental Data Model: The implementation MAY derive topic categories from existing free-text creator topics until a normalized topic schema exists.
- Maintainability: Topic-category mapping MUST be centralized rather than duplicated across SQL and UI code.
- Security: All user-provided filter values MUST be bound through prepared statements and escaped in HTML.

## Clarifications

### Q1: Should ADHS appear as a main Explore filter?

**Answer**: No. ADHS is a concrete topic. It can remain searchable or become a subfilter later, but the primary filter should be Krankheit.

### Q2: Are Alltag, Familie, Hobbys, and Schule & Studium topic categories?

**Answer**: No. They are contribution/life-context categories and stay on the `cat` axis.

### Q3: Should the current database be migrated now?

**Answer**: No. Use a compatible mapping layer first; a normalized topic schema can follow later.

## Success Metrics

- A user can select Krankheit and see posts from creators with concrete topics mapped to Krankheit.
- Concrete topics remain searchable.
- Active filter chips and browse labels use the correct terminology.
- No existing `cat` contribution-category filter behavior is removed.

## Out Of Scope

- Admin UI for maintaining topic-category mappings.
- Full normalized topic/category schema migration.
- Concrete topic subfilter UI beyond preserving existing `topic` URL support.
