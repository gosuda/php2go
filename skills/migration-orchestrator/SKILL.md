---
name: migration-orchestrator
description: Select which php2go Skills a PHP-to-Go feature still needs, reuse valid artifacts, and keep a per-feature state record. Use when starting or resuming a migration, deciding the next task for a feature, or summarizing verification coverage.
---

# Migration Orchestration

Choose and record php2go work for one feature or change at a time. The other Skills are a toolbox, not mandatory stages: invoke only those that close an actual gap, and do not recreate valid artifacts. This Skill selects and records work. It does not produce verdicts or replace another Skill's procedure.

Read the [workspace convention](references/workspace-convention.md) for artifact locations and identifiers, the [state record](references/state-record.md) for the tracking format, and the Evidence Protocol's [artifact reuse](../equivalence-check/references/evidence-protocol.md#artifact-reuse) and [status and aggregation](../equivalence-check/references/evidence-protocol.md#status-and-aggregation) rules. Those documents own the shared rules.

## Procedure

1. Inspect the environment: which target-project artifacts already exist and which tools (PHP runtime, database engine, Go toolchain, test and comparison tooling) are actually available. Record unavailable capabilities as limits instead of assuming them.
2. Select one feature or change. Locate its existing artifacts and check each against its dependencies under the artifact reuse rules. Mark each `valid`, `stale`, or `missing`, with the invalidation reason for stale ones. Widen the scope when a shared dependency changed and its impact cannot be bounded.
3. Pick the Skills that close the gaps:

   | Gap for the selected feature | Skill |
   | --- | --- |
   | No valid inventory, or unexamined entry points and SQL sites | `php-analyze` |
   | PHP/Go semantic divergence suspected or unconfirmed on the reference runtime | `php-edge-cases` |
   | Contract or scenarios missing, or baseline changed | `behavior-extract` |
   | Reference observations missing or stale | `fixture-capture` |
   | Database work without a valid access plan (skip when the feature has none) | `sql-extract` |
   | Candidate missing, or a diff needs a code fix | `go-migrate` |
   | Candidate changed, or comparison evidence missing or stale | `equivalence-check` |
   | Original or candidate risk not yet reviewed | `security-review` |
   | An identified risk needs interim mitigation | `virtual-patch` |

   A Skill needs its inputs, not a prior stage. Comparison needs a valid contract, reference fixtures, and a candidate; it does not need analysis to be rerun. A Go-only change leaves valid PHP-side artifacts in place.
4. Run the selected Skills, or hand the task off with the artifacts to reuse. After each milestone, update the state record with artifact locations, identifying revisions or hashes, statuses, and blockers.
5. Report the selected feature, reused artifact IDs, invalidated dependencies, evidence status, and the next required task.

## Boundaries

- Take verdicts only from comparison evidence. A feature without valid evidence stays `NOT COVERED`; do not promote it because artifacts exist or the candidate builds.
- Keep `release_acceptance` separate from `equivalence_verdict`. An approved security change does not turn a `MISMATCH` into `PASS`.
- Invoke `virtual-patch` only when a risk needs temporary mitigation, never as a default step.
- Do not start comparison while its required inputs are missing or stale. Report the gap and the Skill that closes it.
