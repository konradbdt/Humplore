# Search Delta Specification

## ADDED Requirements

### Requirement: Visible Literal Search Matches

The system SHALL visually highlight literal occurrences of the active search
term in visible matching profile, post, and question text.

#### Scenario: Literal term appears in a result

GIVEN a user searches for `Krebs`
AND a visible result field contains `Krebs`
WHEN the result is rendered
THEN the literal occurrence is displayed with an accessible yellow highlight.

### Requirement: Explain Related Results

The system SHALL distinguish results found only through related terms from
literal search-term matches.

#### Scenario: Related term caused the match

GIVEN a result was found through a related term
AND the entered search term does not occur in the visible result text
WHEN the result is rendered
THEN the actually occurring related term is highlighted
AND the result is labeled `Thematisch verwandt`.

### Requirement: Search-Aware Questions

The system SHALL filter the Explore question rail and its question modal by
the active search context.

#### Scenario: Matching questions exist

GIVEN a user searches for `Krebs`
AND questions match through their question text or the addressed creator's topic
WHEN the Explore page is rendered
THEN the question rail is titled `Passende Fragen`
AND only matching questions are shown
AND visible literal or related terms follow the search highlighting rules.

#### Scenario: No matching question exists

GIVEN a search is active
AND no question matches the search context
WHEN the Explore page is rendered
THEN no unrelated or random question is shown
AND the question rail displays `Keine passenden Fragen gefunden.`

#### Scenario: No search is active

GIVEN no search query is active
WHEN the Explore page is rendered
THEN the existing general question-rail behavior remains available.

### Requirement: Safe Highlight Rendering

The system MUST escape user queries and stored content before adding
highlight markup.

#### Scenario: Search input contains markup

GIVEN a search query or matching stored text contains HTML-like characters
WHEN highlighting is rendered
THEN those characters are displayed as text
AND no executable markup is introduced by the highlighting process.

### Requirement: Bounded Related-Term Scope

The system SHALL NOT imply comprehensive semantic matching when no configured
or discovered relationship exists.

#### Scenario: Unknown semantic relationship

GIVEN a term is conceptually related to the query
BUT the current search logic has no indexed or configured relationship
WHEN the search is executed
THEN the system is not required to return that term as a related match.
