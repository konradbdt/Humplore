# Feature Specification: Profile Discovery Filters

## Problem Statement

Humplore already separates broad topic categories, concrete topics, and contribution categories in discovery. Users now need additional profile-level filters such as age group, city, language, origin country, and gender/identity without mixing those profile attributes with topic or post categories.

## User Stories

### Story 1: Filter Feed By Creator Profile Attributes

As a logged-in user, I want to combine free search with profile filters, so that the feed shows posts from creators whose profile attributes match my selected filters.

**Acceptance Criteria:**
- [ ] Free text search remains a search query and is not silently converted into a profile filter.
- [ ] Profile filters apply to creators, and the feed shows posts from matching creators.
- [ ] Missing profile values do not match an active profile filter.
- [ ] Profile filters apply only to creator profiles.

### Story 2: Keep Filter Axes Separate

As a logged-in user, I want clear filter labels, so that I can distinguish content topics, post context, and creator profile attributes.

**Acceptance Criteria:**
- [ ] Topic category filters remain labeled "Themenkategorie".
- [ ] Concrete topic filters remain labeled "Thema" and stay URL/search-compatible.
- [ ] Contribution or life-context filters remain labeled "Kategorie".
- [ ] Profile attributes appear in a separate collapsible "Profilfilter" area.
- [ ] Active profile filters appear as removable chips even when the profile-filter area is closed.

### Story 3: Use Existing Data First

As a product owner, I want the first implementation to use existing profile data where possible, so that discovery improves without a risky migration.

**Acceptance Criteria:**
- [ ] City filtering uses the existing `CreatorDetails.ort` field as current Wohnort.
- [ ] Language filtering can read controlled language labels from the existing `CreatorDetails.sprache` free-text field.
- [ ] Age group, normalized languages, origin country, and gender/identity are planned as a later data-model step.
- [ ] Existing `topic_cat`, `topic`, and `cat` links remain compatible.

### Story 4: Support Multi-Select Filters

As a logged-in user, I want to select multiple values in one filter group, so that I can broaden one axis while keeping other filters strict.

**Acceptance Criteria:**
- [ ] Multiple values within the same filter group use OR semantics.
- [ ] Different filter groups use AND semantics.
- [ ] Multi-select URL parameters use repeated parameter names, such as `profile_language=Deutsch&profile_language=Englisch`.
- [ ] Legacy single-value URLs such as `cat=Alltag` continue to work.

## Non-Functional Requirements

- Compatibility: Existing search behavior and existing `topic`, `topic_cat`, and `cat` URLs MUST continue to work.
- Privacy: Sensitive profile filters such as origin country and gender/identity MUST be optional and based on self-provided data.
- Consent: Sensitive profile filters MUST only be filterable when the creator has explicitly enabled being found by that field.
- Security: User-provided filter values MUST be validated, bound through prepared statements, and escaped in HTML.
- Maintainability: Filter parsing MUST keep profile filters separate from topic-category, topic, and contribution-category parsing.

## Clarifications

### Q1: Can the same word exist on multiple axes?

**Answer**: Yes. For example, Beruf can be a topic category about work experience, while profile filters describe the person. Humplore will not add Beruf as a profile filter.

### Q2: Is Alter a topic category or a profile field?

**Answer**: Both meanings may exist, but profile filtering uses age groups as a profile field. The topic category Alter remains about age-related experiences.

### Q3: What does Ort mean?

**Answer**: Ort means current Wohnort as city or municipality only. It does not mean origin place, region, land, or distance radius.

### Q4: How are filters combined?

**Answer**: Values inside one filter group use OR semantics. Different filter groups and free search use AND semantics.

### Q5: Which filters are first-stage versus migration-stage?

**Answer**: First stage uses existing data for Themenkategorie, Thema, Kategorie, Wohnort, and a controlled transition language filter. Migration-stage adds age group, normalized language multi-select, origin country, gender/identity, and filter-consent fields.

## Target Filter Model

### Content And Post Filters

- `topic_cat`: Themenkategorie, such as Krankheit, Religion, Beruf, Herkunft, Alter, or Geschlecht/Identitaet.
- `topic`: concrete Thema, such as ADHS; preserved for search and URL compatibility.
- `cat`: Beitrags-/Lebenskategorie, such as Alltag, Familie, Hobbys, Schule & Studium, Karriere, or Arbeitsleben.

### Profile Filters

- `profile_city`: current Wohnort as city or municipality.
- `profile_language`: controlled language label; multi-select.
- `profile_age_group`: one of `18-24`, `25-34`, `35-44`, `45-54`, `55-64`, `65+`.
- `profile_origin_country`: Herkunftsland; optional and consent-gated.
- `profile_gender_identity`: fixed selection value; optional and consent-gated.

## Success Metrics

- Users can search for a free term and then narrow by profile filters without losing search behavior.
- The Explore feed and profile suggestions both respect active profile filters.
- Existing topic/category links still resolve.
- UI labels consistently separate Themenkategorie, Thema, Kategorie, and Profilfilter.

## Out Of Scope

- Geolocation, map search, region search, country search for Wohnort, or distance radius.
- Beruf as a profile filter.
- Automatic conversion of search text into structured filters.
- Admin workflows for maintaining taxonomies.
