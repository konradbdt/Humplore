# Discovery Current Behavior

## Requirements

### Requirement: Topic Filter

The system SHALL allow logged-in users to filter discovery by concrete creator
profile topic.

#### Scenario: Filter feed by creator topic

GIVEN creators have profile topics
WHEN a user opens an existing concrete-topic link
THEN the feed shows posts from creators whose profile topic matches the
selected topic.

### Requirement: Contribution Category Filter

The system SHALL allow logged-in users to filter posts by contribution category
independently of creator topic.

#### Scenario: Filter across topics by contribution category

GIVEN posts have contribution categories
WHEN a user selects a category filter
THEN the feed shows posts in that category across all creator topics.

### Requirement: Combined Discovery Filters

The system SHALL allow topic and contribution category filters to be combined.

#### Scenario: Filter by topic and category

GIVEN a user has selected a creator topic
AND the user has selected a contribution category
WHEN the feed is loaded
THEN only posts matching both filters are shown.

### Requirement: Visible Filter State

The system SHALL show active discovery filters and provide a way to clear them.

#### Scenario: Clear active filters

GIVEN a user has one or more active filters
WHEN the filtered results are shown
THEN the user can remove individual filters or clear all filters.

### Requirement: Topic Category Filter

The system SHALL allow logged-in users to filter discovery by broad topic
category.

#### Scenario: Filter by broad topic category

GIVEN a creator has the concrete topic "ADHS"
AND "ADHS" belongs to the topic category "Krankheit"
WHEN a user selects the topic category "Krankheit"
THEN the feed includes posts from that creator.

### Requirement: Separate Topic And Category Meaning

The system SHALL keep topic categories, concrete topics, and contribution
categories separate in UI labels and filter state.

#### Scenario: Distinct active filters

GIVEN a user has selected a topic category filter
AND a contribution category filter
WHEN active filters are displayed
THEN the topic-category filter is labeled "Themenkategorie"
AND the contribution category filter is labeled "Kategorie".

### Requirement: Legacy Concrete Topic Compatibility

The system SHALL preserve existing concrete topic filtering as a compatible
subfilter behavior.

#### Scenario: Existing topic URL still narrows results

GIVEN an existing link uses `topic=ADHS`
WHEN the Explore page is loaded
THEN the feed remains narrowed to creators whose concrete topic is ADHS
AND the primary UI does not present ADHS as a main topic-category option.

### Requirement: Contribution Category Independence

The system SHALL keep contribution category filtering independent from topic
category filtering.

#### Scenario: Filter life-context category across topic categories

GIVEN posts exist in the contribution category "Alltag"
WHEN a user selects the category "Alltag"
THEN matching posts can come from any topic category.

