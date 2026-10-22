# Evidence Protocol Draft v0

These rules apply to executable checks assembled for the target project. A repository-owned verifier CLI, adapter framework, or machine-validation schema is not required.

This document owns execution tool selection, artifact reuse, execution validity, verdicts, aggregation, coverage, evidence retention, and security change records. [Behavior Contract](../../behavior-extract/references/behavior-contract.md) owns baseline management and declared comparison/normalization semantics; [database policy](../../sql-extract/references/database-policy.md) owns DB implementation requirements.

## Execution Tools

Use the target project's existing test runners, HTTP clients, database tools, and isolation facilities where they meet the contract. The Agent may combine them or write minimal capture, assertion, comparison, and report scripts in that project. A dedicated `equiv` CLI is neither required nor assumed to exist.

Turn missing capture or comparison capabilities into concrete target tasks. Prefer extending an existing test or adding a focused adapter over building a general harness. The absence of a named CLI does not itself make a feature unsupported. If execution remains impossible because a runtime, service, access, or observation capability is unavailable, record the actual limitation under this protocol.

Express comparisons and assertions as executable checks with explicit rules. The Agent can author and explain those checks but must not replace their results with an AI judgment of equivalence. Capture raw observations, check process failures, and distinguish mismatches from setup/capture failures. If existing tools only return assertion results, assemble the required evidence report from those outputs using a reproducible mapping to this protocol's statuses.

Validate new comparison logic with a known matching case and deliberate response/state differences relevant to its scope. Check that required missing observations cannot accidentally PASS. Pin tool versions and script contents/hashes, retain commands and assertion output, and record what was actually observed. Manually inspecting two responses is advisory, not a substitute for executable checks.

Use project-specific scripts first. Promote repeated, proven mechanics into shared tooling only when real migrations justify it; the shared tooling is an optimization, not a prerequisite for verification.

## Artifact Reuse

Before invoking a task, locate existing artifacts for the selected feature and check their recorded provenance and dependencies. Reuse valid inputs instead of rerunning the entire Skill collection. An artifact's existence alone does not make it valid.

| Artifact | Dependencies to check before reuse |
| --- | --- |
| Analysis / SQL access plan | Original source revision and relevant files, schema/configuration, dependency versions, and feature scope |
| Behavior Contract | Original evidence and revision, contract revision, feature boundaries, and declared scenarios/comparisons |
| Reference fixtures | Original revision, contract hash, initial snapshot, runtime/settings, capture version, observation completeness, and stability |
| Comparison evidence | All recorded run inputs, including candidate revision/content hashes, comparator/assertion versions, and required observation scope |

Mark changed, missing, or unverifiable dependencies as stale and record affected features/scenarios. Rebuild or recapture the affected artifacts and rerun affected comparisons. Changes to shared schema, authentication/session behavior, bootstrap code, or runtime settings may invalidate several features; broaden the scope when impact cannot be bounded. Keep unaffected valid artifacts and historical versions.

A Go-only implementation change does not by itself invalidate unchanged PHP analysis or reference fixtures, but it does require new comparison evidence for affected candidate behavior. Never relabel an older PASS as verification of a changed candidate. New scenarios need new observations rather than being inferred from existing coverage. Follow the Behavior Contract's baseline rules for actual source, contract, or initial-state revisions.

Record reused artifact IDs/hashes, dependency checks, invalidation reasons, and the selected verification scope. This policy defines validity decisions; it does not imply that an artifact cache or invalidation engine is implemented.

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

A record starts as `PROPOSED` and has no `approval` block until a human approver's evidence exists; an Agent never supplies an approver or approval timestamp. Do not edit the original fixtures or rewrite the baseline contract so that a fix appears identical to the vulnerable behavior. Every record names normal-input scenarios that must keep passing with no difference. The ID pattern and file location come from the [workspace convention](../../migration-orchestrator/references/workspace-convention.md#identifiers).

The following values are illustrative and the field names are not an executable schema:

```yaml
change_id: SEC-2026-001
status: APPROVED                # PROPOSED | APPROVED | REJECTED
feature: board.read
risk: {type: "CWE-89 SQL injection", severity: critical}
original_evidence:
  source_location: bbs/board.php:54
  description: Request values are interpolated directly into the SQL string.
  reproduced_in_isolated_env: true
  reproduction_scenario: sqli-boolean-probe
intentional_difference:
  input_scope: bo_table outside the registry, or stx containing SQL metacharacters
  php_reference_behavior: Executes the injected fragment or prints a database error (HTTP 200).
  go_candidate_behavior: Binds stx as a value (empty result, HTTP 200); rejects an unknown bo_table with the standard not-found alert.
affected:
  divergence_scenarios: [sqli-boolean-probe, unknown-table-injection]
  expected_verdict: MISMATCH
  regression_pass_scenarios: [public-page-2, search-normal-keyword, empty-search-result]
approval:                       # omit while PROPOSED
  approved_by: <approver identity>
  approved_at: <timestamp>
  reference: <ticket or review link>
```

## Determinism Boundaries

Identical input artifacts and comparator versions must produce identical verdicts. Record unstable repeated reference executions and control clock/RNG/concurrency/external dependencies. Apply only the comparison and normalization declarations permitted by the Behavior Contract.

Retain previous failures and every attempt when retrying execution. Do not select only the final passing attempt. Preserve raw observations so comparator changes can be reevaluated.
