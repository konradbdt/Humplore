# Proposal: Anonymous Questions

## Why

Humplore promises low-barrier questions to creators, but the current question flow always stores and shows the asking user's username to the creator. Sensitive questions need a visible anonymity option while keeping internal account attribution for abuse handling and future moderation.

## What Changes

- Add per-question anonymous asking for logged-in users.
- Preserve internal `author_id` attribution for anonymous questions.
- Hide the asking user's identity from creators and visitors when a question is anonymous.
- Keep anonymous state immutable after submission.
- Add clear question-form copy confirming that the user's name will not be shown.
- Keep creator opt-out, moderation queues, logged-out anonymous questions, anonymous posts, and profile-field visibility out of this MVP.

## Capabilities

### New Capabilities

- **questions** - Question asking, answering, and question-to-post behavior with per-question anonymity.

### Modified Capabilities

- Existing question storage and display behavior changes to support anonymous display without removing internal account attribution.

## Impact

- Affects `Webseite - Redesign/app/support/profile-actions.php` for question submission.
- Affects `Webseite - Redesign/app/support/profile-page.php` for question loading/display data.
- Affects `Webseite - Redesign/app/views/partials/profile-questions-card.php` for form controls and anonymous labels.
- Affects question schema by adding an additive `Questions.is_anonymous` field.
- May affect question-to-post post creation metadata or copy when answering a question as a post.

## Rollback

Remove the anonymous checkbox and hint from the question form, stop writing `Questions.is_anonymous`, and show the existing author username for all questions.
