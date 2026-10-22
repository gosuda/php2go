---
name: behavior-extract
description: Extract Behavior Contracts and success, boundary, failure, and authorization scenarios from observable PHP behavior for migration to Go.
---

# Behavior Contract Extraction

Use analysis findings and observations of the original application to establish criteria independent of the candidate implementation. Read [references/behavior-contract.md](references/behavior-contract.md), which owns contract fields, comparison declarations, baseline management, and examples.

Work on the selected feature's missing or changed declarations. Reuse valid analysis and existing contracts under the [artifact reuse rules](../equivalence-check/references/evidence-protocol.md#artifact-reuse) rather than rebuilding all contracts.

## Procedure

1. Pin the feature ID and original source revision. When inputs are incomplete, distinguish contracts inferred from source from contracts confirmed by execution.
2. Define request method/path/query/body/headers and initial database, file, session, and authorization state. Preserve input representations that affect behavior, including repeated query parameters, missing values, and encoding.
3. Define observation scopes for status/headers/body, database, cookies/sessions, files, and external effects. Even for reads, check for view counter increments or session writes before declaring `none`.
4. Cover empty results, boundary values, invalid inputs, anonymous users, insufficient permissions, missing resources, and relevant failures alongside successful paths. Include multistep state transitions for authentication and writes. Add inputs that separate PHP and Go semantics, such as non-numeric strings, repeated or bracketed parameters, invalid UTF-8, and out-of-range dates; see the [PHP semantics catalog](../php-edge-cases/SKILL.md) for scenario lists.
5. Connect invariants to executable assertions. Mark natural-language requirements without an execution method as unverified; do not imply that they have been enforced as PASS criteria.
6. Write determinism and normalization declarations using the contract reference's comparison semantics, linking each declaration to the fields and relationships observed in the original.

Deliver a versioned Behavior Contract, feature-to-scenario mapping, unobserved items, and decisions requiring human review. Apply the reference's baseline management procedure to proposed revisions or conflicts.
