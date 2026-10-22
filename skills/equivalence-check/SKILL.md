---
name: equivalence-check
description: Use executable verification tools to compare PHP and Go with identical inputs and independent initial state, reporting HTTP, database, session, file, and external effect differences and coverage.
---

# Functional Equivalence Verification

Executable verification tools determine verdicts. The Agent prepares execution, interprets diffs, and guides corrections. Read [references/evidence-protocol.md](references/evidence-protocol.md) for result rules and report fields.

## Procedure

1. Identify an actually available verifier, its version, and its supported channels, comparators, and assertions. This Skill does not include the `equiv` CLI. Do not assume unavailable commands or execution results exist.
2. Pin contract/fixture/source/candidate revisions and hashes and runtime profiles. Record baseline conflicts, unstable reference behavior, or restoration failures as validity issues first.
3. Independently clone database, session, file, and external double state for PHP and Go from the same snapshot. Verify equivalent initial states and prevent cross-contamination regardless of execution order.
4. Execute the same request sequence per scenario. For dynamic tokens and IDs, use each execution's values according to declared extraction/binding rules and check logical request meaning and value relationships. Do not hide failures with arbitrary retries.
5. Capture raw values and deltas for all required channels. Apply only declared normalization and comparators and execute each invariant's actual assertion. Do not replace missing observations with empty values or `none`.
6. Package machine-readable results, diffs, raw evidence, and coverage produced by the verifier. After changes, reverify affected scenarios against the new candidate revision.
7. Map each SQL Inventory site/branch to `.sql` queries, generated methods, and scenarios. Include schema/query/config hashes, sqlc version, and generation reproducibility evidence. Generation success does not establish database equivalence PASS. Compare empty results, NULL, numeric conversions, ordering, affected rows, generated IDs, rollback, and dynamic table dispatch within the relevant contract scope.

## Verdict Boundaries

- Differences found by valid comparisons are `MISMATCH`. Do not replace a tool's verdict with PASS based on Agent interpretation.
- Record `UNSUPPORTED` for required channels or assertions the tool cannot support and `NOT COVERED` for insufficient execution evidence. If no verifier exists, report unexecuted scenarios and the missing tool without generating PASS.
- Link approved security changes in separate records while retaining actual diffs and `MISMATCH` results.
- Do not infer whole-feature equivalence from partial success. Include inventory features with no scenarios in the report.

When execution is impossible, identify missing prerequisites and adapters concretely. Label manual comparisons and AI reviews as advisory observations rather than evidence of deterministic executable verification.
