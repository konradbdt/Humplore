# Moderation Current Behavior

## Requirements

### Requirement: Report Visible Questions

The system SHALL allow a logged-in user to report a visible question without
changing the question's visibility.

#### Scenario: User reports a question

GIVEN a logged-in user views a visible question
WHEN the user submits a valid report reason for that question
THEN the system stores an open report for that user and question
AND the question remains visible
AND the UI shows `Gemeldet`
AND the UI shows `Danke, wir haben deine Meldung erfasst.`

### Requirement: Report Visible Comments

The system SHALL allow a logged-in user to report a visible comment without
changing the comment's visibility.

#### Scenario: User reports a comment

GIVEN a logged-in user views a visible comment
WHEN the user submits a valid report reason for that comment
THEN the system stores an open report for that user and comment
AND the comment remains visible
AND the UI shows `Gemeldet`
AND the UI shows `Danke, wir haben deine Meldung erfasst.`

### Requirement: Report Validation

The system SHALL validate report writes before persistence.

#### Scenario: Invalid report target is rejected

GIVEN a logged-in user submits a report
WHEN the target type is not `question` or `comment`
THEN the system rejects the report
AND no report row is created.

#### Scenario: Missing target is rejected

GIVEN a logged-in user submits a report for a valid target type
WHEN the target ID does not exist in the corresponding table
THEN the system rejects the report
AND no report row is created.

#### Scenario: Invalid reason is rejected

GIVEN a logged-in user submits a report
WHEN the reason is not one of the allowed report reasons
THEN the system rejects the report
AND no report row is created.

#### Scenario: CSRF is required

GIVEN a logged-in user submits a report
WHEN the request omits CSRF or sends an invalid CSRF token
THEN the system rejects the report
AND no report row is created.

#### Scenario: Unauthenticated Ajax report is rejected as JSON

GIVEN a report request has no authenticated user
WHEN the report handler receives the request
THEN the system returns HTTP 401 with JSON
AND the system does not redirect to login.

### Requirement: Duplicate Report Prevention

The system SHALL allow at most one report per reporter and target object.

#### Scenario: Duplicate report attempt

GIVEN a logged-in user has already reported a question or comment
WHEN the same user reports the same object again
THEN the system does not create a second report row
AND the UI still shows `Gemeldet`.

### Requirement: Reporting Is Isolated

The system SHALL keep reporting isolated from existing post, question, comment,
and engagement flows.

#### Scenario: Existing actions remain available

GIVEN a user reports a question or comment
WHEN the page continues to render
THEN existing question asking and answering remains available
AND existing comment submission remains available
AND existing `Neues gelernt!`, `Kommentieren`, `Merken`, and `Teilen` post
actions remain available
AND the system does not render a public report count.

