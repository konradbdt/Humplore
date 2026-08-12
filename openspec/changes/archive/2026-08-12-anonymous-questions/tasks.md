# Tasks: Anonymous Questions

## 1. Foundation

- [x] 1.1 Locate all active question submission and display paths.
- [x] 1.2 Confirm whether a defensive database schema helper already manages additive columns.
- [x] 1.3 Add or document `Questions.is_anonymous INTEGER NOT NULL DEFAULT 0`.
- [x] 1.4 Ensure existing questions default to non-anonymous.

## 2. Backend

- [x] 2.1 Extend question submission to read a per-question anonymous checkbox.
- [x] 2.2 Preserve `author_id` for anonymous and non-anonymous questions.
- [x] 2.3 Store anonymous questions with `is_anonymous = 1` and normal questions with `is_anonymous = 0`.
- [x] 2.4 Return distinct success copy for anonymous question submission.
- [x] 2.5 Load `is_anonymous` wherever questions are rendered.
- [x] 2.6 Ensure question-to-post answering does not publish the question author's username.

## 3. UI

- [x] 3.1 Add an unchecked `Anonym fragen` checkbox to the creator question form.
- [x] 3.2 Add concise helper copy explaining that the user's name will not be shown to the creator or other users.
- [x] 3.3 Render anonymous creator-facing author labels as exactly `Anonym`.
- [x] 3.4 Keep normal creator-facing author labels using the existing username behavior.
- [x] 3.5 Do not render profile links, profile images, stable anonymous IDs, or grouping markers for anonymous question authors.
- [x] 3.6 Keep visitor answered-question previews free of question author identity.

## 4. Verification

- [x] 4.1 Run PHP syntax checks on changed PHP files.
- [x] 4.2 Verify normal question submission stores `is_anonymous = 0` and shows the current success message.
- [x] 4.3 Verify anonymous question submission stores `is_anonymous = 1` and shows anonymous success copy.
- [x] 4.4 Verify the creator sees `Anonym` for anonymous questions and the username for non-anonymous questions.
- [x] 4.5 Verify visitors do not see question author identity in answered-question previews.
- [x] 4.6 Verify anonymous questions still retain internal `author_id`.
- [x] 4.7 Verify users cannot change anonymous state after submission.
- [x] 4.8 Verify logged-out requests cannot create anonymous questions.
- [x] 4.9 Verify answering an anonymous question as a post does not expose the question author's username.

Verification note (2026-08-12): All mutating checks used an isolated copy of
`Webseite - Codex/data/database.db`; the repository database was not changed.

- The schema helper added `is_anonymous` once, retained all 28 existing
  questions as `0`, and completed a second run with exactly one column.
- Handler checks covered normal and anonymous persistence, retained
  `author_id`, exact success messages, empty questions, a manipulated creator
  target, and checkbox values `on`, `true`, `yes`, `2`, `-1`, and an array.
- The unlinked but directly reachable `fragen.php` compatibility route was
  checked over isolated HTTP: it stored `author_id` and `is_anonymous = 1`,
  returned the anonymous success copy, and rendered the Creator label as
  `Anonym` without the username.
- Creator and visitor partial renders suppressed anonymous identity, links,
  and images; normal creator attribution remained `@username`. Answered visitor
  previews suppressed author identity for anonymous and normal questions.
- A manipulated answer request could not change `is_anonymous`. Logged-out
  handler and browser requests created no question and reached the existing
  authentication response.
- Anonymous and normal question-to-post flows created posts containing only
  creator, question title, answer body, category, and source-question context;
  neither stored nor rendered the question author's username.
- `php -l` passed for all eight changed PHP files. Browser checks passed at
  1440x900 and 390x844, including an unchecked default, exact helper copy,
  44px mobile control height, no horizontal overflow, creator labels, visitor
  previews, and empty browser error/warning logs.
