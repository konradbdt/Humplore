# Tasks

## 1. Specification

- [x] 1.1 Capture clarified profile-filter decisions.
- [x] 1.2 Create OpenSpec proposal.
- [x] 1.3 Create OpenSpec design.
- [x] 1.4 Create OpenSpec delta spec.

## 2. Stage 1 Implementation

- [x] 2.1 Normalize legacy single values and repeated URL parameters into filter lists.
- [x] 2.2 Add `profile_city` filter using `CreatorDetails.ort`.
- [x] 2.3 Add controlled `profile_language` filter using `CreatorDetails.sprache`.
- [x] 2.4 Apply profile filters to Explore feed creator matching.
- [x] 2.5 Apply profile filters to profile search and suggestions.
- [x] 2.6 Preserve profile filters through search, sort, pagination, mode links, and clear actions.

## 3. Stage 1 UI

- [x] 3.1 Add collapsed "Profilfilter" section to Explore.
- [x] 3.2 Add Wohnort suggestion input from existing creator cities.
- [x] 3.3 Add Sprache multi-select.
- [x] 3.4 Add active profile-filter chips.
- [x] 3.5 Verify labels keep Themenkategorie, Thema, Kategorie, and Profilfilter separate.

## 4. Stage 2 Data Model And Filtering

- [ ] 4.1 Add defensive schema setup for nullable `CreatorDetails.profile_age_group`, `CreatorDetails.profile_origin_country`, `CreatorDetails.profile_gender_identity`, and consent defaults for origin country and gender/identity.
- [ ] 4.2 Add `CreatorLanguages(user_id, language_code, source_label, created_at)` with primary key `(user_id, language_code)` through the existing schema-helper pattern.
- [ ] 4.3 Define controlled catalogs for age groups, language code/label mapping, origin-country codes, and gender/identity codes.
- [ ] 4.4 Add an idempotent backfill from `CreatorDetails.sprache` to `CreatorLanguages` that preserves the legacy free-text field unchanged.
- [ ] 4.5 Extend filter parsing for `profile_age_group`, normalized `profile_language`, `profile_origin_country`, and `profile_gender_identity` while preserving existing label-based language URLs.
- [ ] 4.6 Extend filter URL building, hidden form preservation, sorting links, pagination links, and active chips for the new Stage 2 profile filters.
- [ ] 4.7 Extend Explore feed queries so the new profile filters restrict matching creators and then return posts from those creators.
- [ ] 4.8 Extend profile search and profile suggestions so the new profile filters apply to creator rows.
- [ ] 4.9 Enforce sensitive-field consent in every origin-country and gender/identity filter query.
- [ ] 4.10 Extend the existing profile settings modal with optional age group, controlled languages, optional origin country plus filter-consent toggle, and optional gender/identity plus filter-consent toggle.
- [ ] 4.11 Extend profile save handling to validate controlled values, store normalized languages, keep sensitive fields optional, and disable effective consent when a sensitive value is empty.
- [ ] 4.12 Document the Stage 2 logical rollback path: disable parser/UI/query code, leave additive schema unused, and keep `CreatorDetails.sprache` as legacy data.

## 5. Verification

- [x] 5.1 Run PHP syntax checks.
- [x] 5.2 Smoke-test existing `topic_cat`, `topic`, and `cat` links.
- [x] 5.3 Smoke-test multi-select OR behavior within one filter group.
- [x] 5.4 Smoke-test AND behavior across search and filter groups.
- [x] 5.5 Verify creators missing active profile-filter values are excluded from filtered results.
- [ ] 5.6 Run `php -l` on every PHP file changed for Stage 2.
- [ ] 5.7 Smoke-test Stage 2 schema setup: new columns and `CreatorLanguages` exist, and schema setup is idempotent.
- [ ] 5.8 Smoke-test language backfill: recognized legacy language labels create normalized codes and `CreatorDetails.sprache` remains unchanged.
- [ ] 5.9 Smoke-test `profile_age_group` matching and missing-value exclusion.
- [ ] 5.10 Smoke-test normalized `profile_language` matching from existing label URLs such as `profile_language=Deutsch`.
- [ ] 5.11 Smoke-test `profile_origin_country`: value plus consent matches, value without consent does not match, consent without value does not match.
- [ ] 5.12 Smoke-test `profile_gender_identity`: value plus consent matches, value without consent does not match, `prefer_not_to_say` does not match.
- [ ] 5.13 Smoke-test that legacy `topic_cat`, `topic`, `cat`, `profile_city`, and Stage 1 label-based `profile_language` URLs remain compatible.
- [ ] 5.14 Smoke-test profile editing: all new sensitive fields are optional, invalid controlled values are rejected, and clearing a sensitive value disables effective filter consent.
