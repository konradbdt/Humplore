# Search Current Behavior

## Requirements

### Requirement: Exact Search

The system SHALL allow logged-in users to search creator profiles and posts with a text query.

#### Scenario: Direct profile or post match

GIVEN an existing profile or post contains the searched text
WHEN the user submits the query
THEN the matching profile or post is shown.

### Requirement: Limited Empty State

The system MAY show an empty state when no exact `LIKE` match exists.

#### Scenario: No exact match

GIVEN no profile or post contains the searched text
WHEN the user submits the query
THEN the page shows no matching profiles or posts.
