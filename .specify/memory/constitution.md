# Humplore Project Constitution

## Core Values

1. **Experience Discovery First**: Search, categories, and profiles MUST help users find relevant lived experience without requiring exact wording.
2. **Trust And Privacy By Default**: Features SHOULD expose trust signals and privacy choices without forcing real-name disclosure.
3. **Small Compatible Steps**: Public routes and existing SQLite data MUST remain compatible while functionality is improved incrementally.
4. **Readable PHP Over Framework Drift**: New behavior SHOULD use the existing `app/support` helper pattern before introducing new dependencies.

## Technical Principles

### Architecture

- Keep route files thin where practical and move reusable behavior into `app/support`.
- Prefer additive schema changes and defensive reads because the project is a brownfield PHP/SQLite application.
- Existing URLs such as `platform.php`, `search.php`, and `profile.php` remain stable.

### Code Quality

- Escape output with existing helper functions.
- Use prepared statements for all database reads and writes.
- Keep functions deterministic enough to smoke-test without a browser.

### User Experience

- A failed exact search SHOULD still provide related results, suggestions, or clear next actions.
- Empty states SHOULD guide users toward categories, creators, or broader terms.

## Decision Framework

When making implementation decisions, consider:

1. Does this improve discovery or trust for users?
2. Does it preserve existing data and routes?
3. Can the behavior be verified locally with focused checks?
4. Can the next prioritized feature build on it?
