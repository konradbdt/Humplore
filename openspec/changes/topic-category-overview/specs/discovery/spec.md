# Discovery Delta Specification

## ADDED Requirements

### Requirement: Browse Overview Visibility

The system SHALL show a topic and category Browse overview on Explore only when the user is viewing the normal unfiltered Explore feed.

#### Scenario: Normal Explore shows overview

- **GIVEN** a logged-in user opens Explore without a search query
- **AND** no topic filter is active
- **AND** no category filter is active
- **WHEN** the Explore page is rendered
- **THEN** the system shows the Browse overview above the feed.

#### Scenario: Search or filters hide overview

- **GIVEN** a logged-in user opens Explore with a search query or an active topic/category filter
- **WHEN** the Explore page is rendered
- **THEN** the system does not show the Browse overview
- **AND** the search or filtered feed remains the primary content.

### Requirement: Separate Topic And Category Groups

The system SHALL present creator topics and contribution categories as separate groups in the Browse overview.

#### Scenario: Distinct discovery axes

- **GIVEN** topic and contribution category data exists
- **WHEN** the Browse overview is shown
- **THEN** the system renders a topic group section
- **AND** the system renders a contribution category group section
- **AND** topic labels are not mixed into the category group.

### Requirement: Topic Group Previews

The system SHALL show compact preview content for each displayed topic group.

#### Scenario: Topic group shows posts and creators

- **GIVEN** a displayed topic has matching posts and creators
- **WHEN** the Browse overview is shown
- **THEN** the topic group shows up to 2 newest matching posts
- **AND** the topic group shows up to 2 matching creators
- **AND** the topic group links to the existing topic-filtered Explore feed.

### Requirement: Category Group Previews

The system SHALL show compact post preview content for each displayed contribution category group.

#### Scenario: Category group shows posts

- **GIVEN** a displayed contribution category has matching posts
- **WHEN** the Browse overview is shown
- **THEN** the category group shows up to 2 newest matching posts
- **AND** the category group links to the existing category-filtered Explore feed.

### Requirement: Overview Limits

The system SHALL cap the first Browse overview to at most 4 topic groups and 4 contribution category groups.

#### Scenario: More groups exist than fit in overview

- **GIVEN** more than 4 topics or more than 4 contribution categories exist
- **WHEN** the Browse overview is shown
- **THEN** the system shows no more than 4 topic groups
- **AND** the system shows no more than 4 contribution category groups
- **AND** each group provides a path into the corresponding filtered Explore feed.

### Requirement: Future Overview Extraction

The system SHOULD keep Browse overview data behavior reusable so the overview can later move from `platform.php` to a dedicated overview page without changing the user-facing group behavior.

#### Scenario: Overview behavior remains route-independent

- **GIVEN** the Browse overview is implemented inside Explore first
- **WHEN** maintainers later add a dedicated overview route
- **THEN** the topic/category grouping rules and preview limits can be reused without redefining the feature.
