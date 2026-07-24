# Tasks: Anonymous Questions

## 1. Foundation

- [ ] 1.1 Locate all active question submission and display paths.
- [ ] 1.2 Confirm whether a defensive database schema helper already manages additive columns.
- [ ] 1.3 Add or document `Questions.is_anonymous INTEGER NOT NULL DEFAULT 0`.
- [ ] 1.4 Ensure existing questions default to non-anonymous.

## 2. Backend

- [ ] 2.1 Extend question submission to read a per-question anonymous checkbox.
- [ ] 2.2 Preserve `author_id` for anonymous and non-anonymous questions.
- [ ] 2.3 Store anonymous questions with `is_anonymous = 1` and normal questions with `is_anonymous = 0`.
- [ ] 2.4 Return distinct success copy for anonymous question submission.
- [ ] 2.5 Load `is_anonymous` wherever questions are rendered.
- [ ] 2.6 Ensure question-to-post answering does not publish the question author's username.

## 3. UI

- [ ] 3.1 Add an unchecked `Anonym fragen` checkbox to the creator question form.
- [ ] 3.2 Add concise helper copy explaining that the user's name will not be shown to the creator or other users.
- [ ] 3.3 Render anonymous creator-facing author labels as exactly `Anonym`.
- [ ] 3.4 Keep normal creator-facing author labels using the existing username behavior.
- [ ] 3.5 Do not render profile links, profile images, stable anonymous IDs, or grouping markers for anonymous question authors.
- [ ] 3.6 Keep visitor answered-question previews free of question author identity.

## 4. Verification

- [ ] 4.1 Run PHP syntax checks on changed PHP files.
- [ ] 4.2 Verify normal question submission stores `is_anonymous = 0` and shows the current success message.
- [ ] 4.3 Verify anonymous question submission stores `is_anonymous = 1` and shows anonymous success copy.
- [ ] 4.4 Verify the creator sees `Anonym` for anonymous questions and the username for non-anonymous questions.
- [ ] 4.5 Verify visitors do not see question author identity in answered-question previews.
- [ ] 4.6 Verify anonymous questions still retain internal `author_id`.
- [ ] 4.7 Verify users cannot change anonymous state after submission.
- [ ] 4.8 Verify logged-out requests cannot create anonymous questions.
- [ ] 4.9 Verify answering an anonymous question as a post does not expose the question author's username.
