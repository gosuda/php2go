# State Record Draft v0

Keep one record per target project to track which artifacts exist for each feature, whether they are still valid, and what to do next. This is a tool-neutral draft for human and Agent use, not an executable schema.

The record tracks work and never establishes a verdict. Verdict values come from comparison evidence under the [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md), which also owns artifact validity ([artifact reuse](../../equivalence-check/references/evidence-protocol.md#artifact-reuse)) and aggregation ([status and aggregation](../../equivalence-check/references/evidence-protocol.md#status-and-aggregation)). This format only stores the outcome of those decisions. Locations and identifiers come from the [workspace convention](workspace-convention.md).

## Rules

- Record each artifact as `valid`, `stale`, or `missing`. A `stale` artifact carries the changed dependency as `stale_reason`; existence alone is not validity.
- Set `equivalence_verdict` from the latest valid evidence report. A feature with no valid report is `NOT COVERED`. Never write `PASS` on the record's own authority.
- Keep `release_acceptance` (`PENDING`, `ACCEPTED`, `REJECTED`) separate from `equivalence_verdict`. An approved security change can make a release acceptable while the verdict stays `MISMATCH`. Use `ACCEPTED` only with an approval record.
- List every inventoried feature, including those with no scenarios. In `summary`, report counts per status and name the denominator; do not report a bare coverage percentage.
- Record unavailable tools under `tooling` as limits. Do not list a capability as available because a Skill would normally use it.

## Example

```yaml
state_version: 0
project: legacy-board-migration
source_revision: example-php-revision
candidate_revision: example-go-revision
tooling:
  php_runtime: {available: true, version: "8.1.27"}
  database_snapshot: {available: true, note: "restore script in scripts/restore-snapshot.sh"}
  go_toolchain: {available: true, version: "go1.24.0"}
  comparison: {available: false, note: "no comparison script yet; scenarios cannot be executed"}
features:
  - feature_id: board.read
    artifacts:
      analysis: {path: migration/inventory/feature-inventory.yaml, status: valid}
      contract: {path: migration/contracts/board.read.yaml, revision: 2, status: valid}
      fixtures:
        - snapshot_id: board-public-page2-v1
          path: migration/fixtures/board-public-page2-v1/manifest.json
          status: stale
          stale_reason: "contract revision 1 -> 2 added scenario invalid-page"
      access_plan: {path: migration/inventory/sql-inventory.yaml, status: valid}
      candidate: {path: internal/handler/board, status: valid}
      evidence: {run_id: null, status: missing}
      security_changes: [SEC-2026-001]
    equivalence_verdict: NOT COVERED
    release_acceptance: PENDING
    next_task: "Recapture fixtures for scenario invalid-page, then compare"
    blockers:
      - "No comparison tooling in the environment"
  - feature_id: board.write
    artifacts:
      analysis: {path: migration/inventory/feature-inventory.yaml, status: valid}
      contract: {path: null, status: missing}
      fixtures: []
      access_plan: {path: null, status: missing}
      candidate: {path: null, status: missing}
      evidence: {run_id: null, status: missing}
      security_changes: []
    equivalence_verdict: NOT COVERED
    release_acceptance: PENDING
    next_task: "Extract the Behavior Contract for the authenticated write flow"
    blockers: []
summary:
  denominator: inventoried features
  total_features: 2
  by_verdict: {PASS: 0, MISMATCH: 0, "NOT COVERED": 2, UNSUPPORTED: 0}
```
