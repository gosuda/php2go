# Repository Instructions

## Purpose and Current Scope

php2go provides Agent Skills for migrating PHP applications to Go while preserving observable behavior. Skills define the workflow; executable verification tools determine equivalence from reference PHP and candidate Go executions.

The repository currently contains eight Skills: seven core migration Skills and an analysis-driven `virtual-patch` Skill, with draft contract, evidence, SQL, and patch references. It does not yet implement the `equiv` CLI, capture tools, automatic SQL extraction, target application database access code, a proxy binary, database/session adapters, or machine-validation schemas. Do not describe planned capabilities as available.

Keep initial work focused on Skills and a small independent verifier. A general PHP-to-Go compiler, custom IR, or complete PHP compatibility runtime is outside the initial scope. Reuse existing analysis tools where appropriate; do not add a conversion engine merely to complete a Skill.

## Documentation and Layout

- Keep README.md focused on project motivation, the rationale for Go, and an accurate brief status. Put Agent workflows and contribution instructions here, and detailed task rules in the relevant Skill or reference.
- Write all content under `skills/` in English, including frontmatter descriptions, headings, instructions, comments, and example descriptions. Keep these repository instructions in English; README.md is Korean.
- Each Skill directory contains `SKILL.md` with YAML frontmatter `name` and `description`. Match the name to its directory and retain meaningful activation descriptions.
- Package required `references/` files with their Skill. Do not assume sibling Skills are installed; standalone instructions must identify the artifacts they need.
- Planned verifier implementation belongs in `cmd/equiv/`; planned machine-validation schemas belong in `contracts/schema/`. These paths are not implemented merely because this document names them.

## Skill Routing

| Skill | Responsibility | Primary artifacts |
| --- | --- | --- |
| [php-analyze](skills/php-analyze/SKILL.md) | Identify dependencies, entry points, state, and feature boundaries | Analysis Report / Feature Inventory |
| [behavior-extract](skills/behavior-extract/SKILL.md) | Define observable contracts and scenarios | Behavior Contract |
| [fixture-capture](skills/fixture-capture/SKILL.md) | Record reproducible reference executions | Fixtures / Capture Manifest |
| [sql-extract](skills/sql-extract/SKILL.md) | Extract existing SQL and define safe dynamic construction and result mapping | SQL Inventory / SQL files / Query construction and mapping plan |
| [go-migrate](skills/go-migrate/SKILL.md) | Implement and revise Go against contracts | Go source / Migration Notes |
| [equivalence-check](skills/equivalence-check/SKILL.md) | Compare executable observations and report coverage | Diff / Evidence Report |
| [security-review](skills/security-review/SKILL.md) | Review original/new risks and track approved changes | Security Report / Change Records |
| [virtual-patch](skills/virtual-patch/SKILL.md) | Derive temporary proxy defenses from PHP analysis and retire them with replaced PHP features | Patch Plan / Finding-to-rule map / Proxy implementation / Verification and retirement evidence |

For Agent use, copy or link the required Skill folders, including their references, into the host Agent's discovery path. Installation paths and discovery depend on the host Agent. Provide the seven core Skills for the full migration workflow; use `virtual-patch` when analyzed PHP risks need temporary protection during staged migration.

Example task:

```text
Migrate this PHP application to Go.
Preserve observable behavior.
Use the functional-equivalence skills.
```

## Migration Workflow

1. Identify available tools, runtimes, and isolated test environments. Record the original revision, dependencies, feature inventory, and state boundaries.
2. Extract feature contracts and success, boundary, authorization, and failure scenarios. Distinguish source inferences from runtime evidence.
3. Capture reproducible reference fixtures and raw observations in isolation.
4. Extract existing schemas and SQL, implement database access with `database/sql`, and implement Go against the contracts and fixtures.
5. Run PHP and Go with equivalent inputs and independent clones of the same initial state. Compare all declared observations.
6. Revise the candidate, rerun affected scenarios, and report differences, coverage gaps, unsupported behavior, and uncertainty.

Read the [Behavior Contract draft](skills/behavior-extract/references/behavior-contract.md), [Evidence Protocol draft](skills/equivalence-check/references/evidence-protocol.md), and [SQL/database access policy](skills/sql-extract/references/database-policy.md) when working on those artifacts. These are design references, not executable schemas.

## SQL-first Database Boundary

- Use SQL-first + `database/sql` as the initial default. Preserve existing schema, table names, and SQL semantics; keep database redesign and query optimization separate until equivalence is established.
- Store static queries in `.sql` files and implement minimal Go construction for dynamic queries. Suggested target paths are `db/schema.sql`, `db/queries/`, and `internal/store/`; schema files document the existing schema, not a redesign. These paths are target conventions, not existing repository artifacts. Make SQL files available in deployed builds, for example through embedding.
- Bind all data values through driver placeholders. Construct identifiers, sort directions, and SQL fragments only from validated trusted allowlists or fixed program fragments. Never concatenate request values into SQL. Record how runtime board creation updates the authoritative table registry and preserves authorization.
- Use context-aware `QueryContext`, `QueryRowContext`, and `ExecContext` in focused database access code. Explicitly map `Scan` targets, NULL, numeric/time conversions, `sql.ErrNoRows`, row iteration errors, affected rows, and generated IDs. Pin driver versions and record connection settings.
- Preserve statement order, transaction boundaries, isolation, rollback, and connection-local behavior. Run transactional queries through the same `*sql.Tx`; do not mix `*sql.DB` calls into that transaction. DDL application, fixture restoration, and observation tools remain separate from ordinary application queries.
- Do not require sqlc or introduce an ORM in the initial migration. Consider sqlx only when repetitive mapping demonstrates a concrete need and verify its runtime mapping semantics. Do not mix database libraries by default. Lack of sqlc support is not a reason to mark a dynamic query unsupported.
- Parameterization and input validation are separate changes. Do not add `LIMIT 1`, integer parsing, or new HTTP 400 responses without checking original semantics. Track intentional security differences separately.

## Analysis-Driven Virtual Patches

Use `virtual-patch` to connect PHP analysis and security findings to feature-specific temporary Go proxy rules. Do not build a general WAF. Default requests go to a configured PHP HTTP upstream; explicitly matched routes receive evidence-backed defenses or verified Go handling. Define ownership, matching, expected changes, review/expiry behavior, rollback, and permanent removal criteria before rollout.

Progress feature by feature: analyze PHP → derive and verify a temporary defense → run protected PHP while implementing Go → verify the permanent fix → cut over and retire the rule plus unused PHP routes/code/dependencies. Verify attack regressions without the rule before removal. Keep shared PHP dependencies and protection needed by remaining features or rollback paths; remove the final PHP runtime/upstream only after no consumers remain.

Verify direct PHP versus proxy pass-through separately from active patch or Go route behavior. Preserve intentional differences as `MISMATCH` with change records. Do not count proxied PHP features as migrated Go coverage. Keep forwarded-header trust, session compatibility, parsing differences, and response rewriting explicit. Never duplicate writes, fallback uncertain writes to another backend, or silently remove security protection on error/expiry. This Skill does not itself authorize live ingress or service changes.

## Equivalence Invariants

- Use original PHP as the reference oracle. Neither AI review, compilation, successful SQL execution, nor HTTP 200 alone establishes equivalence.
- Compare status, headers, body, database changes, cookies/sessions, files, and external effects within the contract scope. Include multistep authenticated and write scenarios where relevant.
- Restore equivalent initial database, file, session, and configuration state independently for each implementation. Never feed Go the database already modified by PHP. Preserve state within each multistep scenario and reset between scenarios.
- Record external effects through isolated doubles. Do not send real mail or mutate production data to capture fixtures.
- Control time, randomness, and generated IDs before normalizing. Any normalization must specify scope, rationale, and preserved relationships. Do not erase differences through blanket removal of IDs, timestamps, ordering, or HTML whitespace.
- Observe important intermediate effects and transactions when final state alone is insufficient. Missing observations are not evidence of no effect.
- Keep contracts and fixtures independent of candidate failures. Baseline changes need original evidence, rationale, revisions, and affected scenarios. Resolve contract/reference conflicts before PASS.
- Retain raw evidence, artifact hashes, tool versions, and all retry attempts. Do not select only a passing retry.

## Results and Security Changes

| Status | Meaning |
| --- | --- |
| `PASS` | Valid execution matches across every required observation/assertion in the declared scenario scope |
| `MISMATCH` | A valid comparison establishes a behavioral difference |
| `NOT COVERED` | Scenarios or valid execution evidence are insufficient |
| `UNSUPPORTED` | Required execution, observation, or comparison capability is not supported |

Record results at feature, scenario, and observation levels. Partial PASS does not establish whole-feature PASS. Preserve missing features in coverage denominators and report uncertainty separately from source line coverage. Follow the Evidence Protocol for aggregation and validity failures.

If tools are absent, report unexecuted checks and missing capabilities. Do not fabricate commands, generated artifacts, execution evidence, or verdicts. Passing observed inputs does not prove equivalence for all possible inputs.

Security fixes may intentionally change unsafe original behavior. Preserve actual diffs as `MISMATCH` and link a separate change record with scope, rationale, approver/approval evidence, and regression scenarios. Distinguish proposals from approved changes and release acceptance from equivalence. A review request does not itself authorize exploitation or changes to production systems.

## Verification and Reporting

For documentation changes, check Skill frontmatter, relative links, Markdown fences, UTF-8 encoding, and unfinished scaffolding. Confirm all Skill content remains English and that README status matches implemented artifacts. Use the standard Skill validator when available; disclose alternative checks and their limits when it cannot run.

For executable changes, run the relevant build and behavior checks. For SQL changes, verify placeholder arguments, identifier allowlists, result mapping, transaction behavior, and relevant database scenarios with the pinned driver and recorded database settings. Build or query execution success is separate from equivalence evidence. Do not claim checks were run when tools or environments are unavailable.

Start verifier implementation with a small PHP/Go read scenario and explicit status/header/body comparisons. Extend to independently restored database writes, multistep sessions, files, and external effect adapters. Report unsupported channels at every stage. The CLI should consume pinned contracts, fixtures, and run configuration and emit raw observations, structured diffs, and machine-readable results. Establish actual command syntax and exit codes with implementation; do not publish nonexistent runnable commands.
