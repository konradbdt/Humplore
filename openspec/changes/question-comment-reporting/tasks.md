# Tasks: Question and Comment Reporting

## 1. Specification

- [x] 1.1 Add OpenSpec change `question-comment-reporting` with capability `moderation`.
- [x] 1.2 Document problem, scope, out-of-scope, and acceptance criteria.

## 2. Persistence and Helpers

- [x] 2.1 Document `Reports` SQL schema.
- [x] 2.2 Add defensive `CREATE TABLE IF NOT EXISTS Reports` runtime schema creation.
- [x] 2.3 Add shared allowed-reason helper.
- [x] 2.4 Add target existence validation for questions and comments.
- [x] 2.5 Add bulk reported-state lookup for rendered questions and comments.

## 3. Handler

- [x] 3.1 Add dedicated POST-only JSON handler.
- [x] 3.2 Require logged-in reporter and return JSON 401 when absent.
- [x] 3.3 Validate CSRF before writing.
- [x] 3.4 Validate target type, target ID, target existence, reason, and note length.
- [x] 3.5 Insert reports with unique duplicate protection and `open` status.
- [x] 3.6 Return minimal success JSON with target identity and `reported: true`.

## 4. UI and JavaScript

- [x] 4.1 Add question report controls in `profile-questions-card.php`.
- [x] 4.2 Add comment report controls in `platform-post-card.php`.
- [x] 4.3 Add comment report controls in `profile-post-card.php`.
- [x] 4.4 Add Fetch behavior with CSRF, button state update, duplicate target synchronization, inline success, and non-misleading failure handling.
- [x] 4.5 Add small restrained CSS for report controls.

## 5. Verification

- [x] 5.1 Run PHP syntax checks on changed PHP files.
- [x] 5.2 Smoke-check question reporting.
- [x] 5.3 Smoke-check comment reporting.
- [x] 5.4 Smoke-check duplicate reporting creates no second row.
- [x] 5.5 Smoke-check reported content remains visible and button shows `Gemeldet`.
- [x] 5.6 Smoke-check comment submission, question ask/answer, learning reaction, comment toggle, save, and share still work.
