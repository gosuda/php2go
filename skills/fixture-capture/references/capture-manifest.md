# Capture Manifest Format Draft v0

This is a tool-neutral draft format for human review, not an executable schema. Field names are illustrative identifiers; serialize them in whatever form the target project's capture and comparison tools read.

This document owns the shape of a fixture snapshot and its manifest. The [fixture-capture Skill](../SKILL.md) owns the capture procedure, the [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md) owns execution validity and reuse of fixtures, and the [Behavior Contract](../../behavior-extract/references/behavior-contract.md) owns the observation channels and normalization rules that the manifest must satisfy. Identifier patterns and locations come from the [workspace convention](../../migration-orchestrator/references/workspace-convention.md).

## Snapshot Layout

Each snapshot holds its initial state and the raw observations of every step. This layout is a suggestion; keep the same information if the project's tools prefer another arrangement.

```text
migration/fixtures/board-public-page2-v1/
├── manifest.json
├── initial/
│   ├── schema.sql              # Deployed DDL
│   ├── data.sql                # Sanitized initial data
│   ├── registry.json           # Dynamic table/board registry state, when the scenario uses it
│   ├── sessions.json
│   └── files_tree.json
└── scenarios/
    └── public-page-2/
        └── 01-read-page/
            ├── request.raw
            ├── response.raw
            ├── response_meta.json
            ├── cookie_jar_after.json
            ├── db_after.json
            ├── db_delta.json
            ├── session_after.json
            ├── files_delta.json
            └── external_events.json
```

## Manifest

Digests are shown as `sha256:<digest>` placeholders.

```json
{
  "manifest_version": 0,
  "snapshot_id": "board-public-page2-v1",
  "feature_id": "board.read",
  "contract_ref": {
    "path": "migration/contracts/board.read.yaml",
    "revision": 1,
    "sha256": "sha256:<digest>"
  },
  "reference_source": {
    "revision": "example-php-revision",
    "dirty_tree_digest": null
  },
  "capture": {
    "tool": "project capture script",
    "version": "<tool version or script hash>",
    "restore_procedure": "scripts/restore-snapshot.sh board-public-page2-v1"
  },
  "runtime_profile": {
    "id": "php-reference-v1",
    "php_version": "8.1.27",
    "extensions": ["mysqli", "mbstring", "session"],
    "db_engine": "MySQL 8.0.36",
    "charset": "utf8mb4",
    "collation": "utf8mb4_general_ci",
    "sql_mode": "NO_ENGINE_SUBSTITUTION",
    "timezone": "Asia/Seoul",
    "clock_policy": {"mode": "fixed", "fixed_timestamp": "2026-10-22T03:00:00Z"},
    "rng_policy": {"mode": "seeded", "seed": 42}
  },
  "initial_state": {
    "schema_sha256": "sha256:<digest>",
    "data_sha256": "sha256:<digest>",
    "registry_sha256": "sha256:<digest>",
    "sessions_sha256": "sha256:<digest>",
    "files_tree_sha256": "sha256:<digest>",
    "observed_tables": ["g5_board", "g5_write_notice", "g5_member"],
    "auto_increment_seeds": {"g5_write_notice": 105}
  },
  "repeatability": {
    "runs_executed": 2,
    "deterministic": true,
    "nondeterministic_fields": []
  },
  "scenarios": [
    {
      "scenario_id": "public-page-2",
      "pre_state_verified": true,
      "steps": [
        {
          "step_id": "read-page",
          "channels": {
            "http": "COMPLETE",
            "database": "COMPLETE",
            "sessions": "COMPLETE",
            "cookies": "COMPLETE",
            "files": "COMPLETE",
            "external_effects": "COMPLETE"
          },
          "artifacts": {
            "scenarios/public-page-2/01-read-page/request.raw": "sha256:<digest>",
            "scenarios/public-page-2/01-read-page/response.raw": "sha256:<digest>",
            "scenarios/public-page-2/01-read-page/db_delta.json": "sha256:<digest>"
          }
        }
      ]
    }
  ]
}
```

## Field Semantics

- **Channels** use the contract's observation channels. A status is `COMPLETE`, `PARTIAL`, `FAILED`, or `UNSUPPORTED` (the capture tool lacks the capability). Never record `COMPLETE` for a channel when a table, session store, or file path in scope was unreachable.
- **`pre_state_verified`** is true only when the restored database, session store, and file tree matched the `initial_state` digests before the scenario's first step.
- **`nondeterministic_fields`** lists any field that differed between repeated reference runs and could not be controlled. Each entry must correspond to a scoped normalization declaration in the contract; an unexplained entry leaves the fixture unusable as a baseline.
- **`artifacts`** lists every raw file of the step with its digest (the example is abbreviated), so a changed observation is detectable. Normalized copies are additional and never replace the raw files.
- **Secrets** are referenced by name and delivery mechanism, never stored in the manifest or the raw artifacts.
