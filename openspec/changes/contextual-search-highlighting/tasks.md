# Tasks: Contextual Search Highlighting

## 1. Search Context

- [x] 1.1 Define a shared result structure for literal and related matched terms.
- [x] 1.2 Preserve existing exact, fuzzy, suggestion, filter, and ranking behavior.
- [x] 1.3 Add question matching by question text and addressed creator topic.

## 2. Safe Highlighting

- [x] 2.1 Add a shared escaping and literal-highlight helper.
- [x] 2.2 Highlight matching profile fields in search contexts.
- [x] 2.3 Highlight matching post title, category, and visible content.
- [x] 2.4 Highlight matching question text or creator topic.
- [x] 2.5 Label related-only results as `Thematisch verwandt`.
- [x] 2.6 Add accessible yellow highlight styling.

## 3. Questions Rail

- [x] 3.1 Load search-filtered questions when a query is active.
- [x] 3.2 Rename the active-search rail to `Passende Fragen`.
- [x] 3.3 Show `Keine passenden Fragen gefunden.` without unrelated fallback.
- [x] 3.4 Keep the existing general question rail when no search is active.
- [x] 3.5 Reuse the filtered question data in the questions modal.

## 4. Verification

- [x] 4.1 Run PHP syntax checks on all changed PHP files.
- [x] 4.2 Verify literal `Krebs` matches across profiles, posts, and questions.
- [x] 4.3 Verify a related-only result highlights the related term and shows its label.
- [x] 4.4 Verify unrelated questions disappear during an active search.
- [x] 4.5 Verify the no-question empty state.
- [x] 4.6 Verify the unchanged question rail without an active search.
- [x] 4.7 Verify HTML-like query and content values remain escaped.
- [x] 4.8 Verify desktop, mobile, keyboard, and contrast behavior.
