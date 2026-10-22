---
name: fixture-capture
description: Capture reproducible request sequences, responses, and database, session, and file effects by running the original PHP application from isolated initial state.
---

# Reference Fixture Capture

Take a Behavior Contract and an executable original application as inputs. Read the [Evidence Protocol](../equivalence-check/references/evidence-protocol.md) for execution validity and evidence retention and the [Behavior Contract](../behavior-extract/references/behavior-contract.md) for baseline and normalization rules.

## Reproduction Prerequisites

1. Pin the source revision, contract revision/hash, PHP/extensions/database/web server versions, dependency lockfiles, environment profile, and capture tool version. Reference secrets without exposing their values.
2. Prepare sanitized test database, file tree, session store, and external service doubles as an initial snapshot and record its digest. Do not replay writes against production.
3. Verify that workers, jobs, mail, queues, and external APIs use recording doubles in the isolated environment. Define completion conditions and timeouts for asynchronous effects. Failed initialization or dependencies outside the doubles invalidate the affected capture.
4. Control clock/timezone/locale, RNG, sequence starting values, and ordering rules. For values that cannot be injected, retain raw values and observed relationships and link them to narrowly scoped contract comparison rules.
5. Capture the schema/data artifacts and runtime profile specified by the [database policy](../sql-extract/references/database-policy.md), including dynamic registry state needed by the scenario. Record digests and restoration inputs in the manifest.

## Capture

- Restore initial state for each scenario and verify its pre-state digest. Preserve the cookie jar and state within multistep scenarios and execute requests in their actual order. Capture redirects starting with intermediate responses.
- Record raw/binary requests, response status/headers/body, database before/after state and deltas, session/cookie changes, file digests/content/required metadata, and external effects within the contract scope.
- Capture failure responses too. Do not replace errors or timeouts with empty responses or no-effect claims. Distinguish partial observations from capture failures.
- Restore initial state and rerun the original to check stability. Locate and control nondeterminism, or account for it in the contract, before recapturing. Do not arbitrarily select one unstable result as a golden fixture.

## Deliverables

Include the fixture ID, contract/source/tool versions and hashes, snapshot and restoration procedure, runtime profile, scenario and step IDs, raw artifact paths/hashes, capture success per channel, and repeatability results in the Capture Manifest. Retain raw artifacts even when normalized results are also available.

Capture synthetic data instead of authentication tokens and personal information. Restrict access to sensitive comparison artifacts and use safe references in reports. Disclose limits when redaction removes information needed for reproduction or comparison.

Apply the Behavior Contract's baseline management rules when recapturing or revising fixtures.
