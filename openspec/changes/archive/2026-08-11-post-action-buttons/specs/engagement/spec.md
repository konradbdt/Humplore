# Engagement Delta Specification

## ADDED Requirements

### Requirement: Post Action Toolbar Composition

The system SHALL render a consistent post action toolbar below post content with the first-iteration actions `Neues gelernt!`, `Kommentieren`, `Merken`, and `Teilen` in that order.

#### Scenario: Post action row shows required actions

- **GIVEN** a logged-in user views a post card in Explore, search results, or a profile
- **WHEN** the post card is rendered
- **THEN** the action row shows `Neues gelernt!`
- **AND** the action row shows `Kommentieren`
- **AND** the action row shows `Merken`
- **AND** the action row shows `Teilen`
- **AND** the actions appear in the same order across post-card contexts.

#### Scenario: Modal action row stays consistent

- **GIVEN** a logged-in user opens a post modal that renders post actions
- **WHEN** the modal is displayed
- **THEN** the action row uses the same action set and order as the post card.

#### Scenario: Mobile action row uses icons only

- **GIVEN** a logged-in user views a post card on a small screen
- **WHEN** the post action row is rendered
- **THEN** the system shows icon-only actions
- **AND** the system keeps counts visible for `Neues gelernt!` and `Kommentieren`
- **AND** the system shows no counts for `Merken` or `Teilen`
- **AND** each action still has an accessible label.

### Requirement: Learning Reaction Label

The system SHALL present the existing learning reaction as `Neues gelernt!` with a lightbulb icon while preserving the current reaction toggle behavior and public count.

#### Scenario: User toggles learning reaction

- **GIVEN** a logged-in user sees a post with the `Neues gelernt!` action
- **WHEN** the user activates the action
- **THEN** the system toggles the user's learning reaction for that post
- **AND** the public count/state behavior remains compatible with the previous `Wissenswert` action
- **AND** paid/locked post behavior remains unchanged.

### Requirement: Comment Entry

The system SHALL provide a visible `Kommentieren` action with a speech bubble icon and the existing comment count that opens or focuses the post comment surface.

#### Scenario: User opens comment surface

- **GIVEN** a logged-in user sees a post with the `Kommentieren` action
- **WHEN** the user activates the action
- **THEN** the system opens or focuses the existing comment area for that post
- **AND** the system keeps the existing comment count visible
- **AND** the visible label is `Kommentieren`, not `Kommentar`
- **AND** the system does not introduce a separate question mode for posts.

### Requirement: Remember Post Action

The system SHALL allow a logged-in user to privately mark and unmark a post with `Merken` from the post action toolbar, using a bookmark/lesezeichen icon.

#### Scenario: User remembers a post

- **GIVEN** a logged-in user sees an unsaved post
- **WHEN** the user activates the `Merken` action
- **THEN** the system sends an Ajax/Fetch toggle request
- **AND** the request is handled by `save_post_handler.php`, separate from `like_handler.php`
- **AND** the handler validates CSRF before mutating state
- **AND** the system persists the saved relationship between the user and the post
- **AND** the handler returns JSON with `post_id` and `saved: true`
- **AND** the action shows a marked state to that user
- **AND** any other rendered `Merken` button for the same `post_id` also shows the marked state
- **AND** the system does not show a success toast
- **AND** the system does not show a public `Merken` count.

#### Scenario: Logged-out visitors do not use Merken

- **GIVEN** a visitor is not logged in
- **WHEN** post cards are rendered outside the logged-in experience
- **THEN** the system does not offer `Merken` as an actionable post control.

#### Scenario: Unauthenticated Merken request is rejected as JSON

- **GIVEN** a request to `save_post_handler.php` has no authenticated user
- **WHEN** the handler receives the request
- **THEN** the system returns HTTP 401 with a JSON response
- **AND** the system does not redirect to login.

#### Scenario: User unmarks a post

- **GIVEN** a logged-in user sees a saved post
- **WHEN** the user activates the `Merken` action
- **THEN** the system sends an Ajax/Fetch toggle request
- **AND** the request is handled by `save_post_handler.php`, separate from `like_handler.php`
- **AND** the handler validates CSRF before mutating state
- **AND** the system removes the saved relationship between the user and the post
- **AND** the handler returns JSON with `post_id` and `saved: false`
- **AND** the action shows an unmarked state to that user
- **AND** any other rendered `Merken` button for the same `post_id` also shows the unmarked state
- **AND** the system does not show a success toast.

#### Scenario: Duplicate remembered records are prevented

- **GIVEN** a logged-in user has already saved a post
- **WHEN** the `Merken` operation is triggered again for the same post
- **THEN** the system does not create duplicate saved records.

#### Scenario: CSRF is required for Merken writes

- **GIVEN** a logged-in user request to toggle `Merken` omits or sends an invalid CSRF token
- **WHEN** `save_post_handler.php` receives the request
- **THEN** the system rejects the request
- **AND** no saved-post state is changed.

#### Scenario: User remembers a locked post

- **GIVEN** a logged-in user sees a paid or locked post card
- **WHEN** the user activates the `Merken` action
- **THEN** the system can mark the post for that user
- **AND** the system does not unlock or reveal locked content.

#### Scenario: Remembered-post schema is ensured safely

- **GIVEN** the application starts in a brownfield database
- **WHEN** the schema helper runs
- **THEN** the system ensures the `SavedPosts` relationship table exists using additive schema behavior
- **AND** the schema contains only `user_id`, `post_id`, and `created_at` for this change
- **AND** the schema is documented in SQL for maintainers.

### Requirement: Share Action Preservation

The system SHALL preserve the existing post share behavior and continue to present share as `Teilen` with the molecule-style icon.

#### Scenario: User shares a post

- **GIVEN** a logged-in user sees a post with the `Teilen` action
- **WHEN** the user activates the action
- **THEN** the system uses the existing share behavior for that post.

### Requirement: Future Donation Extension

The system SHOULD reserve the post action model for a later post-level Donation action, but MUST NOT render a disabled or non-functional Donation action in this change.

#### Scenario: Donation is not implemented yet

- **GIVEN** post-level donation behavior has not been specified or implemented
- **WHEN** a post card is rendered
- **THEN** the action row does not show a Donation action
- **AND** the implemented actions remain usable without implying donation support.
