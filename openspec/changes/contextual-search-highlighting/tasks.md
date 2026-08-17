# Tasks: Contextual Search Highlighting

## 1. Search Context

- [ ] 1.1 Define a shared result structure for literal and related matched terms.
- [ ] 1.2 Preserve existing exact, fuzzy, suggestion, filter, and ranking behavior.
- [ ] 1.3 Add question matching by question text and addressed creator topic.

## 2. Safe Highlighting

- [ ] 2.1 Add a shared escaping and literal-highlight helper.
- [ ] 2.2 Highlight matching profile fields in search contexts.
- [ ] 2.3 Highlight matching post title, category, and visible content.
- [ ] 2.4 Highlight matching question text or creator topic.
- [ ] 2.5 Label related-only results as `Thematisch verwandt`.
- [ ] 2.6 Add accessible yellow highlight styling.

## 3. Questions Rail

- [ ] 3.1 Load search-filtered questions when a query is active.
- [ ] 3.2 Rename the active-search rail to `Passende Fragen`.
- [ ] 3.3 Show `Keine passenden Fragen gefunden.` without unrelated fallback.
- [ ] 3.4 Keep the existing general question rail when no search is active.
- [ ] 3.5 Reuse the filtered question data in the questions modal.

## 4. Verification

- [ ] 4.1 Run PHP syntax checks on all changed PHP files.
- [ ] 4.2 Verify literal `Krebs` matches across profiles, posts, and questions.
- [ ] 4.3 Verify a related-only result highlights the related term and shows its label.
- [ ] 4.4 Verify unrelated questions disappear during an active search.
- [ ] 4.5 Verify the no-question empty state.
- [ ] 4.6 Verify the unchanged question rail without an active search.
- [ ] 4.7 Verify HTML-like query and content values remain escaped.
- [ ] 4.8 Verify desktop, mobile, keyboard, and contrast behavior.
