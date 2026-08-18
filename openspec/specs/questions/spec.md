# Questions Current Behavior

## ADDED Requirements

### Requirement: Per-Question Anonymous Asking

The system SHALL allow a logged-in user to mark an individual question to a creator as anonymous at submission time.

#### Scenario: User submits a normal question

- **GIVEN** a logged-in user views another user's creator profile
- **WHEN** the user submits a question without selecting anonymous asking
- **THEN** the system stores the question as not anonymous
- **AND** the system keeps the existing author account relationship
- **AND** the system confirms that the question was sent.

#### Scenario: User submits an anonymous question

- **GIVEN** a logged-in user views another user's creator profile
- **WHEN** the user selects anonymous asking and submits a question
- **THEN** the system stores the question as anonymous
- **AND** the system keeps the existing author account relationship internally
- **AND** the system confirms that the anonymous question was sent.

#### Scenario: Anonymous state is selected per question

- **GIVEN** a logged-in user has submitted one anonymous question
- **WHEN** the same user submits another question without selecting anonymous asking
- **THEN** the second question is stored as not anonymous
- **AND** the first question remains anonymous.

### Requirement: Anonymous Question Form Copy

The system SHALL present anonymous asking as an explicit per-question control with concise copy explaining that the user's name will not be shown.

#### Scenario: User sees anonymous option

- **GIVEN** a logged-in user can ask a question on a creator profile
- **WHEN** the question form is rendered
- **THEN** the form shows a selectable `Anonym fragen` option
- **AND** the option is not selected by default
- **AND** the form shows concise helper copy that the user's name will not be shown to the creator or other users.

### Requirement: Anonymous Display to Creator

The system SHALL hide the asking user's identity from the creator when the question is anonymous.

#### Scenario: Creator views anonymous question

- **GIVEN** a creator receives an anonymous question
- **WHEN** the creator views their questions list
- **THEN** the author label is shown as `Anonym`
- **AND** the system does not show the asking user's username, profile image, profile link, or stable anonymous pseudonym.

#### Scenario: Creator views normal question

- **GIVEN** a creator receives a question that was not marked anonymous
- **WHEN** the creator views their questions list
- **THEN** the system may show the asking user's username using the existing question author behavior.

### Requirement: Anonymous Display to Visitors

The system SHALL avoid exposing question author identity to visitors for answered question previews.

#### Scenario: Visitor sees answered anonymous question

- **GIVEN** an anonymous question has been answered
- **WHEN** another user views the creator profile answered-question preview
- **THEN** the preview shows the question and answer
- **AND** the preview does not show the asking user's identity.

#### Scenario: Visitor sees answered normal question

- **GIVEN** a non-anonymous question has been answered
- **WHEN** another user views the creator profile answered-question preview
- **THEN** the preview shows the question and answer
- **AND** the preview does not newly expose the asking user's identity.

### Requirement: Internal Attribution Preservation

The system MUST preserve internal account attribution for anonymous questions.

#### Scenario: Anonymous question remains attributable internally

- **GIVEN** a logged-in user submits an anonymous question
- **WHEN** the question is stored
- **THEN** the system retains the author account relationship internally
- **AND** the visible creator and visitor UI treats the question as anonymous.

### Requirement: Immutable Anonymous State

The system SHALL treat a question's anonymous state as final after submission.

#### Scenario: User cannot change anonymous state after sending

- **GIVEN** a user has submitted a question
- **WHEN** the question has been accepted by the system
- **THEN** the user cannot change that question from anonymous to visible
- **AND** the user cannot change that question from visible to anonymous.

### Requirement: Anonymous Question-to-Post Handling

The system SHALL preserve question anonymity when a creator answers a question as a post.

#### Scenario: Anonymous question becomes a post

- **GIVEN** a creator answers an anonymous question using the post answer mode
- **WHEN** the post is created
- **THEN** the public post does not show the asking user's identity
- **AND** the post context may indicate that the source was an anonymous or community question.

#### Scenario: Non-anonymous question becomes a post

- **GIVEN** a creator answers a non-anonymous question using the post answer mode
- **WHEN** the post is created
- **THEN** the public post does not automatically show the asking user's identity
- **AND** public author attribution requires a later explicit consent model.

### Requirement: Anonymous Question MVP Boundaries

The system MUST keep the first anonymous-question implementation scoped to logged-in per-question anonymity.

#### Scenario: Logged-out visitor attempts anonymous asking

- **GIVEN** a visitor is not logged in
- **WHEN** the visitor attempts to submit an anonymous question
- **THEN** the system rejects the request using the existing authentication requirement
- **AND** no question is created.

#### Scenario: Creator cannot opt out in MVP

- **GIVEN** a creator profile can receive questions
- **WHEN** the anonymous-question MVP is implemented
- **THEN** the system does not provide a creator setting to disable anonymous questions.

#### Scenario: No moderation queue in MVP

- **GIVEN** a logged-in user submits an anonymous question
- **WHEN** the question is accepted by the system
- **THEN** the question appears through the same delivery flow as normal questions
- **AND** the system does not require creator approval before the creator can see it.
