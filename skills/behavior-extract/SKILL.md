---
name: behavior-extract
description: Extract Behavior Contracts and success, boundary, failure, and authorization scenarios from observable PHP behavior for migration to Go.
---

# Behavior Contract Extraction

Use analysis findings and observations of the original application to establish criteria independent of the candidate implementation. Read [references/behavior-contract.md](references/behavior-contract.md) for contract fields and examples.

## Procedure

1. Pin the feature ID and original source revision. When inputs are incomplete, distinguish contracts inferred from source from contracts confirmed by execution.
2. Define request method/path/query/body/headers and initial database, file, session, and authorization state. Preserve input representations that affect behavior, including repeated query parameters, missing values, and encoding.
3. Define observation scopes for status/headers/body, database, cookies/sessions, files, and external effects. Even for reads, check for view counter increments or session writes before declaring `none`.
4. Cover empty results, boundary values, invalid inputs, anonymous users, insufficient permissions, missing resources, and relevant failures alongside successful paths. Include multistep state transitions for authentication and writes.
5. Connect invariants to executable assertions. Mark natural-language requirements without an execution method as unverified; do not imply that they have been enforced as PASS criteria.
6. Define how to control time, randomness, and IDs first. If normalization is necessary, specify the exact field scope, algorithm, rationale, and relationships to preserve. Do not use broad exclusion rules that erase real differences.

## Baseline Management

- Use both the contract and original execution results as the baseline. Record conflicts and withhold a valid PASS until they are resolved.
- Do not change expected values because the candidate fails. Record original evidence, rationale, revision, and affected scenarios for contract changes.
- Link security fixes to separate approved change records rather than silently folding them into contracts. Keep unapproved proposals labeled as proposals.

Deliver a versioned Behavior Contract, feature-to-scenario mapping, unobserved items, and decisions requiring human review. Writing a contract does not establish completed verification.
