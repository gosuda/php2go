# SQL-first + database/sql Policy

Apply this policy to migration targets. Begin with `database/sql` and a driver for the existing database. Preserve SQL semantics without requiring code generation or an ORM. This repository contains instructions, not executable database access code.

This document is the sole owner of shared database rules. Extraction and implementation Skills reference it rather than maintaining independent copies. [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md) owns verdicts, coverage, and security change handling.

## Preserve the Database Before Redesigning It

The first migration preserves existing schema, table names, statement order, filters, joins, sorting, limits, transactions, and database configuration. Record actual deployed DDL and runtime settings rather than assuming historical migrations match production.

Separate PHP-to-Go migration from database redesign. After equivalence is established, propose SQL optimization, schema consolidation, or a different access library as an independently verified change. Parameterization and input validation are separate decisions: adding LIMIT, integer parsing, or a new HTTP 400 may change original behavior. For intentional security differences, use the Evidence Protocol's security change record.

## Suggested Target Layout

```text
db/
├── schema.sql          # Existing schema, separate from data fixtures.
└── queries/            # Static SQL.
internal/store/         # database/sql calls, dynamic construction, result mapping.
```

These are target conventions, not implemented repository paths. Reuse an existing project's equivalent structure. Define how SQL files reach deployed builds, such as embedding them in a Go package that can access their directory. Avoid maintaining duplicate SQL copies in files and Go constants.

Pin the driver version and record DSN behavior, charset/collation, sql_mode, timezone, time parsing, affected-row settings, and pool limits without exposing secrets. Schema DDL alone does not restore data, triggers/procedures, connection state, or all runtime semantics.

## Static and Dynamic Queries

| Original pattern | Migration rule |
| --- | --- |
| Static SQL with interpolated values | Extract SQL to a file; replace data values with driver placeholders |
| Optional WHERE | Append fixed program fragments with ordered arguments; preserve missing/null/empty distinctions |
| Dynamic ORDER BY | Choose column names and ASC/DESC from validated trusted allowlists |
| Per-board table or configured prefix | Resolve an exact validated table identifier through a trusted server-managed registry |
| Variable-length IN | Generate placeholders for bound values; specify empty-list behavior and preserve duplicates/NULL semantics |
| Runtime board/table creation | Preserve authorized DDL and authoritative registry update/refresh behavior; verify engine-specific DDL effects |
| Procedures or database-specific functions | Preserve when supported by the actual driver/database; report real execution or observation limits |

Values always remain placeholder arguments. Table/column identifiers, SQL keywords, directions, and fragments cannot be supplied as ordinary value parameters. Use fixed program fragments or validated entries from trusted configuration/metadata. A regex check alone does not establish authorization to access a table. Validate and dialect-quote identifiers where needed; never interpolate raw request values or accept arbitrary fragments through the registry.

For runtime board creation, document who can update the registry, how names are validated, how concurrent requests see changes, and how authorization is checked. Do not require recompilation merely because a new allowed table is created. If that behavior cannot be reproduced, report the specific limitation; do not blame a code generator that is not required.

## Explicit Calls and Result Mapping

Use `QueryRowContext`, `QueryContext`, and `ExecContext` in small database access functions. Preserve original result consumption: a single-row query and a multi-row loop are different contracts. `QueryRowContext` reports query/no-row errors when `Scan` is called; handle `sql.ErrNoRows` according to original application behavior. See [Go query documentation](https://go.dev/doc/database/querying).

- Match SELECT column order to `Scan` destinations and preserve NULL with suitable nullable types or pointers. Do not map NULL silently to zero or an empty string.
- Check signed/unsigned bounds, decimal precision, text/byte encoding, timestamps/timezones, and invalid or zero-date handling against the actual driver. Do not assume every ID fits `int64` or use floating point for exact decimals without verifying semantics.
- For multiple rows, close rows, check each `Scan` error, and check `rows.Err()` after iteration. Preserve result order and the observable distinction between an empty result, an error, and a missing row.
- For writes, inspect `RowsAffected` and generated IDs where required. Verify driver configuration and engine behavior rather than assuming matched rows equal changed rows.
- Never obtain a generated ID on an unrelated pooled connection. Keep connection-local operations on the same transaction or dedicated connection when required.

## Transaction and Schema Boundaries

Use `BeginTx`, execute related statements through the same `*sql.Tx`, check commit errors, and roll back on failure. Do not accidentally execute part of the transaction through `*sql.DB`. Preserve isolation, statement order, locking, and connection-local settings; investigate observable differences rather than rewriting transaction boundaries for convenience. See [Go transaction documentation](https://go.dev/doc/database/execute-transactions).

Database schema application, fixture restoration, and observation adapters are separate tools. Application features that perform DDL still need explicit migration and verification. Do not assume MySQL DDL participates in rollback like ordinary transactional writes; capture implicit commits and other relevant effects. See [MySQL implicit commit rules](https://dev.mysql.com/doc/refman/8.4/en/implicit-commit.html) and check the target server version.

## Optional Libraries

The initial default is `database/sql` alone plus the required driver. sqlc is not a required dependency, and lack of support in sqlc is not an unsupported-feature criterion. Do not introduce GORM or another ORM as part of the initial migration: rewriting SQL into model operations adds another behavior change to verify.

Consider sqlx only if repetitive row mapping demonstrates a concrete maintenance cost. It extends `database/sql` with mapping, named parameters, and IN helpers, but does not replace equivalence checks or establish compile-time query safety. Verify column mapping, NULL, empty-list behavior, and transaction usage before adoption. Avoid mixing sqlc, sqlx, and ORM layers by default. See the [sqlx guide](https://jmoiron.github.io/sqlx/).

## Completion Evidence

1. SQL sites and construction branches map to features, static files/dynamic functions, argument bindings, result mappings, and scenarios. Unknown branches remain visible.
2. Static SQL is available in deployed builds; dynamic fragments and identifier allowlists have known trusted sources. Invalid identifiers and unknown/unauthorized boards are covered.
3. Go builds with the pinned driver. Relevant checks cover Scan ordering, NULL, numeric/time conversions, empty/no-row results, iteration errors, affected rows, generated IDs, and connection affinity.
4. Independent equivalent snapshots produce comparable PHP/Go observations, including database effects, transaction failure/rollback, dynamic registry updates, and required DDL behavior.
5. Existing schema and SQL semantics remain intact apart from explicitly tracked changes. Deferred optimization or redesign is recorded separately.

Attach these checks to the execution evidence and result rules in the Evidence Protocol.
