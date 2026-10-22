---
name: equivalence-check
description: Use executable verification tools to compare PHP and Go with identical inputs and independent initial state, reporting HTTP, database, session, file, and external effect differences and coverage.
---

# Functional Equivalence Verification

Executable checks determine verdicts. The Agent selects or writes target-project checks, prepares execution, interprets diffs, and guides corrections. Read [references/evidence-protocol.md](references/evidence-protocol.md) for tool selection, result rules, and report fields.

## Procedure

1. Inspect available project tests, HTTP/database tools, capture scripts, and assertions. Select or implement the missing target-specific checks using the Evidence Protocol's execution tool rules; do not wait for a dedicated CLI or shared harness.
2. Select affected scenarios under the Evidence Protocol's artifact reuse rules and reuse valid reference inputs. Pin contract/fixture/source/candidate revisions and hashes and runtime profiles. Record baseline conflicts, unstable reference behavior, or restoration failures as validity issues first.
3. Independently clone database, session, file, and external double state for PHP and Go from the same snapshot. Verify equivalent initial states and prevent cross-contamination regardless of execution order.
4. Execute the same request sequence per scenario. For dynamic tokens and IDs, use each execution's values according to declared extraction/binding rules and check logical request meaning and value relationships. Do not hide failures with arbitrary retries.
5. Capture raw values and deltas for all required channels. Apply only declared normalization and comparators and execute each invariant's actual assertion. Do not replace missing observations with empty values or `none`.
6. Assemble tool/assertion outputs, machine-readable results, diffs, raw evidence, and coverage into the Evidence Protocol's report. After changes, reverify affected scenarios against the new candidate revision.
7. Map SQL Inventory branches to queries/construction, Go functions, mappings, and scenarios. Derive database-specific checks from the [database policy](../sql-extract/references/database-policy.md#completion-evidence) and attach their results to the report.

## Report Assembly

Apply the Evidence Protocol's validity, verdict, aggregation, and security change rules directly. Assemble results against the complete feature inventory and identify missing prerequisites or adapters.

For proxy work, run the relevant comparison stages in the [Virtual Patch Plan](../virtual-patch/references/patch-plan.md#verification-matrix), identify which implementation handled each scenario, and attach its route-level support and rollout decision.
