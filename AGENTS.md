# Repository Instructions

## Purpose and Current Scope

php2go provides eight Agent Skills for PHP-to-Go migration: seven core migration Skills and `virtual-patch` for temporary defenses during staged migration. Skills guide the Agent; executable tools determine equivalence.

The repository contains Skills, policy/format references, and a standalone Skill packager. It does not yet implement the `equiv` CLI, capture tools, automatic SQL extraction, target application code, a proxy binary, execution adapters, or machine-validation schemas. Do not describe planned capabilities as available.

Keep initial implementation focused on a small independent verifier. A general compiler, custom IR, or complete PHP compatibility runtime is outside the initial scope.

## Documentation Ownership

Edit each rule in its owning document. Other documents should link to the owner and describe only task-specific actions, inputs, and outputs. Do not maintain parallel policy summaries in this file or in Skill procedures.

| Subject | Authoritative document |
| --- | --- |
| Database library choice, SQL/schema preservation, binding, dynamic tables, result mapping, transactions | [Database policy](skills/sql-extract/references/database-policy.md) |
| Contract fields, comparison semantics, normalization declarations, baseline changes | [Behavior Contract](skills/behavior-extract/references/behavior-contract.md) |
| Execution validity, verdicts/aggregation, coverage, evidence retention, security change records | [Evidence Protocol](skills/equivalence-check/references/evidence-protocol.md) |
| Proxy trust/protocol requirements, patch lifecycle, rollback and PHP retirement | [Virtual Patch Plan](skills/virtual-patch/references/patch-plan.md) |

Read the relevant owner before work in that area. These references are instructions and design formats, not implemented schemas or verification tools. If a procedure conflicts with its owner, correct the procedure instead of introducing a second policy.

## Documentation and Layout

- Keep README.md focused on motivation, the rationale for Go, the incremental migration story, and accurate status.
- Write all content under `skills/`, including metadata, comments, and examples, in English. Keep these instructions in English; README.md is Korean.
- Each Skill has `SKILL.md` with YAML `name` and `description`; match its name to its directory.
- Keep reusable rules in the owners above. A new owner is warranted only for a distinct subject, not to duplicate an existing policy.
- Planned verifier code belongs in `cmd/equiv/`; planned machine-validation schemas belong in `contracts/schema/`. Naming these paths does not implement them.

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
| [virtual-patch](skills/virtual-patch/SKILL.md) | Derive interim defenses and retire replaced PHP features | Patch Plan / Proxy implementation / Retirement evidence |

Start with analysis, contracts, and fixtures, then extract SQL, implement Go, compare, and revise. Use security review for relevant findings and `virtual-patch` when protection is needed during the transition. Consult each Skill for its procedure rather than repeating it here.

## Standalone Skill Packaging

Source Skills may reference an owning document in another Skill folder; that is a document dependency, not a requirement to install or invoke the sibling Skill. Do not install a raw folder alone when its references leave that folder.

With Node.js available, package a Skill into a new destination:

```text
node scripts/package-skill.mjs go-migrate dist/go-migrate
```

The packager copies the selected Skill and its transitive local document dependencies, rewriting links to stay inside the package. Install the resulting folder in the host Agent's discovery path. It does not include sibling Skill entrypoints unless explicitly referenced, invoke Skills, or access the network. Existing destinations are rejected.

Treat packaged references as generated distribution artifacts. Change the canonical source and regenerate packages; do not edit or commit distribution copies. `dist/` is ignored. Apply the same packaging process to any of the eight Skills.

## Verification and Reporting

For documentation changes, check Skill frontmatter, Markdown links/fences, UTF-8, English Skill content, and accurate status. Use the standard Skill validator when available and disclose alternative checks when unavailable. Verify standalone packages resolve all local links without the source repository, especially after changing cross-Skill references.

For executable changes, run relevant build and behavior checks. For SQL or verifier changes, derive required checks and result reporting from the owning policy. Do not claim unavailable tools or unexecuted checks succeeded.

Start verifier implementation with a small PHP/Go read scenario; add stateful observation adapters incrementally. Establish runnable command syntax and exit codes when implemented rather than documenting nonexistent commands.
