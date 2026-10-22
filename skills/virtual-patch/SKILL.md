---
name: virtual-patch
description: Derive temporary Go reverse proxy defenses from legacy PHP analysis, verify scoped mitigations, and retire rules and PHP dependencies as affected features are replaced by verified Go implementations.
---

# Analysis-Driven Virtual Patching

Use this Skill to reduce specific, identified PHP risks while migration is in progress. Derive feature-specific defenses from legacy source analysis and observed behavior, place them in a Go reverse proxy, and replace protected PHP features incrementally with verified Go implementations. Retire the associated rules and PHP dependencies when the replacement no longer relies on them.

Do not build a general-purpose WAF or introduce broad attack signatures without feature evidence. Default traffic continues to the configured PHP HTTP upstream; only explicitly matched routes receive a defense or Go handling. This mode does not claim to fix every vulnerability or prove that a protected PHP feature is already migrated.

Read [references/patch-plan.md](references/patch-plan.md) for the patch record, protocol checks, and lifecycle, and the [Evidence Protocol](../equivalence-check/references/evidence-protocol.md) for verdicts and security change records. The Skill supplies instructions, not an existing proxy binary or deployable configuration.

## Procedure

1. Start from the PHP Feature Inventory, source locations/data flow, relevant security findings, and Behavior Contracts. Identify the original revision, public origin, PHP HTTP upstream, existing ingress/rewrite rules, TLS termination, and trusted proxy chain. An HTTP reverse proxy targets a web server serving PHP, not a PHP-FPM FastCGI socket.
2. For each identified risk, define its affected inputs, exploit preconditions, a narrowly scoped rule, expected safe behavior, normal requests that must remain accepted, and known bypasses/limits. Capture baseline behavior before adding the proxy. Link finding → feature/contract → rule → scenarios → permanent Go fix. Define exact host/method/path/query matching, precedence, unmatched behavior, ownership, review deadline, expiry action, and removal criteria. Label uncertain findings rather than presenting them as confirmed exploits.
3. Build a small Go entry point using `net/http/httputil.ReverseProxy` and `Rewrite` on a compatible Go version. Keep upstreams configured server-side. Place route decisions before dispatch and send each request to one authoritative implementation. Keep request/response transformation narrowly scoped.
4. Preserve the declared external origin, path/query representation, body, cookies, redirects, and authentication/session semantics. Define trusted forwarded-header handling, timeout/error behavior, streaming, and upgrade requirements. Check Go/PHP parsing differences rather than assuming transparent forwarding.
5. Implement the patch and an explicit enable/disable mechanism with validated configuration. Do not duplicate writes, automatically retry uncertain writes against PHP, or shadow traffic with side effects. Do not bypass a security block on error or expiry.
6. Execute the Patch Plan's verification matrix, including attack containment, legitimate boundary inputs, and direct-origin bypass checks. Attach comparison results and proposed security changes using the Evidence Protocol.
7. Prepare the implementation, tested configuration, operational checks, and rollback procedure for review. Do not change live ingress, DNS, certificates, or services merely because a Skill was invoked. Execute rollout only within the user's authorized deployment scope.
8. Migrate the protected feature to Go with the permanent fix and explicit session/state compatibility. Verify both ordinary behavior and the attack regression without the temporary rule in isolation. After authorized cutover and rollback review, remove the redundant rule and the feature's unused PHP route/code/dependencies together. Retain shared PHP infrastructure until every consumer is migrated, and keep regression evidence and change history.

## Incremental Lifecycle

```text
Analyze PHP and identify a feature-specific risk
  → Generate and verify a temporary proxy defense
  → Keep the protected PHP feature running during migration
  → Implement and verify the permanent Go replacement
  → Cut over the feature and retire its rule and unused PHP dependencies
```

Track each feature separately; other features can remain on protected PHP. Replacement alone is insufficient to remove a rule: prove that Go supplies the intended protection without it and that old PHP entry points cannot still be reached.

## Boundaries

- Prefer a request guard or explicit route replacement when it solves the problem. Response rewriting is appropriate only for a known bounded representation; it does not undo database writes or external effects already performed by PHP.
- State when a proxy rule cannot enforce a property, such as authorization depending on unavailable database or session state. Do not substitute a regex or header check for that property. Record residual risk and required source/Go fixes rather than claiming complete mitigation.
- A route handled by Go must preserve authentication and authorization, including PHP session compatibility where needed. Do not treat possession of a cookie as proof of identity. For database access, read the [database policy](../sql-extract/references/database-policy.md).
- Keep proxied PHP routes visible as unmigrated features. Proxy pass-through evidence is not evidence that those features have been implemented in Go.
- Report unsupported protocol cases and unverified paths. A proxy must not conceal an unimplemented feature by returning generic success.

## Deliverables

Deliver a Patch Plan, finding-to-rule-to-feature inventory, Go source and configuration instructions, attack and legitimate-request evidence, residual risks, enable/disable and rollback procedures, and a joint rule/PHP retirement plan linked to the permanent Go fix. Record unavailable checks as unexecuted. A working proxy is not a whole-application equivalence verdict.
