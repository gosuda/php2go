---
name: go-migrate
description: Implement Go code that preserves observable PHP behavior using Behavior Contracts and fixtures, and revise it based on execution diffs.
---

# Go Implementation

Implement feature by feature using the original source, feature inventory, contracts, and captured fixtures. Respect the target Go project's structure and existing dependency choices.

## Implementation Criteria

- Build the database layer with SQL-first + sqlc. Call methods generated from `.sql` files and pinned sqlc configuration using the original SQL Inventory and actual schema. Prepare missing extraction artifacts first; do not handwrite or edit generated code.
- Prohibit SQL string construction and direct `Query*`/`Exec*`/`Prepare*` calls in application code. Infrastructure handles connections, pools, health checks, and begin/commit/rollback; bind transactional queries through `WithTx`. Schema application, fixture restoration, and observation tools sit outside this application boundary.
- Handle dynamic conditions, sorting, and table names with finite static SQL and allowlist dispatch. Do not bind arbitrary identifiers or introduce raw SQL fallbacks. Report behavior that cannot be represented with sqlc as `UNSUPPORTED`.
- Match request parsing, routing/rewrites, status/headers/body, errors, and redirects to the contract. Do not assume Go defaults match PHP missing/null/empty values, loose comparisons, truthiness, numeric strings, arrays, or encoding semantics.
- Preserve transactions/rollback, NULL/types, sorting and tie handling, auto-increment IDs, and reference relationships. Do not rely on driver result order or default timezone.
- Preserve observable session IDs, cookie attributes, authentication/authorization, CSRF behavior, file paths/permissions, and external effects. Specify compatibility boundaries with existing stores when needed.
- Allow injection of clock/RNG/external service dependencies without inserting verification constants or fixture-specific response branches into production service code.
- Implement success, error, and authorization paths together. Explicitly identify features outside the initial scope instead of disguising them with success responses.

## Revision Loop

1. Run `sqlc generate` with the pinned version, verify reproducible generation, and run Go builds/tests for a small feature boundary. If sqlc is unavailable, disclose that generation was not run rather than claiming success.
2. Compare against independent clones of the same snapshot using an executable verifier when available. Otherwise report verification as not run.
3. Narrow diffs to input interpretation, responses, state transitions, or environment differences, then revise Go. Do not erase differences by changing the original or expected values.
4. Rerun affected scenarios and related regression paths. If the same cause repeats without progress, document missing information and unresolved differences and continue other implementable work.

Propose security changes to unsafe original behavior separately with evidence and impact scope. Implement within existing approval where available, and record approved changes separately from comparison results.

Deliver Go source, execution/configuration instructions, PHP-to-Go feature mappings, build/test results, execution evidence references, unresolved differences, and unverified areas. Neither successful compilation nor AI code review establishes equivalence PASS.
