# Design: Profile Discovery Filters

## Technical Approach

Add profile filters as a separate discovery axis. Existing content/post filters remain unchanged:

- `topic_cat`: Themenkategorie.
- `topic`: concrete Thema, kept for URL/search compatibility.
- `cat`: Beitrags-/Lebenskategorie.
- `profile_*`: creator profile attributes.

Profile filters are applied to creator rows. Feed queries then return posts from matching creators. Profile result queries return matching creators directly.

## Architecture Decisions

### Decision: Profile Filter URL Prefix

Use `profile_` parameters for all profile filters.

**Rationale:**
- Keeps profile filters visibly separate from `topic_cat`, `topic`, and `cat`.
- Avoids future ambiguity when labels such as Herkunft or Geschlecht/Identitaet exist both as topic categories and profile attributes.
- Makes active chips and query parsing easier to audit.

### Decision: Repeated Parameters For Multi-Select

Use repeated parameters such as `profile_language=Deutsch&profile_language=Englisch`.

**Rationale:**
- Browser forms and checkboxes map naturally to repeated parameters.
- Values do not need comma parsing.
- Legacy single-value parameters can be normalized into one-item lists.

### Decision: Two-Stage Data Rollout

Stage 1 uses existing fields for Wohnort and transitional Sprache. Stage 2 adds structured profile attributes and consent fields.

**Rationale:**
- Current data already includes `CreatorDetails.ort` and `CreatorDetails.sprache`.
- Age group, origin country, and gender/identity do not exist yet and need privacy-conscious schema design.
- Sensitive filters require explicit creator consent before filtering.

## Current Data

- `CreatorDetails.ort`: current Wohnort free text; usable for city/municipality exact matching after normalization.
- `CreatorDetails.sprache`: language free text; usable only through controlled label matching as a transition.
- `CreatorDetails.main_topic` and `Users.main_topic`: concrete Thema.
- `Posts.category`, `Categories`, `PostCategories`: Beitrags-/Lebenskategorie.

## Target Data

- `profile_city`: current Wohnort city/municipality.
- `profile_language`: normalized creator-language relation.
- `profile_age_group`: fixed age group values: `18-24`, `25-34`, `35-44`, `45-54`, `55-64`, `65+`.
- `profile_origin_country`: Herkunftsland, optional and consent-gated.
- `profile_gender_identity`: fixed selection value, optional and consent-gated.

## Stage 2 Profile Attribute Model

Stage 2 remains additive. Existing `CreatorDetails` data and existing filter URLs are not removed or repurposed.

### Additive `CreatorDetails` Columns

Add single-value profile attributes directly to `CreatorDetails`:

- `profile_age_group TEXT NULL`
- `profile_origin_country TEXT NULL`
- `profile_origin_country_filter_enabled INTEGER NOT NULL DEFAULT 0`
- `profile_gender_identity TEXT NULL`
- `profile_gender_identity_filter_enabled INTEGER NOT NULL DEFAULT 0`

`profile_origin_country` stores an ISO-3166-1-alpha-2 country code. `profile_gender_identity` stores a controlled identity code. All new profile fields are optional; no creator must provide age group, origin country, gender/identity, or normalized languages.

### Normalized Creator Languages

Add normalized language storage as a separate relation because creators can have multiple languages:

```sql
CREATE TABLE IF NOT EXISTS CreatorLanguages (
  user_id INTEGER NOT NULL,
  language_code TEXT NOT NULL,
  source_label TEXT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, language_code)
)
```

`language_code` uses stable controlled codes such as `de`, `en`, `fr`, `es`, `it`, `tr`, `ar`, `ru`, `pl`, `uk`, `pt`, and `nl`. UI labels remain German labels such as `Deutsch` and `Englisch`. Stage 2 must keep existing `profile_language=Deutsch` URLs valid by mapping labels to codes during parsing.

`CreatorDetails.sprache` remains the legacy free-text source during and after Stage 2. It must not be deleted, overwritten, or used as the only storage once normalized languages exist.

### Controlled Value Catalogs

Stage 2 filterable profile attributes use controlled catalogs only:

- Age groups: `18-24`, `25-34`, `35-44`, `45-54`, `55-64`, `65+`.
- Languages: stable internal language codes mapped from controlled German labels.
- Origin country: ISO-3166-1-alpha-2 code, displayed as a localized country label in UI.
- Gender/identity: controlled codes such as `female`, `male`, `non_binary`, `trans`, `queer`, `self_describe`, and `prefer_not_to_say`.

Free-text values are not accepted as new filter values for age group, origin country, or gender/identity. Empty values and `prefer_not_to_say` do not match active discovery filters.

## Stage 2 Migration And Backfill

Schema setup follows the existing defensive schema helper pattern:

- Check `PRAGMA table_info(CreatorDetails)` before each `ALTER TABLE`.
- Use `CREATE TABLE IF NOT EXISTS CreatorLanguages`.
- Make the schema setup idempotent so a second request can run it without failing.

Backfill from `CreatorDetails.sprache` is conservative:

- Read the legacy free-text field.
- Split on common separators such as comma, semicolon, slash, pipe, newline, and repeated whitespace where safe.
- Match only known controlled language labels, aliases, and codes.
- Insert recognized values into `CreatorLanguages` with `INSERT OR IGNORE`.
- Preserve the original `CreatorDetails.sprache` value unchanged, including unknown free-text fragments.
- Do not infer languages from ambiguous or unknown text.

The transition rule is: normalized language filters should prefer `CreatorLanguages` after Stage 2, while legacy free text remains available for display and rollback.

## Stage 2 Consent Rules

Origin country and gender/identity are sensitive filter fields. A creator must provide both a value and explicit filter consent before they can match a sensitive discovery filter.

- `profile_origin_country` matches only when `profile_origin_country` is non-empty and `profile_origin_country_filter_enabled = 1`.
- `profile_gender_identity` matches only when `profile_gender_identity` is non-empty, not `prefer_not_to_say`, and `profile_gender_identity_filter_enabled = 1`.
- Missing value plus enabled consent does not match.
- Present value plus missing/disabled consent does not match.
- Consent toggles default to off for existing and new creators.

Stage 2 consent means "findable through discovery filters". It does not automatically mean that sensitive fields are prominently displayed on public profiles. Public display of sensitive fields requires a separate product decision.

## Stage 2 URL Contract

New profile filters continue to use repeated `profile_` parameters:

- `profile_age_group=25-34`
- `profile_language=Deutsch&profile_language=Englisch`
- `profile_origin_country=DE`
- `profile_gender_identity=non_binary`

Repeated values within one filter group use OR semantics. Different filter groups use AND semantics. Existing `topic_cat`, `topic`, `cat`, `profile_city`, and label-based `profile_language` URLs remain valid. Stage 2 does not convert free search text into structured filters.

Unknown Stage 2 URL filter values are rejected or ignored by the parser before SQL generation. User-provided values must be validated against the controlled catalogs and bound through prepared statements.

## Stage 2 Profile Editing UI

Extend the existing profile settings modal instead of adding a separate settings page. Add a "Profilattribute" section with:

- Optional age-group select.
- Controlled language multi-select backed by `CreatorLanguages`.
- Optional origin-country select plus separate "In Profilfiltern auffindbar machen" toggle.
- Optional gender/identity select plus separate "In Profilfiltern auffindbar machen" toggle.

Bio and profile image behavior remain unchanged. Sensitive fields are not required. When a sensitive value is cleared, its matching filter-consent flag must be stored as disabled. Consent without a stored sensitive value must not become effective.

## Stage 2 Discovery Querying

The existing Stage 1 profile filter helpers should be extended without changing existing content/post filter behavior:

- Age group filters compare against `CreatorDetails.profile_age_group`.
- Language filters match creators through `CreatorLanguages.language_code`.
- Origin-country filters compare against `CreatorDetails.profile_origin_country` and require origin-country filter consent.
- Gender/identity filters compare against `CreatorDetails.profile_gender_identity` and require gender/identity filter consent.

Profile filters continue to restrict creators. Feed queries return posts from matching creators. Profile search and profile suggestions return matching creators directly.

## Stage 2 Privacy And Rollback

Privacy risks:

- Sensitive origin or identity fields could expose creators if values are filterable without explicit consent.
- Free-text migration could incorrectly infer languages from ambiguous text.
- URL filters could leak or persist sensitive intent in shared links.

Mitigations:

- Consent defaults to off and is required in SQL for sensitive filters.
- New sensitive profile fields are optional.
- Backfill only recognizes controlled language values and preserves legacy free text unchanged.
- Unknown URL values are rejected before SQL generation.
- Sensitive values are not automatically promoted as public profile display fields in Stage 2.

Rollback is logical and non-destructive:

- Disable or remove Stage 2 parser, UI, and query clauses.
- Leave additive `CreatorDetails` columns and `CreatorLanguages` unused in the database.
- Keep `CreatorDetails.sprache` as the legacy language source.
- Optional cleanup scripts may drop `CreatorLanguages`, but SQLite column removal is not required for rollback.

## Query Semantics

- OR within one filter group.
- AND across filter groups.
- Free text search AND active filters.
- Missing profile values do not match active profile filters.
- Profile filters apply only to creators.

## UI Behavior

- Add a collapsed "Profilfilter" section.
- Keep the section closed by default.
- Show active profile-filter chips near the feed.
- Wohnort uses a suggestion input sourced from existing creator cities.
- Sprache uses controlled multi-select labels.
- Beruf is not a profile filter. Beruf remains a Themenkategorie, and contribution contexts should use labels such as Karriere or Arbeitsleben.

## File Changes

- `Webseite - Codex/app/support/platform-page.php` - parse list values, load profile filter options, generate profile-filter SQL.
- `Webseite - Codex/app/support/search-discovery.php` - apply profile filters to profile and post searches.
- `Webseite - Codex/platform.php` - render profile-filter UI and active chips.
- `Webseite - Codex/app/support/profile-page.php` - later-stage read support for new fields.
- `Webseite - Codex/app/views/partials/profile-settings-modal.php` - later-stage editing controls.
- `Webseite - Codex/app/support/helpers.php` - later-stage additive schema setup and language backfill.
- `Webseite - Codex/app/support/profile-actions.php` - later-stage validation and persistence for profile attributes and consent.

## Risks

- Free-text city and language data may be inconsistent. Mitigation: normalize city matching and restrict language values to controlled labels.
- Sensitive filters may expose personal attributes. Mitigation: optional fields plus explicit filter consent.
- URL parsing may regress legacy links. Mitigation: normalize single and repeated values through shared helpers and smoke-test legacy URLs.
- Stage 2 language backfill may miss unknown free-text language data. Mitigation: preserve `CreatorDetails.sprache` unchanged and allow creators to select normalized languages later.
- SQLite rollback for added columns is awkward. Mitigation: use logical rollback and leave additive columns unused.

## Verification Strategy

- Run `php -l` on every changed PHP file.
- Smoke-test schema setup: new columns exist, `CreatorLanguages` exists, and running schema setup twice is harmless.
- Smoke-test language backfill: known legacy labels create normalized language rows and legacy free text remains unchanged.
- Smoke-test query semantics for `profile_age_group`, normalized `profile_language`, `profile_origin_country`, and `profile_gender_identity`.
- Smoke-test consent gates: sensitive fields match only with both value and consent enabled.
- Smoke-test legacy URL compatibility for `topic_cat`, `topic`, `cat`, `profile_city`, and label-based `profile_language`.
- Smoke-test profile save behavior: new fields are optional and clearing a sensitive value disables effective filter consent.
