# Proposal: Topic Category Filter Model

## Why

Humplore's current Explore filter treats concrete creator topics such as ADHS as the primary `topic` filter. Product terminology now separates three concepts:

- Topic category: broad experience area such as Krankheit, Religion, Beruf, Herkunft, Alter, or Geschlecht/Identitaet.
- Topic: concrete creator experience such as ADHS.
- Category: contribution or life-context category such as Alltag, Familie, Schule & Studium, or Hobbys.

The primary discovery filter must move from concrete topics to topic categories while keeping existing search and legacy topic links functional.

## What Changes

- Add `topic_cat` as the primary topic-category filter.
- Keep `topic` as a backward-compatible concrete-topic subfilter/search continuation, but stop presenting it as the main Explore filter.
- Map existing free-text creator topics to topic categories through a central catalog so the current SQLite data remains usable.
- Keep `cat` for contribution/life-context categories.
- Update Explore UI copy and active filter labels to distinguish Themenkategorie, Thema, and Kategorie.
- Preserve text search across concrete topics, contribution categories, posts, and profiles.

## Impact

- Affects `platform.php`.
- Updates shared helpers in `app/support/platform-page.php` and `app/support/search-discovery.php`.
- Updates the shared sidebar category partial only if needed for clearer contribution category behavior.
- Updates Spec-Kit artifacts with the same terminology.
- No destructive schema migration.

## Rollback

Revert the helper/UI changes and remove the `topic-category-filter-model` change artifacts. Existing `topic` and `cat` URLs remain compatible during rollback.
