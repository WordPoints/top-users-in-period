# Change Log for Top Users In Period

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [1.0.0] - 2017-05-08

### Added

- Query class to query the total points for each user in a period. #4
  - Uses a points logs query class for short periods.
  - Uses a query including blocks of data cached in the database for longer periods.
  - Automatically caches the data in the blocks on the fly.
  - Uses blocks that are one week in seconds long by default.
  - Uses a block types app to manage the block types.
  - Also caches the results of finished queries.
  - Uses a query cache types app to manage the query caches.
  - Caches query results in transients by default.
- Cache invalidation handlers that automatically clear the caches for open-ended
  queries as needed whenever a new points transaction occurs.
- Class to display a query's results in a table.
- Admin screen where admins can query the totals over fixed periods.
- Widget that displays totals from a fixed period.
- Widget that displays totals from a dynamic period.
  - Period can be calculated relative to the calendar, or relative to the present.
- Shortcode that displays totals from a fixed period.
- Shortcode that displays totals from a dynamic period.

[unreleased]: https://github.com/WordPoints/top-users-in-period/compare/master...HEAD
[1.0.0]: https://github.com/WordPoints/top-users-in-period/compare/...1.0.0
