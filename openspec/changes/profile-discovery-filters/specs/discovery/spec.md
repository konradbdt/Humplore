# Delta for Discovery

## ADDED Requirements

### Requirement: Profile Filter Axis

The system SHALL provide profile filters as a separate discovery axis from Themenkategorie, Thema, and Kategorie.

#### Scenario: Profile filters are visually separate

- **GIVEN** the user opens Explore
- **WHEN** profile filters are available
- **THEN** the system presents them in a separate "Profilfilter" area
- **AND** the system does not place profile filters inside Themenkategorie or Kategorie lists.

### Requirement: Profile Filters Restrict Creators

The system SHALL apply active profile filters to creator profiles and show feed posts from matching creators.

#### Scenario: City filter narrows feed by creator

- **GIVEN** a creator has Wohnort "Hamburg"
- **AND** another creator has no Wohnort
- **WHEN** the user filters by `profile_city=Hamburg`
- **THEN** the feed includes posts from the Hamburg creator
- **AND** the feed excludes posts from the creator without a matching Wohnort.

### Requirement: Profile Filters Apply To Profile Suggestions

The system SHALL apply active profile filters to profile suggestions and profile search results.

#### Scenario: Profile suggestions respect profile filters

- **GIVEN** profile suggestions are shown in discovery
- **WHEN** the user activates `profile_city=Hamburg`
- **THEN** suggested profiles match Hamburg
- **AND** non-matching creator profiles are not suggested.

### Requirement: Free Search Combines With Filters

The system SHALL combine free text search with active filters using AND semantics across filter groups.

#### Scenario: Search plus age filter

- **GIVEN** creators and posts match the search term "Feuerwehrmann"
- **WHEN** the user also activates an age-group profile filter
- **THEN** the system shows only results that match the free search and the selected age group.

### Requirement: Multi-Select Filter Semantics

The system SHALL treat multiple values inside one filter group as OR and different filter groups as AND.

#### Scenario: Language multi-select with city filter

- **GIVEN** the user selects `profile_language=Deutsch`
- **AND** the user selects `profile_language=Englisch`
- **AND** the user selects `profile_city=Hamburg`
- **WHEN** the filtered feed loads
- **THEN** matching creators speak Deutsch or Englisch
- **AND** matching creators have Wohnort Hamburg.

### Requirement: Repeated Parameter Compatibility

The system SHALL support repeated URL parameters for multi-select filters while preserving legacy single-value parameters.

#### Scenario: Legacy category link still works

- **GIVEN** an existing link uses `cat=Alltag`
- **WHEN** the user opens the link
- **THEN** the system filters by Kategorie Alltag.

#### Scenario: Repeated category links work

- **GIVEN** a new link uses `cat=Alltag&cat=Familie`
- **WHEN** the user opens the link
- **THEN** the system filters by Kategorie Alltag or Familie.

### Requirement: Sensitive Profile Filter Consent

The system SHALL only filter by sensitive profile fields when the creator has provided the field and enabled being found by that field.

#### Scenario: Origin country filter respects consent

- **GIVEN** a creator has provided an origin country
- **AND** the creator has not enabled filtering by origin country
- **WHEN** the user filters by that origin country
- **THEN** the creator is not included because filter consent is missing.

#### Scenario: Gender identity filter respects consent

- **GIVEN** a creator has provided a gender/identity value
- **AND** the creator has not enabled filtering by gender/identity
- **WHEN** the user filters by that gender/identity value
- **THEN** the creator is not included because filter consent is missing.

#### Scenario: Sensitive consent without value does not match

- **GIVEN** a creator has enabled filtering by origin country
- **AND** the creator has not provided an origin country
- **WHEN** the user filters by an origin country
- **THEN** the creator is not included because the profile value is missing.

### Requirement: Stage 2 Profile Attributes Are Optional

The system SHALL keep age group, normalized languages, origin country, and gender/identity optional for creator profiles.

#### Scenario: Creator saves profile without sensitive fields

- **GIVEN** a creator edits their profile
- **WHEN** the creator leaves age group, languages, origin country, and gender/identity empty
- **THEN** the profile can be saved
- **AND** the creator does not match active filters for those empty fields.

#### Scenario: Prefer not to say does not match identity filters

- **GIVEN** a creator selects "prefer not to say" for gender/identity
- **AND** the creator enables gender/identity filter consent
- **WHEN** the user filters by a gender/identity value
- **THEN** the creator is not included because "prefer not to say" is not a filterable identity value.

### Requirement: Normalized Creator Languages

The system SHALL store creator languages as controlled normalized values while preserving compatibility with existing language filter labels.

#### Scenario: Existing language label URL still filters normalized languages

- **GIVEN** a creator has normalized language Deutsch
- **WHEN** the user opens Explore with `profile_language=Deutsch`
- **THEN** the creator can match the language filter.

#### Scenario: Multiple normalized languages use OR semantics

- **GIVEN** a creator has normalized language Deutsch
- **AND** another creator has normalized language Englisch
- **WHEN** the user filters by `profile_language=Deutsch`
- **AND** the user filters by `profile_language=Englisch`
- **THEN** both creators can match the language filter group.

### Requirement: Legacy Language Text Is Preserved

The system SHALL preserve existing free-text creator language data when normalized languages are introduced.

#### Scenario: Known language is backfilled without deleting free text

- **GIVEN** a creator has legacy language text containing "Deutsch"
- **WHEN** normalized language storage is initialized
- **THEN** the creator has normalized language Deutsch
- **AND** the original legacy language text remains available.

#### Scenario: Unknown language text is not guessed

- **GIVEN** a creator has legacy language text that is not a controlled language label
- **WHEN** normalized language storage is initialized
- **THEN** the system does not create an inferred normalized language from that unknown text
- **AND** the original legacy language text remains available.

### Requirement: Stage 2 Profile Filter URL Compatibility

The system SHALL add Stage 2 profile filters through `profile_` URL parameters without breaking existing discovery filter URLs.

#### Scenario: Age group filter uses profile-prefixed URL parameter

- **GIVEN** a creator has age group `25-34`
- **WHEN** the user opens Explore with `profile_age_group=25-34`
- **THEN** the feed includes posts from creators in that age group
- **AND** excludes creators without a matching age group.

#### Scenario: Origin country filter uses profile-prefixed URL parameter

- **GIVEN** a creator has provided origin country DE
- **AND** the creator has enabled filtering by origin country
- **WHEN** the user opens Explore with `profile_origin_country=DE`
- **THEN** the creator can match the origin-country filter.

#### Scenario: Gender identity filter uses profile-prefixed URL parameter

- **GIVEN** a creator has provided gender/identity value `non_binary`
- **AND** the creator has enabled filtering by gender/identity
- **WHEN** the user opens Explore with `profile_gender_identity=non_binary`
- **THEN** the creator can match the gender/identity filter.

#### Scenario: Existing profile language label URL remains valid

- **GIVEN** an existing link uses `profile_language=Deutsch`
- **WHEN** the user opens the link after normalized language storage is introduced
- **THEN** the system still applies the Deutsch language filter.

### Requirement: Invalid Stage 2 Profile Filter Values Are Not Applied

The system SHALL validate Stage 2 profile filter values against controlled catalogs before applying them.

#### Scenario: Unknown age group does not broaden results

- **GIVEN** the user opens Explore with an unknown age-group filter value
- **WHEN** the system parses profile filters
- **THEN** the unknown age-group value is not applied as a profile filter
- **AND** it is not used as free-text SQL.

#### Scenario: Unknown sensitive filter value is not applied

- **GIVEN** the user opens Explore with an unknown gender/identity filter value
- **WHEN** the system parses profile filters
- **THEN** the unknown gender/identity value is not applied as a profile filter
- **AND** it is not used as free-text SQL.

### Requirement: Existing Filter Compatibility

The system MUST keep existing `topic_cat`, `topic`, and `cat` URLs compatible.

#### Scenario: Existing topic URL still works

- **GIVEN** an existing link uses `topic=ADHS`
- **WHEN** the user opens Explore with that link
- **THEN** the system narrows discovery to the concrete Thema ADHS.
