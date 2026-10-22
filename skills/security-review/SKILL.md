---
name: security-review
description: Review unsafe original behavior and vulnerabilities introduced during PHP-to-Go migration, tracking approved security changes separately from functional equivalence results.
---

# Migration Security Review

Avoid treating preservation of original vulnerabilities as an automatic migration goal, while ensuring security fixes do not conceal equivalence differences. Security findings and functional equivalence verdicts remain separate.

## Review Scope

- Examine authentication/authorization, session fixation, cookie attributes, CSRF, SQL injection, escaping/XSS, file uploads and path handling, command execution, deserialization, outbound requests, and secret exposure in the original and candidate, tied to actual features.
- Pay particular attention to whether differences in request parsing, PHP truthiness/type conversion, SQL construction, template escaping, or path normalization introduce authorization bypasses or data exposure.
- Distinguish original risks, newly introduced candidate risks, and suspicions lacking sufficient evidence. Do not claim exploit reproduction when only static review was performed.

## Evidence and Changes

1. Record source locations, data flows, affected inputs/permissions/state, and related features/contracts/scenarios.
2. Perform necessary reproduction only in authorized isolated local environments with synthetic data. A review request does not authorize attacks on external systems or production data changes.
3. Link actual original and candidate results, distinguishing reproduced behavior from behavior not reproduced and the reasons. Preserve mismatches in equivalence results when differences exist.
4. Propose intentional changes to original risks with a change ID, rationale, impact scope, compatibility changes, and regression scenarios. Proceed within existing approval with its evidence linked; do not fabricate approval.
5. Verify safe behavior after fixes and regress normal paths. Do not implement security exceptions through broad normalization or comparison exclusions.

## Deliverables

Include finding IDs, classification (original/new/unconfirmed), impact and severity rationale, source/execution evidence, proposals/actions, and residual risks in the Security Report. Link approved changes to their approver, evidence, timestamp, scope, regression evidence, and relevant mismatches.

A finding of no new vulnerabilities applies only within the review scope. Security review PASS does not establish functional equivalence PASS, and functional equivalence PASS does not prove security.
