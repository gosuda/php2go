---
name: sql-extract
description: Extract embedded SQL and actual schemas from PHP, bind values, and separate static queries to prepare a sqlc-generated database layer for migration to Go.
---

# SQL Extraction and sqlc Generation

Use SQL files as the authority for target application database queries and call sqlc-generated methods from Go. Apply the dynamic SQL handling, configuration examples, and verification criteria in [references/sqlc-policy.md](references/sqlc-policy.md).

## Procedure

1. Obtain the original revision and actual database schema. Record PHP wrappers, all SQL call sites, string construction branches, dynamic identifiers, database functions/triggers/procedures/DDL, and transaction boundaries in the SQL Inventory. Supplement branches unresolved by static analysis with isolated runtime observations and disclose remaining gaps.
2. Reconcile the deployed schema with original migrations and select `db/schema.sql` or a schema migration directory. Separate it from data fixtures and record database version, charset/collation, sql_mode, timezone, and NULL/unsigned/decimal/zero-date semantics. Do not silently simplify schemas to satisfy sqlc.
3. Extract SQL into `db/queries/*.sql` and assign query names and cardinality annotations. Bind user values while preserving original filters, sorting/ties, LIMIT, errors, and result consumption. Deduplicate only when transaction and call semantics match.
4. Separate dynamic WHERE/ORDER BY/table names into finite static queries and dispatch. Record the identifiers generated at build time and their provenance. Explicitly report unrepresentable behavior as `UNSUPPORTED` rather than bypassing sqlc with application raw SQL.
5. Specify engine, schema/query paths, Go output/package, and required type overrides in `sqlc.yaml`. Pin sqlc and Go driver versions and inspect actual generated types after `sqlc generate`. Do not directly edit generated Go files.
6. Verify identical regeneration, build the target Go code, and perform database integration verification. Compare against the original using independent clones of the same snapshot. Record verification as not run when tools or the database are unavailable.

## Deliverables and Boundaries

- SQL Inventory: original file/symbol/location/branch to feature/contract, schema/query name, generated method, and verification scenario.
- Schemas and queries, sqlc configuration, tool versions, generation/build results, and static SQL generation scripts and input lists when needed.
- Unresolved dynamic SQL, unsupported syntax, extraction/execution gaps, security change proposals, and verification limits.

Infrastructure handles connections, pools, health checks, and begin/commit/rollback; transactional queries use generated methods. DDL application, restoration, and observation tools are separate. Do not move business queries into infrastructure as a way to execute them directly.

Record security changes when parameterization removes original SQL injection risks. Treat input policy changes, such as returning a new HTTP 400 for integer parsing failures, separately. SQL generation or type checking alone does not establish functional equivalence PASS.
