---
name: php-analyze
description: Analyze a PHP application's entry points, dependencies, global state, and observable feature boundaries before migrating it to Go.
---

# PHP Analysis

Extract a feature inventory and runtime prerequisites without modifying the original source. Analysis provides input for contracts and scenarios; it does not prove equivalence.

## Procedure

1. Identify the source revision, PHP version and extensions, Composer lockfile, web server rewrites, environment configuration, and database engine. Distinguish facts confirmed by execution from static analysis inferences.
2. Find front controllers, directly accessible `.php` files, include/require paths, dynamic calls, CLI/cron tasks, and upload/download entry points. Use available analysis tools without assuming they resolve all dynamic PHP behavior.
3. Map each feature to inputs, authentication and authorization boundaries, database tables, globals, sessions, cookies, file paths, and external services. Record merging, precedence, and configuration dependencies for `$_GET`, `$_POST`, and `$_REQUEST`.
4. Assign stable feature IDs to boundaries that can be observed and reproduced independently, such as reading a board. Separate shared bootstrap code from feature code and identify scenarios for success, failure, authorization, and state transitions.
5. Link potential PHP/Go differences to source locations: loose comparison, truthiness, numeric/string conversion, missing/null/empty values, array order, character encoding, timezone, errors, and redirects.
6. Inventory database wrappers, SQL call sites, string construction branches, dynamic tables/columns/sorting, stored procedures, triggers, and DDL. Identify the actual schema, charset/collation, sql_mode, storage engine, and transaction boundaries as inputs to SQL extraction. Statically discovered queries do not establish complete database access coverage.

## Deliverables

Include the following in the Analysis Report:

- Source revision, analysis scope, runtime environment, and dependencies needed for reproduction.
- Feature Inventory: feature ID, entry point, source location, inputs, state reads/writes, external effects, and authorization boundaries.
- Evidence: file, symbol, and configuration locations, with execution records where available.
- Unexamined areas and reasons, such as dynamic includes, encrypted source, or missing extensions and services.
- Next steps per feature: contract extraction readiness, required fixtures, and execution/observation adapters.

Keep features with missing source or unavailable execution in the inventory. Label inferences as inferences. Record configuration keys and secret delivery mechanisms rather than copying secret values into reports.
