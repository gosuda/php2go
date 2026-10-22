---
name: sql-extract
description: Extract PHP SQL and existing schemas, separate static queries from safe dynamic construction, and define explicit database/sql result mapping for migration to Go.
---

# SQL Extraction and Database Access

Use SQL-first + `database/sql` as the initial default. Keep static SQL in files and dynamic SQL in minimal Go construction code. Apply the schema preservation, binding, identifier handling, result mapping, and verification rules in [references/database-policy.md](references/database-policy.md).

## Procedure

1. Obtain the original revision and actual database schema. Record PHP wrappers, all SQL call sites, string construction branches, dynamic identifiers, database functions/triggers/procedures/DDL, and transaction boundaries in the SQL Inventory. Supplement branches unresolved by static analysis with isolated runtime observations and disclose remaining gaps.
2. Reconcile the deployed schema with original migrations and record the existing schema in `db/schema.sql` or the established migration directory. Separate it from data fixtures and record database version, charset/collation, sql_mode, timezone, and NULL/unsigned/decimal/zero-date semantics. Preserve schema and table names during initial migration; defer schema redesign and query optimization until equivalence is established.
3. Extract static SQL into `db/queries/*.sql` with stable inventory IDs and a deployment loading strategy. Preserve filters, sorting/ties, LIMIT, errors, and result consumption. Deduplicate only when transaction and call semantics match. SQL annotations and generated query methods are not required.
4. Extract dynamic WHERE/ORDER BY/table construction into small Go functions. Keep values in placeholder argument lists and identifiers/directions in validated trusted allowlists. Record the authoritative registry and refresh rules for runtime board tables; do not insert request strings or require per-table generation.
5. Define context-aware `database/sql` calls, parameter order, explicit `Scan` targets, NULL/type conversions, no-row handling, row cleanup, iteration errors, affected rows, and generated IDs. Pin the driver and record connection settings and transaction/connection affinity. No sqlc configuration or ORM is required.
6. Build the target Go code and perform database integration verification. Compare against the original using independent clones of the same snapshot, including dynamic branches and mapping boundaries. Record verification as not run when tools or the database are unavailable. Report genuine execution/observation limitations without treating lack of code generator support as unsupported application behavior.

## Deliverables and Boundaries

- SQL Inventory: original file/symbol/location/branch to feature/contract, schema/static query or dynamic construction, Go access function, result mapping, and verification scenario.
- Existing schema and static SQL files, dynamic construction and allowlist plan, loading/embedding strategy, mapping and transaction plan, driver/settings, and build/integration results.
- Unresolved dynamic SQL, unsupported syntax, extraction/execution gaps, security change proposals, and verification limits.

Keep SQL execution in focused database access functions using `database/sql`. Execute transactional queries on the same `*sql.Tx` and preserve original order, isolation, and rollback behavior. DDL application, restoration, and observation tools remain separate; do not assume MySQL DDL rolls back with ordinary writes.

Do not introduce an ORM or mandatory sqlc generation in the initial migration. Consider sqlx only for demonstrated repetitive mapping, with its mapping behavior verified separately. Do not mix libraries by default.

Record security changes when parameterization removes original SQL injection risks. Treat input policy changes, such as returning a new HTTP 400 for integer parsing failures, separately. Successful SQL execution or type checking alone does not establish functional equivalence PASS.
