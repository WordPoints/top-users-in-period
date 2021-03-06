# Change Log for Top Users In Period

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased]

Nothing documented at this time.

## [1.0.2] - 2017-10-20

### Requires

- WordPoints: 2.4+

### Added

- `wordpoints_top_users_in_period_table_avatar_size` to filter the size of the user avatars in the top users in period table.

### Changed

- Query class to use the `wordpoints_prevent_interruptions()` function rather than duplicating its code.

### Fixed

- Deprecated notices from `Channel`, `Module Name`, and `Module URI` extension headers.
- Deprecated notices during uninstall, by using the new installables API.
- Deprecated notices from queries involving the `start` arg, by using the `offset` arg instead.

## [1.0.1] - 2017-06-01

### Fixed

- Queries with future end dates not having their cache flushed when new points logs
  are added. #11
- Blocks being published even if they were not filled successfully. #13
- Empty `*in*` args not being removed before calculating the query signature. #14
- The module code being loaded even when the points component isn't active. #16
- Caches not being flushed and block logs not being cleaned when a user was deleted. #12
- Blocks and block logs not being cleaned when a points type was deleted. #15
- MySQL errors when attempting to count points logs or block logs queries. #17
- Deprecated errors from `Channel` module header on WordPoints 2.4.0. #18

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
[1.0.2]: https://github.com/WordPoints/top-users-in-period/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/WordPoints/top-users-in-period/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/WordPoints/top-users-in-period/compare/...1.0.0
