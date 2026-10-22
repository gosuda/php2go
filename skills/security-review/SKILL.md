---
name: security-review
description: Review unsafe original behavior and vulnerabilities introduced during PHP-to-Go migration, tracking approved security changes separately from functional equivalence results. Use when auditing injection, XSS, CSRF, or authorization risks or preparing a security change record.
---

# Migration Security Review

Review original and candidate risks within actual feature boundaries. Read the [Evidence Protocol](../equivalence-check/references/evidence-protocol.md#security-change-records) for the authoritative security change format and its relationship to equivalence results.

## Review Scope

- Examine authentication/authorization, session fixation, cookie attributes, CSRF, SQL injection, escaping/XSS, file uploads and path handling, command execution, deserialization, outbound requests, and secret exposure in the original and candidate, tied to actual features.
- Pay particular attention to whether differences in request parsing, PHP truthiness/type conversion, SQL construction, template escaping, or path normalization introduce authorization bypasses or data exposure.
- Distinguish original risks, newly introduced candidate risks, and suspicions lacking sufficient evidence. Do not claim exploit reproduction when only static review was performed.

## Evidence and Changes

1. Record source locations, data flows, affected inputs/permissions/state, and related features/contracts/scenarios.
2. Perform necessary reproduction only in authorized isolated local environments with synthetic data. A review request does not authorize attacks on external systems or production data changes.
3. Link actual original and candidate results, distinguishing reproduced behavior from behavior not reproduced and the reasons.
4. Prepare proposed fixes and security change records under the Evidence Protocol, attaching the relevant finding, implementation, and regression scenarios.
5. Verify safe behavior after fixes and regress normal paths. Apply the contract's comparison declarations when evaluating results.

## Deliverables

Include finding IDs, classification (original/new/unconfirmed), impact and severity rationale, source/execution evidence, proposals/actions, and residual risks in the Security Report. Attach security change records using the referenced format rather than defining another approval or verdict format here. Change IDs and file locations follow the [workspace convention](../migration-orchestrator/references/workspace-convention.md).

A finding of no new vulnerabilities applies only within the review scope. Security review PASS does not establish functional equivalence PASS, and functional equivalence PASS does not prove security.
