# Virtual Patch Plan

Use this record to derive temporary defenses from PHP analysis and retire them with the affected PHP implementation. It is a design checklist, not a general WAF specification, machine schema, or implemented configuration format.

This document owns proxy requirements and lifecycle. [Evidence Protocol](../../equivalence-check/references/evidence-protocol.md) owns verdicts and security change records; [database policy](../../sql-extract/references/database-policy.md) owns DB access requirements.

## Required Record

| Field | Required content |
| --- | --- |
| Identity | Patch ID, revision, owner, purpose, affected feature/contract/scenario IDs |
| Analysis evidence | PHP source/data flow, finding ID and confidence, attack preconditions, affected inputs, and proxy enforcement limits |
| Baseline | Original revision, fixture hashes, direct PHP behavior, initial state |
| Topology | Public origin, ingress/TLS termination, trusted hops, fixed PHP HTTP upstream and optional Go handlers |
| Match | Allowed hosts, methods, paths/query rules, precedence, encoding rules, unmatched behavior |
| Action | Request guard, request transformation, response transformation, or Go route replacement |
| Behavior delta | Expected response/state changes; approval evidence if already approved, otherwise proposal status |
| Session policy | Identity verification, session storage/format, cookie attributes, CSRF, login/logout behavior |
| Failure policy | Upstream errors, timeouts, invalid configuration, retry limits, and behavior after partial responses or uncertain writes |
| Lifecycle | Enable/disable mechanism, review deadline, expiry action, rollback target, permanent fix and removal criteria |
| Retirement dependency map | Rule → protected PHP feature → permanent Go fix → PHP routes/code/session/include/deployment consumers that must be retired or retained |
| Evidence | Build/test results, pass-through and patched diffs, effect observations, limitations, rollback rehearsal |

Default unmatched routing should preserve existing PHP service behavior through the proxy. Record intentional security restrictions using the Evidence Protocol's change record.

## HTTP and Trust Checks

- Fix upstream destinations in trusted configuration. Never accept arbitrary upstream URLs from request parameters or headers. Validate incoming hosts and avoid routing loops.
- Prefer `ReverseProxy.Rewrite`. `SetURL` changes upstream Host and may join a base path; `SetXForwarded` derives forwarding values from the immediate request. Specify the desired public Host/scheme and how verified ingress metadata is carried through TLS termination. Do not blindly copy client-supplied forwarding or identity headers. See [Go ReverseProxy documentation](https://pkg.go.dev/net/http/httputil#ReverseProxy).
- Define one route matching interpretation and check it against PHP/web server parsing. Cover duplicate query keys, percent encoding, encoded separators, semicolons, trailing slashes, and malformed input relevant to the patch. `Rewrite` can remove unparsable query parameters; do not assume byte-identical forwarding or copy `RawQuery` back without examining parsing disagreement. Preserve legacy forms only under an explicit reviewed policy.
- Preserve repeated `Set-Cookie` headers, cookie Domain/Path/Secure/HttpOnly/SameSite/expiry, redirect Location, and subsequent authenticated requests. Avoid exposing internal upstream addresses. Rewrite cookies or redirects only when the public-origin contract requires it.
- Preserve request bodies, multipart uploads, content types, status codes, and response representations within scope. Inspecting a body must not consume it before forwarding; apply bounded buffering only where required. Cover cancellation, slow uploads, large responses, SSE/streaming, WebSocket upgrades, and Range/HEAD/conditional requests when used. Report unhandled required modes rather than silently buffering or corrupting them.
- If modifying a response body, account for compression, charset, Content-Length, validators such as ETag, and cache policy. Bound buffering and limit transformations to explicit content types and routes. Do not apply blanket HTML/string substitutions. Client-visible redaction cannot prevent upstream side effects.
- Configure errors, timeouts, and transport behavior explicitly. An upstream error must not become HTTP 200. Never switch an already attempted write to the other backend after a timeout; its outcome may be unknown. Do not add application retries without proving replay safety for the actual feature, including GET routes that write state.
- Keep logs and metrics useful without recording session tokens, secrets, or full sensitive bodies. Track patch decisions, upstream/handler identity, latency, errors, and configuration revision.

## Routing, Writes, and Sessions

Route each request once. Canary selection should keep a coherent user/session workflow where cross-backend state matters. If PHP and Go cannot read compatible session state or preserve authorization, leave the affected feature on PHP until that boundary is implemented and verified.

Do not mirror writes to PHP and Go against shared live state. A nominal read may update counters, sessions, or queues, so method alone does not prove safe shadowing. Use isolated captures and independent snapshots for differential execution.

Apply the database policy when introducing a Go write path. Account for in-flight requests and state compatibility when switching routes back.

## Verification Matrix

1. **Direct PHP versus proxy with patch disabled:** Compare declared HTTP and state behavior from independent equivalent snapshots. List required transport/origin adaptations explicitly rather than treating them as arbitrary ignored fields.
2. **Baseline versus active patch:** Exercise exact matches, near misses, unauthorized users, malformed inputs, body boundaries, and upstream failures. Link expected and unexpected differences to the Evidence Protocol's result and change records.
3. **Go route replacement:** Compare the actual Go implementation against direct PHP using the feature contract. Pass-through results do not count as Go implementation coverage.
4. **Operational transitions:** Verify enable/disable behavior, invalid config rejection, expiry behavior, rollback and in-flight handling. Do not remove a security mitigation as an automatic fallback.
5. **Defense and retirement:** Reproduce the specific finding only in authorized isolated fixtures. Check legitimate near-boundary inputs for false positives, relevant encoding/alternate-route bypasses, and origin reachability. Verify the Go replacement blocks the attack with the temporary rule disabled, then verify old PHP routes cannot be reached after retirement.

Apply the Evidence Protocol to these observations, including missing channels or scenarios.

## Expiry and Removal

Set a review deadline and an explicit expiry action. A security mitigation should alert and retain protection or use a reviewed safe response until the permanent fix is ready; time passing must not silently reopen the original vulnerability. For other patches, specify whether expiry stops rollout, alerts, or reverts to a known compatible route. Do not infer an expiry action from a timestamp alone.

Rehearse rollback in isolation, retaining the patch configuration and evidence. Rolling back routing does not reverse writes already committed, and a security rollback target must preserve necessary protection. If rollback still targets PHP, retain the required rule and PHP artifacts until that dependency is closed.

After the permanent Go fix is verified and deployed within authorized scope, retire the rule and the now-unused PHP implementation as one tracked feature transition. Check alternative routes, direct origin access, cron/CLI entry points, includes, shared session/authentication code, and deployment dependencies before deleting anything. Keep shared infrastructure for remaining PHP consumers and document why it remains. Remove the final PHP runtime/upstream only after no feature or rollback path requires it. Retain regression scenarios and change history.
