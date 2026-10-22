# Virtual Patch Plan

This document owns proxy capability planning, production readiness, and patch lifecycle for the target project. The Agent reuses its existing proxy components or generates the needed Go source/configuration and checks; a common gateway product is not required. [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md) owns tool selection, reuse, verdicts, and security change records; [database policy](../../sql-extract/references/database-policy.md) applies if a replacement handler accesses a database.

## Coverage Goal and Implementation Order

Preserve every feature required by the target application, either through compatible PHP forwarding during migration or through a verified Go replacement. Do not exclude a feature merely because it uses streaming, protocol upgrades, session integration, or another capability beyond the first implementation.

A useful first increment is ordinary HTTP forwarding, explicit path blocking, and route-by-route Go dispatch. This is an implementation order, not a permanent support boundary. If the selected feature requires another capability, implement or integrate that capability and verify it as part of the feature work. Reuse existing Go libraries and ingress facilities instead of rebuilding a general gateway platform.

| Action | Behavior |
| --- | --- |
| Forward | Preserve the required request/response or protocol behavior through a configured PHP upstream |
| Block or guard | Apply an evidence-backed feature-specific mitigation before the vulnerable operation |
| Route to Go | Dispatch selected traffic to a Go implementation and verify its feature contract |
| Transform when needed | Apply a bounded request/response adaptation justified by the feature contract or recorded security change |

Use deterministic route precedence and documented matching semantics. Unmatched requests retain compatible PHP handling. Test encoded forms and alternate entry points relevant to the feature. A path block may intentionally disable legitimate use; record that impact. If a narrower guard preserves legitimate use, it may be implemented and verified rather than rejecting the whole path for convenience.

Use an HTTP server serving PHP as the HTTP upstream, not a PHP-FPM socket. An existing ingress can continue serving specialized paths when that preserves the feature and the deployment boundary is explicit. Forwarded PHP functionality is preserved functionality, not completed Go implementation coverage.

## Capability Planning

Assess capabilities from application analysis and contracts, not from a blanket unsupported list.

| Required capability | Implementation and verification work when needed |
| --- | --- |
| WebSocket, protocol upgrades, or tunnels used by the application | Integrate appropriate forwarding; verify handshake, authorization, bidirectional transfer, cancellation, and closure |
| SSE or other request/response streams | Verify streaming, flushing, cancellation, backpressure, and suitable timeouts |
| Request/response transformation | Scope parsing and buffering; verify encoding/compression, content length, validators, and side effects |
| Cookies, redirects, or public-origin adaptation | Preserve or explicitly adapt public-origin behavior and verify repeated cookies and subsequent requests |
| PHP/Go session and identity compatibility | Reuse compatible storage/adapters or implement the required bridge; verify login/logout, authorization, and state transitions |
| Sticky/canary routing or coordinated switching | Add only if the target transition needs it; verify coherent user/state ownership and rollback |
| Forwarded headers, TLS, and ingress integration | Reuse the deployment's trust and termination facilities, adding only missing integration and verifying the resulting origin/identity behavior |

These are work categories, not a requirement to build every subsystem before any feature can ship. Library support may satisfy a requirement after integration is verified; do not presume it is absent or declare it supported without evidence.

Record capability status from actual implementation and tests. `UNSUPPORTED` means a current execution/observation/tooling limitation with a concrete follow-up, not a decision to permanently drop the application feature. Use `NOT COVERED` for missing valid execution evidence under the Evidence Protocol.

A capability gap should drive the next implementation or integration task. Continue that work within the authorized feature scope rather than stopping because it exceeds the first increment. If an external prerequisite prevents progress, retain working PHP handling and report the precise blocker and plan.

## Production Readiness

Do not cut over a route until every capability it requires is implemented or delegated through a verified compatible path, and the feature's required checks have passed. This gate restricts unverified deployment, not implementation or future feature support. If the entire deployment must pass through the proxy, assess all routes affected by that switch.

An existing PHP/ingress path may retain service while a capability is being completed. Document that path and whether the virtual patch protects it; do not silently bypass a mitigation or claim excluded traffic is protected. Reject requests only when no safe supported route exists or an intentional security rule calls for rejection, with the availability impact recorded. Do not install blanket WebSocket/SSE rejection merely because the first implementation focused on ordinary HTTP.

## Required Record

| Field | Content |
| --- | --- |
| Identity and evidence | Patch/feature/finding IDs, source revision, source/data-flow evidence, contract and reusable fixtures |
| Action and match | Selected actions, route precedence, matching semantics, unmatched behavior, and expected delta |
| Capability plan | Required protocols/state/origin behavior, existing support, integration work, implementation tasks, and evidence |
| Production readiness | Per-route verified handling or delegation, missing checks, deployment blockers, and next work |
| Lifecycle | Owner, enable/disable setting, review deadline, expiry action, safe rollback target, permanent fix and retirement dependencies |

Use the Evidence Protocol for security change approval and verdict fields rather than defining another format here.

## Compatibility and Failure Rules

- Keep upstream destinations in trusted configuration. Do not select arbitrary destinations from user input or duplicate live side effects. Never retry an uncertain write against another backend.
- Preserve public Host/scheme and verified forwarding/identity information across the actual ingress topology. Check `Rewrite`, `SetURL`, query handling, and upgrade behavior against the feature contract; see [Go ReverseProxy documentation](https://pkg.go.dev/net/http/httputil#ReverseProxy).
- Preserve bodies, status, headers, repeated Set-Cookie, and redirects unless a declared feature adaptation requires a change. Response rewriting cannot undo upstream writes or external effects.
- Match resource limits and timeout/cancellation behavior to the request or streaming contract. Upstream failures must not become generic success or automatic unprotected fallback. Invalid configuration or expiry must not disable a security defense.
- Record patch decisions, configuration revisions, and errors without exposing secrets. The Go handler or verified identity adapter owns authentication/authorization; cookie presence alone does not establish identity.

## Verification Matrix

Run stages relevant to the chosen feature and lifecycle point, reusing valid evidence.

1. **Forward:** Compare direct PHP with proxy handling for required ordinary and specialized protocol scenarios and declared effects.
2. **Mitigation:** Verify the identified risk is contained before the vulnerable effect; assess alternate-path bypasses and legitimate traffic. Record intended security/availability changes.
3. **Go route:** Compare the Go implementation with PHP under the feature contract, including required protocols and identity/state boundaries.
4. **Capabilities and transitions:** Verify each capability task or delegated path, upstream errors, configuration changes, and the rollback strategy actually used. Keep unverified cutovers distinct from implementation work in progress.
5. **Permanent replacement:** Verify the original attack is contained without the temporary rule, then check old PHP entry points cannot bypass Go.

## Review, Rollback, and Removal

Start with versioned route configuration and a manual rollback strategy when sufficient. Add coordination only when the feature's actual state or traffic requirements demand it; do not forbid that work. Rollback selects a compatible path with necessary protection retained and does not undo committed writes.

Declare the action at the review deadline, such as alerting while retaining protection. If rollback still depends on PHP, keep the required rule and artifacts until that dependency closes.

After the permanent Go fix is verified and authorized cutover is complete, retire the redundant rule and unused PHP feature together. Check remaining route, cron/CLI, include, session, shared code, and rollback consumers before removal. Keep shared PHP dependencies until no consumers remain. Retirement remains a later feature milestone, not a prerequisite to deliver an interim mitigation.
