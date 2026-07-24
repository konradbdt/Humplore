# Discovery Delta Specification

## ADDED Requirements

### Requirement: Topic Filter

The system SHALL allow logged-in users to filter discovery by creator profile topic.

#### Scenario: Filter feed by creator topic

GIVEN creators have profile topics
WHEN a user selects a topic filter
THEN the feed shows posts from creators whose profile topic matches the selected topic.

### Requirement: Contribution Category Filter

The system SHALL allow logged-in users to filter posts by contribution category independently of creator topic.

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
