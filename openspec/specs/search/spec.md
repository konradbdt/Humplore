# Search Current Behavior

## Requirements

### Requirement: Exact Search

The system SHALL allow logged-in users to search creator profiles, creator
topics, post titles, post content, and post categories with a text query.

#### Scenario: Direct profile or post match

GIVEN an existing profile or post contains the searched text
WHEN the user submits the query
THEN the matching profile or post is shown.

### Requirement: Typo-Tolerant Discovery

The system SHOULD return related profiles or posts when a submitted query is
close to an indexed term and direct results are weak.

#### Scenario: Near miss query

GIVEN a user submits a misspelled topic
AND a close topic, category, username, or title term exists
WHEN direct search has few or no results
THEN the system shows results matched through related terms.

### Requirement: Search Suggestions

The system SHALL provide suggested search terms when direct results are weak or
empty and related terms exist.

#### Scenario: Suggested continuation

GIVEN a user submits a query with weak or empty results
WHEN related terms exist in creators, categories, or posts
THEN the system shows a short list of clickable suggestions.

### Requirement: Guided Empty State

The system MAY show an empty state only when neither direct matches, related
matches, nor useful suggestions exist.

#### Scenario: No useful result

GIVEN no direct or related profile or post matches the searched text
AND no useful suggestion exists
WHEN the user submits the query
THEN the page shows a clear empty state.

