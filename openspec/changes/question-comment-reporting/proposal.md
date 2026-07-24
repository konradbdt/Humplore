# Proposal: Question and Comment Reporting

## Why

Humplore needs a low-friction way for logged-in users to report problematic visible questions and comments without changing public content visibility or adding moderation workflows.

## What Changes

- Add reporting for visible questions and comments.
- Store one report per user and target object with status `open`.
- Keep reported content visible; do not hide, collapse, moderate, or queue it in this change.
- Add small report controls directly on question and comment objects.
- Return JSON from the report handler for Ajax/Fetch workflows.

## Scope

- Logged-in users can report questions.
- Logged-in users can report comments.
- Users may report their own questions and comments.
- Duplicate report attempts for the same user and object do not create a second row.
- UI state changes from `Melden` to `Gemeldet` after report creation or duplicate detection.

## Out of Scope

- Reporting posts or profiles.
- Automatic moderation, deletion, hiding, review queues, admin views, moderator roles, notifications, appeals, or sanctions.
- Public report counters or any public moderation metadata.
- Changes to search, filters, profile routing, posts, categories, likes, saved posts, sharing, commenting, or the question-answer flow beyond the isolated secondary report action.

## Acceptance Criteria

- A logged-in user can report a visible question.
- A logged-in user can report a visible comment.
- The same user cannot create duplicate reports for the same object.
- Reported questions and comments remain visible.
- The relevant report button shows `Gemeldet` after success.
- Existing question, comment, and post action flows remain unchanged.

## Impact

- Adds a `Reports` table to SQL documentation and defensive runtime schema creation.
- Adds shared report helpers for allowed reasons, target validation, and bulk state.
- Adds a dedicated `report_handler.php` JSON handler.
- Updates question/comment partial rendering and page-local JavaScript.
