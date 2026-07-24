# Feature Specification: Topic And Category Overview

## Problem Statement

Humplore now supports search and independent filters for creator topics and contribution categories, but users still lack a visible browsing overview that helps them discover available topic and category areas before they know what to filter for. The existing category and topic controls are useful for narrowing the feed, but they do not present a curated overview with matching posts and creators.

## User Stories

### Story 1: Browse Topic Groups

As a logged-in user, I want to see a compact group of relevant creator topics above the Explore feed, so that I can discover experience areas without typing a search term.

**Acceptance Criteria:**
- [ ] The overview shows topic groups separately from contribution category groups.
- [ ] The first version shows at most 4 topic groups.
- [ ] Each topic group shows the topic name and a link into the filtered Explore feed for that topic.
- [ ] Each topic group shows up to 2 newest matching posts.
- [ ] Each topic group shows up to 2 matching creators when creator data exists.

### Story 2: Browse Category Groups

As a logged-in user, I want to see contribution category groups with matching posts, so that I can browse experience types such as Alltag, Familie, or Beruf across creator topics.

**Acceptance Criteria:**
- [ ] The first version shows at most 4 contribution category groups.
- [ ] Each category group shows the category name and a link into the filtered Explore feed for that category.
- [ ] Each category group shows up to 2 newest matching posts.
- [ ] Category groups do not merge with topic groups.

### Story 3: Keep Explore Focused

As a logged-in user, I want the overview to appear only when I am browsing the normal Explore feed, so that searches and filtered result views remain focused.

**Acceptance Criteria:**
- [ ] The overview is visible only when no search query and no topic/category filter are active.
- [ ] The overview does not replace the existing feed.
- [ ] The overview appears above the feed and below the existing global header/search area.
- [ ] The existing sidebar filters remain available as quick filters.

### Story 4: Continue Into Filtered Feed

As a logged-in user, I want "more" actions from the overview to open the corresponding filtered Explore feed, so that I can continue browsing with the existing filter behavior.

**Acceptance Criteria:**
- [ ] Topic "more" links set the existing `topic` filter.
- [ ] Category "more" links set the existing `cat` filter.
- [ ] Links preserve the existing `platform.php` route for the first implementation.
- [ ] The design keeps the option open to move the overview to a dedicated page later.

## Non-Functional Requirements

- Compatibility: The first implementation MUST keep `platform.php` as the route and MUST NOT introduce a new required public route.
- Future Extraction: Overview data loading SHOULD be isolated enough that a later `topics.php` or category overview route can reuse it without rewriting the feature.
- Performance: The overview MUST cap groups and preview rows to avoid competing with feed pagination.
- Security: All database values MUST be read through prepared statements where user-provided filters are involved and escaped in HTML output.
- Accessibility: The overview SHOULD use headings and links that make the two axes understandable to screen readers.

## Clarifications

### Q1: Should the overview be a separate page or an Explore extension?

**Recommended Answer**: Start as an Explore extension because the current roadmap prioritizes strengthening discovery inside the existing flow.

**Answer**: Implement as an extension of `platform.php`, but explicitly preserve a future option to move it to a dedicated overview page.

### Q2: Where should the overview appear?

**Recommended Answer**: Place it above the feed, not in the sidebar, because the sidebar is hidden on mobile and has limited space.

**Answer**: Show a compact Browse area above the feed.

### Q3: Should topics and categories be separate groups?

**Recommended Answer**: Yes. This keeps the previously decided topic/category distinction intact.

**Answer**: Show topics and categories as two separate groups.

### Q4: What should each group show?

**Recommended Answer**: Show name, counts or link affordance, and direct preview content.

**Answer**: Show up to 2 newest posts per group; topic groups also show up to 2 matching creators.

### Q5: How should preview posts be selected?

**Recommended Answer**: Use newest matching posts because this is understandable and stable for a browse overview.

**Answer**: Use newest matching posts.

### Q6: How many groups should be visible first?

**Recommended Answer**: Keep the first version compact with 4 topics and 4 categories.

**Answer**: Show at most 4 topic groups and 4 category groups.

### Q7: When should the overview be visible?

**Recommended Answer**: Only in the normal unfiltered Explore state.

**Answer**: Hide it during active search or active topic/category filtering.

### Q8: What should "more" do?

**Recommended Answer**: Link into the filtered Explore feed.

**Answer**: Open the corresponding filtered Explore feed.

## Success Metrics

- Users can discover available topics and categories without entering a search query.
- Users can see matching post previews before committing to a filter.
- Users can continue from any overview group into the existing filtered Explore feed.
- The overview can later be extracted into its own page without changing the visible behavior.

## Out Of Scope

- Creating a separate topic or category overview route in this iteration.
- Adding admin management for curated topic/category order.
- Normalizing topics into a new database table.
- Implementing personalized recommendations.
- Changing the already completed search and filter behavior.
