# Implementation Tasks: Profile Discovery Filters

## Phase 1: Specification And Compatibility

- [x] 1.1 Capture clarified filter terminology and decisions in Spec-Kit.
- [x] 1.2 Create OpenSpec change proposal, design, tasks, and delta spec.
- [ ] 1.3 Confirm no existing `topic_cat`, `topic`, or `cat` route behavior is removed.

## Phase 2: Stage 1 Existing-Data Filters

- [ ] 2.1 Extend filter parsing to support repeated parameters and legacy single values.
- [ ] 2.2 Add `profile_city` parsing and normalized exact matching against `CreatorDetails.ort`.
- [ ] 2.3 Add controlled `profile_language` parsing and matching against `CreatorDetails.sprache`.
- [ ] 2.4 Apply profile filters to Explore feed queries by restricting matching creators.
- [ ] 2.5 Apply profile filters to profile search and profile suggestions.
- [ ] 2.6 Preserve profile filters through search form, sorting, pagination, and clear actions.

## Phase 3: UI

- [ ] 3.1 Add a collapsed "Profilfilter" section to Explore.
- [ ] 3.2 Add Wohnort suggestion input from existing creator cities.
- [ ] 3.3 Add Sprache multi-select from controlled language labels.
- [ ] 3.4 Add removable active chips for profile filters.
- [ ] 3.5 Keep Themenkategorie, Thema, Kategorie, and Profilfilter labels distinct.

## Phase 4: Stage 2 Data Model

- [ ] 4.1 Design additive schema for age group, origin country, gender/identity, and consent fields.
- [ ] 4.2 Add normalized creator-language storage and migration from `CreatorDetails.sprache`.
- [ ] 4.3 Add profile-edit controls for age group, languages, origin country, gender/identity, and consent toggles.
- [ ] 4.4 Apply consent rules for sensitive filters.

## Phase 5: Verification

- [ ] 5.1 Run `php -l` on changed PHP files.
- [ ] 5.2 Smoke-test legacy filter URLs.
- [ ] 5.3 Smoke-test repeated-parameter OR behavior within one filter group.
- [ ] 5.4 Smoke-test AND behavior across search, content filters, and profile filters.
- [ ] 5.5 Verify that creators without an active profile-filter value are excluded only when that filter is active.
