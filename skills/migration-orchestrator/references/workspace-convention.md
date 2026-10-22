# Workspace Convention Draft v0

This is a tool-neutral convention for naming and locating migration artifacts in a target project, so each Skill can find what the others produced. It is a suggestion, not an implemented schema. When the target project already keeps equivalent artifacts, reuse its structure and record the mapping in the [state record](state-record.md). References between artifacts use paths relative to the target project root.

This document owns the suggested layout, identifier patterns, and handoffs. Content rules stay with each artifact's owner: [inventory format](../../php-analyze/references/inventory-format.md), [Behavior Contract](../../behavior-extract/references/behavior-contract.md), [capture manifest](../../fixture-capture/references/capture-manifest.md), [database policy](../../sql-extract/references/database-policy.md), and [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md).

## Suggested Layout

```text
<target-project-root>/
├── WORKSPACE_STATE.yaml                 # State record (migration-orchestrator)
└── migration/
    ├── inventory/
    │   ├── analysis-report.md           # Human-readable summary from php-analyze
    │   ├── feature-inventory.yaml       # Feature boundaries
    │   └── sql-inventory.yaml           # Original SQL call sites and branches
    ├── contracts/
    │   └── <feature-id>.yaml            # Behavior Contract per feature
    ├── fixtures/
    │   └── <snapshot-id>/
    │       └── manifest.json            # Capture manifest; layout defined by its format
    ├── security/
    │   ├── security-report.md
    │   └── changes/
    │       └── <change-id>.yaml         # Security change records
    └── reports/
        └── <run-id>/
            ├── evidence-report.json     # Evidence report for one comparison run
            └── diffs/                   # Per-scenario channel diffs
```

Database schema, static SQL, and access code follow the [database policy's suggested layout](../../sql-extract/references/database-policy.md#suggested-target-layout). Go source and comparison scripts follow the target project's own structure. These are target conventions, not paths implemented in this repository.

## Identifiers

| Identifier | Pattern | Example | Use |
| --- | --- | --- | --- |
| `feature_id` | `<domain>.<action>`, lowercase, dot-separated | `board.read` | Key across the inventory, contract, and state record |
| `scenario_id`, `step_id` | kebab-case | `public-page-2`, `read-page` | Unique within a feature's contract and its capture manifest |
| `snapshot_id` | `<kebab-case>-v<int>` | `board-public-page2-v1` | Immutable reference to a captured initial state |
| `query_site_id` | `<file>:<line>:<branch>` | `bbs/board.php:142:default` | Links a PHP SQL construction site to its access plan; valid only for the recorded source revision |
| `change_id` | `SEC-<YYYY>-<NNN>` | `SEC-2026-001` | Links a security change record to the mismatches it explains |
| `run_id` | UTC timestamp, `YYYYMMDDThhmmssZ` | `20261022T031500Z` | One comparison run and its evidence report |

An identifier is never reused for different content. A changed source revision can move line numbers, so re-confirm `query_site_id` values under the Evidence Protocol's [artifact reuse](../../equivalence-check/references/evidence-protocol.md#artifact-reuse) rules before relying on them.

## Handoffs

The table shows where an artifact is normally produced and read. Skills are a toolbox, not stages: a consumer reuses a valid existing artifact instead of requesting it again, and a missing producer is a gap to fill only when the selected feature needs it.

| Producer | Artifact | Typical consumers |
| --- | --- | --- |
| `php-analyze` | Feature and SQL inventories | `behavior-extract`, `sql-extract`, `go-migrate`, `equivalence-check`, `security-review` |
| `php-edge-cases` | Divergence findings, probe outputs | `php-analyze`, `behavior-extract`, `go-migrate` |
| `behavior-extract` | Behavior Contract | `fixture-capture`, `go-migrate`, `equivalence-check`, `security-review` |
| `fixture-capture` | Fixtures and capture manifest | `go-migrate`, `equivalence-check` |
| `sql-extract` | Schema, static SQL, and access plans | `go-migrate`, `equivalence-check` |
| `go-migrate` | Go candidate and Migration Notes | `equivalence-check`, `security-review` |
| `security-review` | Security report and change records | `behavior-extract`, `go-migrate`, `equivalence-check` |
| `equivalence-check` | Evidence report and diffs | `migration-orchestrator`, `go-migrate`, `security-review` |

`virtual-patch` artifacts follow the [Virtual Patch Plan](../../virtual-patch/references/patch-plan.md).
