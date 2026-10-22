---
name: go-migrate
description: Implement Go code that preserves observable PHP behavior using Behavior Contracts and fixtures, and revise it based on execution diffs.
---

# Go Implementation

Implement feature by feature using the original source, feature inventory, contracts, and captured fixtures. Respect the target Go project's structure and existing dependency choices.

## Implementation Criteria

- Build the initial database layer with SQL-first + `database/sql` and a pinned driver. Preserve existing schema, table names, and SQL semantics; defer optimization and schema redesign until equivalence is established. Do not require sqlc or introduce an ORM. Consider sqlx only when repetitive result mapping creates a concrete need, with separate verification.
- Keep static queries in `.sql` files available to deployed builds and dynamic construction in focused Go database access code. Use context-aware `QueryContext`, `QueryRowContext`, and `ExecContext` with bound values. Map `Scan` targets, NULL, numeric/time conversions, `sql.ErrNoRows`, affected rows, and generated IDs explicitly; close rows and check `rows.Err()`.
- Build dynamic conditions from fixed SQL fragments and argument lists. Resolve table/column names and sort direction through trusted validated allowlists; never concatenate request data. Support dynamic board tables through the authoritative server-managed registry, including authorized creation and registry refresh. Do not require per-table code generation or mark dynamic queries unsupported merely because sqlc cannot represent them.
- Keep transactional statements on the same `*sql.Tx` and preserve order, isolation, rollback, and connection-local semantics. Infrastructure handles connection pools and lifecycle; schema application, fixture restoration, and observation tooling remain separate.
- Match request parsing, routing/rewrites, status/headers/body, errors, and redirects to the contract. Do not assume Go defaults match PHP missing/null/empty values, loose comparisons, truthiness, numeric strings, arrays, or encoding semantics.
- Preserve transactions/rollback, NULL/types, sorting and tie handling, auto-increment IDs, and reference relationships. Do not rely on driver result order or default timezone.
- Preserve observable session IDs, cookie attributes, authentication/authorization, CSRF behavior, file paths/permissions, and external effects. Specify compatibility boundaries with existing stores when needed.
- Allow injection of clock/RNG/external service dependencies without inserting verification constants or fixture-specific response branches into production service code.
- Implement success, error, and authorization paths together. Explicitly identify features outside the initial scope instead of disguising them with success responses.

## Revision Loop

1. Run Go builds/tests for a small feature boundary. Verify static SQL availability, placeholder arguments, identifier allowlists, result mapping, and transaction behavior with the pinned driver and recorded database settings. Disclose unavailable checks without claiming success.
2. Compare against independent clones of the same snapshot using an executable verifier when available. Otherwise report verification as not run.
3. Narrow diffs to input interpretation, responses, state transitions, or environment differences, then revise Go. Do not erase differences by changing the original or expected values.
4. Rerun affected scenarios and related regression paths. If the same cause repeats without progress, document missing information and unresolved differences and continue other implementable work.

Propose security changes to unsafe original behavior separately with evidence and impact scope. Implement within existing approval where available, and record approved changes separately from comparison results.

Deliver Go source, execution/configuration instructions, PHP-to-Go feature mappings, build/test results, execution evidence references, unresolved differences, and unverified areas. Neither successful compilation nor AI code review establishes equivalence PASS.
