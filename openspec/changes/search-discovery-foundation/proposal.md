# Change Proposal: Search Discovery Foundation

## Why

The current search expects exact or very close input. This blocks Humplore's core discovery use case because users often search by memory, situation, or a misspelled topic.

## What Changes

- Add shared search behavior for profile and post discovery.
- Add typo-tolerant fallback using bounded fuzzy matching over existing terms.
- Add suggested terms when the query has weak or empty results.
- Reuse the same search helper from `search.php` and `platform.php`.

## Impact

- Affects `search.php` and `app/support/platform-page.php`.
- Adds a helper under `app/support`.
- No schema migration required.

## Rollback

Revert the helper and restore the previous direct SQL queries in both routes.
