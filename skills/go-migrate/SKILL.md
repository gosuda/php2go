---
name: go-migrate
description: Implement Go code that preserves observable PHP behavior using Behavior Contracts and fixtures, and revise it based on execution diffs.
---

# Go Implementation

Implement the selected feature from available source, inventory, contracts, fixtures, and SQL access plans where applicable. Check existing inputs against the [artifact reuse rules](../equivalence-check/references/evidence-protocol.md#artifact-reuse) and obtain only missing or stale prerequisites. A feature without database work does not require SQL extraction. Respect the target Go project's structure.

Read the [database policy](../sql-extract/references/database-policy.md) for DB implementation, [contract baseline rules](../behavior-extract/references/behavior-contract.md#baseline-management) before proposing expectation changes, and [Evidence Protocol](../equivalence-check/references/evidence-protocol.md) for comparison results and security changes. These documents own the shared rules; this Skill defines implementation work.

## Implementation Tasks

- Map request parsing, routing/rewrites, status/headers/body, errors, and redirects to the feature contract. Investigate PHP/Go differences in missing/null/empty values, loose comparison, truthiness, numeric strings, arrays, and encoding at their actual call sites, using the [PHP semantics catalog](../php-edge-cases/SKILL.md) for the reference PHP version. Implement required PHP semantics in focused, tested helpers.
- Implement extracted query/access plans under the database policy and attach original-to-Go function mappings to Migration Notes. Resolve missing or ambiguous extraction artifacts before implementing the affected query.
- Implement sessions, authentication/authorization, CSRF, cookie behavior, files, and external service adapters. Document compatibility boundaries with existing stores.
- Add injection points for clock/RNG/external dependencies needed by the scenario environment, keeping fixture-specific behavior out of service code.
- Implement success, error, and authorization paths for the chosen feature boundary and identify remaining paths.

## Revision Loop

1. Build and test the feature and run the database policy's relevant checks for any DB work.
2. Run the target project's selected executable checks for affected scenarios and collect their output under the Evidence Protocol. Fill missing checks with focused tests/scripts rather than waiting for a shared verifier.
3. Trace diffs to input interpretation, response generation, state transitions, or environment configuration and revise the candidate. Check diffs against known PHP/Go semantic differences before changing expectations. Route proposed baseline or security changes through their owning references.
4. Rerun affected and related regression scenarios. If the same cause repeats without progress, record the missing information and unresolved difference and continue other implementable work.

Deliver Go source, execution/configuration instructions, feature/function mappings, build/test evidence, comparison references, and unresolved implementation or verification work.
