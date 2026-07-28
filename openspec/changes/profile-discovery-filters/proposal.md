# Proposal: Profile Discovery Filters

## Why

Humplore's discovery model now separates Themenkategorie, Thema, and Kategorie. Users also need profile-level filters such as Wohnort, Sprache, Altersgruppe, Herkunftsland, and Geschlecht/Identitaet without overloading the existing content and post filter axes.

## What Changes

- Add a separate profile-filter axis using `profile_` URL parameters.
- Let profile filters restrict matching creators; feed results show posts from those creators.
- Apply profile filters to profile suggestions and profile search results.
- Support repeated URL parameters for multi-select filters while preserving legacy single-value links.
- Add a collapsed "Profilfilter" UI section and active chips.
- Stage implementation so existing data supports Wohnort and transitional Sprache first.
- Plan additive migration for Altersgruppe, normalized Sprache, Herkunftsland, Geschlecht/Identitaet, and sensitive-field consent.

## Capabilities

### New Capabilities

- **profile-discovery-filters** - Users can filter discovery by creator profile attributes while keeping content topics and post categories separate.

### Modified Capabilities

- **discovery** - Explore feed and profile suggestions are narrowed by active profile filters in addition to existing search, topic-category, topic, and contribution-category filters.

## Impact

- Affects `Webseite - Codex/platform.php`.
- Affects `Webseite - Codex/app/support/platform-page.php`.
- Affects `Webseite - Codex/app/support/search-discovery.php`.
- Later-stage profile editing affects `Webseite - Codex/app/support/profile-page.php` and `Webseite - Codex/app/views/partials/profile-settings-modal.php`.
- No breaking URL changes. Existing `topic_cat`, `topic`, and `cat` links remain compatible.

## Rollback

Remove the profile-filter parsing, query clauses, UI section, and active chips. Existing search, topic-category, topic, and category filtering continue to work because the new profile-filter axis is additive.
