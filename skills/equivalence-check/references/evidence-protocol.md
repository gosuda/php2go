# Evidence Protocol Draft v0

These rules are intended for the verification CLI and adapters. This repository currently provides no runner, comparators, or machine-validation schemas.

This document owns execution validity, verdicts, aggregation, coverage, evidence retention, and security change records. [Behavior Contract](../../behavior-extract/references/behavior-contract.md) owns baseline management and declared comparison/normalization semantics; [database policy](../../sql-extract/references/database-policy.md) owns DB implementation requirements.

## Verdict Boundary

Use original PHP as the reference oracle. Executable comparisons determine equivalence; AI review, compilation, a successful query, or HTTP 200 alone cannot establish PASS. Report manual or AI observations as advisory. Missing tools or unexecuted checks must remain explicit rather than producing fabricated evidence or verdicts.

Observe every required channel and assertion in the contract, including important intermediate effects. Missing observations are not proof of no effect. A working proxy does not establish Go implementation coverage for forwarded PHP features. Passing observed scenarios does not prove equivalence for all possible inputs.

## Execution Validity

Each run must pin contract/fixture hashes, original and candidate revisions, runner/comparator versions, environment profiles, and initial snapshot digests. For a dirty working tree, retain a digest of the actual changes rather than identifying the source by revision alone.

Restore each scenario independently and preserve multistep state within each execution. Run PHP and Go on independent equivalent database/file/session/configuration state; never reuse state mutated by the other implementation. Record external effects through isolated doubles rather than real production effects. Parallel execution is allowed only when database, file, session, and service double namespaces can be isolated. Valid comparison requires successful pre-state checks, capture, and asynchronous completion conditions.

If infrastructure timeouts or capture errors prevent comparison, record `NOT COVERED` with the cause. An application timeout can itself be compared when it is a declared observation and the execution environment is valid. Report `UNSUPPORTED` when the tool lacks a required capability. Record conflicts between original execution and the contract and withhold PASS until resolution.

## Status and Aggregation

Retain results for individual observations.

| Status | Required conditions |
| --- | --- |
| `PASS` | Valid execution, all required observations/assertions present, and matching comparisons |
| `MISMATCH` | At least one difference established by valid comparison |
| `NOT COVERED` | Missing scenarios, unexecuted checks, validity failures, or missing evidence |
| `UNSUPPORTED` | Required execution, observation, or comparison capability absent from the tool |

If a single parent status is needed, aggregate in priority order `MISMATCH` > `UNSUPPORTED` > `NOT COVERED` > `PASS`, retaining counts and items for every status. A feature with zero required scenarios is `NOT COVERED`. Do not remove unexecuted features from the denominator. Aggregate PASS applies only to the declared scope.

A scenario cannot PASS when required database observations are missing, even if other observations were compared successfully. Conversely, retain an established HTTP difference alongside unsupported database observations. Do not collapse results in ways that discard information.

## Report Fields

- `report_version`, `run_id`, `started_at`, `finished_at`, and execution validity.
- Contract/fixture/source/candidate/environment/tool identifiers and hashes.
- Complete feature inventory and feature-to-scenario-to-step-to-observation/assertion mappings.
- Per-item status, error classification, diff location, safe expected/actual displays, and raw artifact paths and hashes.
- Applied normalization, ID mapping, and comparator rules and versions, with traceable evidence before and after transformation.
- Total required scenarios/observations, executed/compared counts, and counts by status. Do not use a single coverage percentage without a named metric and denominator.
- Unverified input regions, tables/paths/services outside observation scope, instability, tool limits, and remaining uncertainty.
- Approved security change IDs linked to relevant mismatches, distinguished from unapproved proposals.

When raw values contain secrets, include restricted artifact references and safe diffs in reports. Hashes do not replace actual comparison; record how reproduction artifacts can be accessed.

## Security Change Records

Record change ID, original evidence, risk, affected input/output/state scope, approver and approval evidence/timestamp, related contracts/scenarios/diffs, and regression verification. Approved differences remain `MISMATCH`. Separate release acceptance from functional equivalence verdicts and do not describe changes as approved without approval records.

## Determinism Boundaries

Identical input artifacts and comparator versions must produce identical verdicts. Record unstable repeated reference executions and control clock/RNG/concurrency/external dependencies. Apply only the comparison and normalization declarations permitted by the Behavior Contract.

Retain previous failures and every attempt when retrying execution. Do not select only the final passing attempt. Preserve raw observations so comparator changes can be reevaluated.
