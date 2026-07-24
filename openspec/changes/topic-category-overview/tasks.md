# Tasks: Topic Category Overview

## 1. Foundation

- [x] 1.1 Add an overview visibility helper for normal unfiltered Explore state.
- [x] 1.2 Add a reusable overview data loader that returns separate topic and category groups.
- [x] 1.3 Keep route-specific URL generation outside the core grouping logic where practical.

## 2. Data

- [x] 2.1 Load at most 4 topic groups from creator topic fields.
- [x] 2.2 Attach up to 2 newest matching posts to each topic group.
- [x] 2.3 Attach up to 2 matching creators to each topic group.
- [x] 2.4 Load at most 4 contribution category groups from existing category data.
- [x] 2.5 Attach up to 2 newest matching posts to each category group.

## 3. UI

- [x] 3.1 Render the Browse overview above the Explore feed.
- [x] 3.2 Render topics and categories as two separate sections.
- [x] 3.3 Link topic "more" actions to the existing `topic` filtered feed.
- [x] 3.4 Link category "more" actions to the existing `cat` filtered feed.
- [x] 3.5 Hide the overview during active search or active topic/category filtering.

## 4. Verification

- [x] 4.1 Run PHP syntax checks on changed PHP files.
- [x] 4.2 Verify overview visibility for unfiltered, searched, topic-filtered, and category-filtered Explore states.
- [x] 4.3 Verify group and preview limits.
- [ ] 4.4 Inspect desktop and mobile layout to confirm the overview stays above the feed and the feed remains available.
