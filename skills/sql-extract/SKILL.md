---
name: sql-extract
description: Extract PHP SQL and existing schemas, separate static queries from safe dynamic construction, and define explicit database/sql result mapping for migration to Go. Use when a feature with database access lacks a valid access plan.
---

# SQL Extraction and Database Access

Read the authoritative [database policy](references/database-policy.md) before extraction and the [security change record format](../equivalence-check/references/evidence-protocol.md#security-change-records) when extraction exposes an intentional behavior change. This Skill defines extraction tasks; those documents own the shared rules.

Apply the Evidence Protocol's artifact reuse rules to the selected feature's SQL plans. Inventory the call sites and shared dependencies reachable from that feature, reusing unchanged schema/runtime artifacts and existing plans. Expand the scope only for unresolved shared impact.

## Procedure

1. Obtain the original revision and deployed schema. Inventory wrappers, SQL call sites, string construction branches, dynamic identifiers, database functions/triggers/procedures/DDL, and transaction boundaries. Supplement unresolved static analysis with isolated observations and identify remaining gaps.
2. Reconcile deployed DDL with historical migrations. Produce the schema artifact and runtime profile required by the database policy, recording source provenance and discrepancies.
3. Classify static queries and dynamic construction. Assign stable inventory IDs, extract static files, and document their deployment loading strategy. Review duplicate candidates against original call and transaction context before merging them.
4. Produce dynamic construction plans: branch conditions, fragment sources, ordered arguments, identifier registry ownership, and registry refresh paths. Map each plan back to its original construction branches.
5. Produce an access/mapping plan per query: Go function, call shape, input types, result destinations, error/result consumption, and transaction/connection ownership. Resolve uncertain types using the actual driver and schema.
6. Build any resulting access code and run the database policy's completion checks. Attach scenarios to every extracted branch and record missing prerequisites or unsupported observations.

## Deliverables

- SQL Inventory mapping original location/branch to feature/contract, schema/query or construction function, mapping plan, and scenario. Complete the `access_plan` of each call site in the [inventory format](../php-analyze/references/inventory-format.md#sql-inventory).
- Schema/runtime profile, static SQL files, dynamic construction and loading plans, driver configuration, and build/integration evidence.
- Unresolved branches, extraction gaps, proposed behavior changes, and follow-up work.
