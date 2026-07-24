# Feature Specification: Question and Comment Reporting

## Problem

Logged-in Humplore users need a way to flag problematic visible questions and comments while preserving the current social and Q&A flows.

## Scope

- Report visible questions.
- Report visible comments.
- Store one report per user and object.
- Keep content visible after reporting.
- Show `Gemeldet` and the inline confirmation after successful report submission.

## Out of Scope

- Reporting posts or profiles.
- Automatic moderation or hiding.
- Admin or moderator roles, review queues, notifications, sanctions, or public report counters.
- Changes to search, filters, profile routing, post actions, saved posts, likes, sharing, commenting, or question-answer behavior beyond the report secondary action.

## Acceptance Criteria

- A logged-in user can report a visible question.
- A logged-in user can report a visible comment.
- Duplicate report attempts by the same user for the same object do not create duplicate rows.
- The reported object remains visible.
- The relevant report control displays `Gemeldet`.
- Existing question, comment, and post action flows continue to work.

## Functional Requirements

- The system MUST accept reports only from the authenticated session user.
- The system MUST support only `question` and `comment` targets.
- The system MUST verify that `question` targets exist in `Questions.id`.
- The system MUST verify that `comment` targets exist in `Comments.id`.
- The system MUST accept only the configured report reasons.
- The system MUST trim and limit optional notes.
- The system MUST require CSRF for writes.
- The system MUST return JSON 401 for unauthenticated Ajax requests.
- The system MUST store new reports with status `open`.
