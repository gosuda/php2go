# Repository Instructions

## Purpose and Current Scope

php2go provides nine task-specific Agent Skills for PHP-to-Go migration. They are a toolbox selected per feature, not nine mandatory stages. `virtual-patch` is optional and applies only when an identified risk needs interim mitigation. Skills guide the Agent; executable tools determine equivalence.

The repository is a Skills-first collection of workflows, policy/format references, and a standalone Skill packager. It does not require a repository-owned verifier CLI, capture engine, proxy product, or migration runtime.

The Agent produces application code, proxy code/configuration, fixtures, and executable checks in the target project as needed. Prefer that project's existing tools; fill concrete gaps with minimal target-specific scripts or adapters. Executable evidence is required, but a particular CLI or framework is not. Tool selection and comparison requirements belong to the Evidence Protocol.

Extract a common tool into this repository only after actual migrations demonstrate repeated mechanics worth maintaining. Do not make a generic verifier, gateway, compiler, or PHP compatibility runtime a prerequisite for using the Skills. The packaging script distributes Skills; it is not a migration runtime. Do not describe unexecuted work or unavailable capabilities as implemented.

## Documentation Ownership

Edit each rule in its owning document. Other documents should link to the owner and describe only task-specific actions, inputs, and outputs. Do not maintain parallel policy summaries in this file or in Skill procedures.

| Subject | Authoritative document |
| --- | --- |
| Database library choice, SQL/schema preservation, binding, dynamic tables, result mapping, transactions | [Database policy](skills/sql-extract/references/database-policy.md) |
| Contract fields, comparison semantics, normalization declarations, baseline changes | [Behavior Contract](skills/behavior-extract/references/behavior-contract.md) |
| Execution tool selection, validity, artifact reuse/invalidation, verdicts/aggregation, coverage, security changes | [Evidence Protocol](skills/equivalence-check/references/evidence-protocol.md) |
| Proxy capability planning, production readiness, patch lifecycle and PHP retirement | [Virtual Patch Plan](skills/virtual-patch/references/patch-plan.md) |
| Version-dependent PHP runtime semantics that diverge from Go defaults, with detection patterns and boundary inputs | [PHP semantics catalog](skills/php-edge-cases/SKILL.md) |
| Placement, consolidation, scope, and evidence of PHP compatibility code in Go | [PHP compatibility boundary](skills/go-migrate/SKILL.md#php-compatibility-boundary) |

Read the relevant owner before work in that area. These references are instructions and design formats, not implemented schemas or verification tools. If a procedure conflicts with its owner, correct the procedure instead of introducing a second policy.

## Documentation and Layout

- Keep README.md focused on motivation, the rationale for Go, the incremental migration story, and accurate status.
- Write all content under `skills/`, including metadata, comments, and examples, in English. Keep these instructions in English; README.md is Korean.
- Each Skill has `SKILL.md` with YAML `name` and `description`; match its name to its directory.
- Keep reusable rules in the owners above. A new owner is warranted only for a distinct subject, not to duplicate an existing policy.
- Application-specific code, checks, proxy configuration, and optional schemas belong in the target project. No `cmd/equiv/` or `contracts/schema/` implementation is mandated here. Keep reference formats tool-neutral.

## Skill Routing

| Skill | Task | Primary artifacts |
| --- | --- | --- |
| [php-analyze](skills/php-analyze/SKILL.md) | Analyze dependencies, entry points, state, and features | Analysis Report / Feature Inventory |
| [behavior-extract](skills/behavior-extract/SKILL.md) | Extract contracts and scenarios | Behavior Contract |
| [fixture-capture](skills/fixture-capture/SKILL.md) | Capture reproducible reference executions | Fixtures / Capture Manifest |
| [sql-extract](skills/sql-extract/SKILL.md) | Extract SQL and plan access/mapping | SQL Inventory / SQL files / Access plan |
| [go-migrate](skills/go-migrate/SKILL.md) | Implement and revise Go | Go source / Migration Notes |
| [equivalence-check](skills/equivalence-check/SKILL.md) | Execute comparisons and interpret diffs | Diff / Evidence Report |
| [security-review](skills/security-review/SKILL.md) | Review risks and proposed fixes | Security Report / Change Records |
| [virtual-patch](skills/virtual-patch/SKILL.md) | Temporarily mitigate an identified risk when interim protection is needed | Patch Plan / Target-specific proxy source or configuration / Retirement evidence |
| [php-edge-cases](skills/php-edge-cases/SKILL.md) | Find and confirm PHP semantics that diverge from Go on the reference runtime | Divergence findings / Boundary scenarios / Probe outputs |

Choose one feature or change, inspect its existing artifacts, and invoke only the Skills needed to close the actual gaps. Apply [artifact reuse rules](skills/equivalence-check/references/evidence-protocol.md#artifact-reuse) before repeating work. Reading a required policy does not mean invoking its owning Skill.

- If analysis, contract, and fixtures are valid, implement or repair the Go feature and compare it; do not recreate those inputs.
- Use `sql-extract` for missing or changed database access plans; skip it for features without database work. Recapture only fixtures invalidated by relevant changes or missing scenarios.
- Use `security-review` for relevant risks. Invoke `virtual-patch` only when an identified risk needs temporary mitigation; ordinary feature migration does not invoke it. Preserve all target application features, treating missing proxy capabilities as implementation tasks rather than permanent exclusions.

Record the selected feature, reused artifact IDs, invalidated dependencies, and next required task. A change to shared dependencies can affect multiple features; use the Evidence Protocol to determine the verification scope. Consult each Skill for its own procedure.

## Standalone Skill Packaging

Source Skills may reference an owning document in another Skill folder; that is a document dependency, not a requirement to install or invoke the sibling Skill. Do not install a raw folder alone when its references leave that folder.

With Node.js available, package a Skill into a new destination:

```text
node scripts/package-skill.mjs go-migrate dist/go-migrate
```

The packager copies the selected Skill and its transitive local document dependencies, rewriting links to stay inside the package. Install the resulting folder in the host Agent's discovery path. It does not include sibling Skill entrypoints unless explicitly referenced, invoke Skills, or access the network. Existing destinations are rejected.

Treat packaged references as generated distribution artifacts. Change the canonical source and regenerate packages; do not edit or commit distribution copies. `dist/` is ignored. Apply the same packaging process to any of the nine Skills.

## Verification and Reporting

For documentation changes, check Skill frontmatter, Markdown links/fences, UTF-8, English Skill content, and accurate status. Use the standard Skill validator when available and disclose alternative checks when unavailable. Verify standalone packages resolve all local links without the source repository, especially after changing cross-Skill references.

For executable changes, run relevant build and behavior checks. For SQL or verifier changes, derive required checks and result reporting from the owning policy. Do not claim unavailable tools or unexecuted checks succeeded.

Verify target-specific scripts and adapters against real scenarios, including deliberate mismatches when implementing a comparator. Record actual commands, tool/script versions, results, and limits under the Evidence Protocol. Repeated target work can motivate a shared utility later; do not create one speculatively or document nonexistent commands as available.
