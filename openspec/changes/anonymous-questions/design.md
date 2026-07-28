# Design: Anonymous Questions

## Current State

Questions are submitted from the visitor view of a creator profile. The backend inserts `creator_id`, `author_id`, and `question_text` into `Questions`. The creator's questions list displays the author username from `author_name`. Answered-question previews shown to visitors currently show question and answer without an author label.

## Target State

Logged-in users can choose `Anonym fragen` for each question. The checkbox is off by default. Anonymous questions still keep their internal `author_id`, but all creator-facing and visitor-facing question UI displays the author as `Anonym` or omits author identity. The anonymous state is final after submission.

## Technical Approach

### Schema

Add an additive boolean-like column to `Questions`:

```sql
ALTER TABLE Questions ADD COLUMN is_anonymous INTEGER NOT NULL DEFAULT 0;
```

For brownfield databases, schema handling should be defensive and additive. If the project has an existing schema helper, add the column there with an idempotent column-existence check. Document the SQL in `create_tables.sql` or the project's schema notes.

### Submission

Extend the `ask_question` handling in `profile-actions.php` to read the checkbox value. Store `1` only when the submitted value explicitly indicates anonymous asking; otherwise store `0`. Keep `author_id` required and unchanged.

Successful anonymous submission should use a distinct success message such as `Anonyme Frage wurde gesendet.` Normal submission can keep the current `Frage wurde gesendet.` message.

### Display

Load `is_anonymous` with question records in `profile-page.php`. In the creator question list, derive a display label:

- `Anonym` when `is_anonymous` is true
- existing `@username` behavior when `is_anonymous` is false

Do not introduce anonymous IDs, stable labels, profile images, profile links, or any other marker that lets the creator group multiple anonymous questions from the same user.

Visitor answered-question previews should continue showing only question and answer. If future UI adds author labels to visitor previews, it must respect this spec and omit or anonymize author identity.

### Question-to-Post

When a creator answers a question as a post, the created post must not publish the question author's username automatically. For anonymous questions, post context may say `Anonyme Frage` or `Frage aus der Community`. For non-anonymous questions, public attribution requires a separate future consent model and remains out of scope.

The current implementation deletes the source question after creating a post. If the UI needs to show source-question context later, the implementation may store a source-anonymity flag or context text with the post in a follow-up change. For this MVP, the key rule is that no question author identity is exposed in the generated post.

## Architecture Decisions

### Decision: Store `is_anonymous` on `Questions`

**Rationale:**

- The anonymity decision belongs to the individual question.
- The current roadmap explicitly scopes the first decision to anonymous questions.
- A generic privacy model would add unnecessary complexity before profile-field visibility and anonymous posts are specified.

**Alternatives considered:**

- Global account anonymity setting - rejected because users need per-question control.
- Nulling `author_id` for anonymous questions - rejected because abuse handling, future reporting, and a future `Meine Fragen` view need internal attribution.
- Stable anonymous pseudonyms - rejected because they make repeated anonymous questions linkable by creators.

### Decision: Keep anonymous asking logged-in only

**Rationale:**

- The current flow requires an authenticated `author_id`.
- Logged-out anonymous questions would require additional spam protection, captcha, IP-rate limits, and moderation decisions.

### Decision: Anonymous state is immutable

**Rationale:**

- A creator may have already seen the original state.
- Changing anonymity after submission creates confusing and unsafe historical states.

## Files Likely Affected

- `Webseite - Codex/app/support/profile-actions.php` - read and persist anonymous question state.
- `Webseite - Codex/app/support/profile-page.php` - load `is_anonymous` and/or display metadata.
- `Webseite - Codex/app/views/partials/profile-questions-card.php` - add checkbox, helper copy, anonymous author label.
- `Webseite - Codex/create_tables.sql` - document additive question schema.
- Existing schema helper if present - ensure `Questions.is_anonymous` for brownfield databases.

## UX Constraints

- The checkbox must be clearly associated with question submission.
- The checkbox is off by default.
- Helper copy should be concise and should not crowd the form.
- Creator-facing anonymous label is exactly `Anonym`.
- No UI should imply that anonymous means technically untraceable to the platform.

## Out of Scope

- Anonymous posts created directly by creators.
- Profile-field visibility controls such as hiding location, language, or profile image.
- Creator opt-out for anonymous questions.
- Moderation queues or approval workflows.
- Logged-out anonymous questions.
- Public attribution consent for non-anonymous question-to-post publishing.
