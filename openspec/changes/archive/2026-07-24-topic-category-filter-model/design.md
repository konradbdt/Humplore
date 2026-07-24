# Design: Topic Category Filter Model

## Technical Approach

Introduce a third internal filter axis:

- `topicCategory` from URL parameter `topic_cat`: broad Themenkategorie.
- `topic` from URL parameter `topic`: concrete Thema, preserved for legacy/deep links.
- `category` from URL parameter `cat`: Beitrag-/Lebenskontext category.

Existing database rows keep using `CreatorDetails.main_topic` and `Users.main_topic` for concrete creator topics. A central PHP catalog maps those concrete topic strings to broad topic categories at query time.

## Data Model

### Current Compatible Model

- `CreatorDetails.main_topic` / `Users.main_topic`: concrete Thema, e.g. ADHS.
- `Posts.category`, `Categories`, `PostCategories`: Beitrag-/Lebenskontext Kategorie.
- Topic category is derived from the concrete topic with a deterministic catalog and keyword mapping.

### Target Normalized Model

Later schema work should introduce:

- `TopicCategories(id, slug, name, position)`
- `Topics(id, topic_category_id, slug, name, aliases)`
- `CreatorTopics(user_id, topic_id)`

Until then, all filtering must remain compatible with free-text `main_topic`.

## Query Behavior

- `topic_cat=Krankheit` matches creators whose concrete topic maps to Krankheit, such as ADHS.
- `topic=ADHS` still matches the exact concrete topic for existing links or future subfilter/search UI.
- `cat=Alltag` still matches contribution/life-context categories independently of topic category.
- Combined filters use AND semantics.
- Search terms still search concrete topics; filtering by topic category narrows the result set without replacing text search.

## UI Behavior

- Sidebar primary topic filter is labeled "Themenkategorien".
- Browse overview section is labeled "Themenkategorien" and links with `topic_cat`.
- Active chips use "Themenkategorie" for `topic_cat`, "Thema" only for concrete legacy/subfilter `topic`, and "Kategorie" for `cat`.
- Post cards and profile headers may continue to display concrete creator topics as "Thema".

## Risks

- Free-text topics cannot be perfectly classified. Mitigation: centralize the mapping and fall back to "Sonstiges" instead of hiding content.
- Existing URLs may use `topic`. Mitigation: keep `topic` support in parsing and SQL filtering.
- Topic-category labels may evolve. Mitigation: keep labels and keyword rules in one helper function.
