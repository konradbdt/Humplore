# Design: Search Discovery Foundation

## Approach

Add `app/support/search-discovery.php` and include it from bootstrap before `platform-page.php`.

The helper returns:

- `resultsProfiles`
- `resultsPosts`
- `countProfiles`
- `countPosts`
- `totalFound`
- `suggestions`
- `relatedTerms`
- `usedFuzzy`

Direct SQL remains the first path. If direct results are weak, the helper collects bounded candidate terms from usernames, emails, creator topics, categories, post categories, post titles, and post content snippets. It then compares normalized query tokens with candidate tokens using `levenshtein()` and `similar_text()`.

## File Changes

- Add `Webseite - Codex/app/support/search-discovery.php`.
- Update `Webseite - Codex/app/bootstrap.php`.
- Update `Webseite - Codex/app/support/platform-page.php`.
- Update `Webseite - Codex/search.php`.

## Risks

- Fuzzy matching can become expensive on large datasets. Mitigation: cap candidate and result counts.
- Existing text encoding is inconsistent in some files. Mitigation: keep new code ASCII where practical.
