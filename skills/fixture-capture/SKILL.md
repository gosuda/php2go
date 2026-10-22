---
name: fixture-capture
description: Capture reproducible request sequences, responses, and database, session, and file effects by running the original PHP application from isolated initial state.
---

# Reference Fixture Capture

Take a Behavior Contract and an executable original application as inputs. If capture tools are unavailable or observation channels are unimplemented, disclose those limits rather than fabricating execution artifacts.

## Reproduction Prerequisites

1. Pin the source revision, contract revision/hash, PHP/extensions/database/web server versions, dependency lockfiles, environment profile, and capture tool version. Reference secrets without exposing their values.
2. Prepare sanitized test database, file tree, session store, and external service doubles as an initial snapshot and record its digest. Do not replay writes against production.
3. Verify that workers, jobs, mail, queues, and external APIs use recording doubles in the isolated environment. Define completion conditions and timeouts for asynchronous effects. Failed initialization or dependencies outside the doubles invalidate the affected capture.
4. Control clock/timezone/locale, RNG, sequence starting values, and ordering rules. For values that cannot be injected, retain raw values and observed relationships and link them to narrowly scoped contract comparison rules.
5. Distinguish DDL/schema hashes from data snapshots. Record database version, charset/collation, sql_mode, timezone, triggers/procedures, and storage engine. A sqlc schema file alone does not restore data or database runtime semantics.

## Capture

- Restore initial state for each scenario and verify its pre-state digest. Preserve the cookie jar and state within multistep scenarios and execute requests in their actual order. Capture redirects starting with intermediate responses.
- Record raw/binary requests, response status/headers/body, database before/after state and deltas, session/cookie changes, file digests/content/required metadata, and external effects within the contract scope.
- Capture failure responses too. Do not replace errors or timeouts with empty responses or no-effect claims. Distinguish partial observations from capture failures.
- Restore initial state and rerun the original to check stability. Locate and control nondeterminism, or account for it in the contract, before recapturing. Do not arbitrarily select one unstable result as a golden fixture.

## Deliverables

Include the fixture ID, contract/source/tool versions and hashes, snapshot and restoration procedure, runtime profile, scenario and step IDs, raw artifact paths/hashes, capture success per channel, and repeatability results in the Capture Manifest. Retain raw artifacts even when normalized results are also available.

Capture synthetic data instead of authentication tokens and personal information. Restrict access to sensitive comparison artifacts and use safe references in reports. Disclose limits when redaction removes information needed for reproduction or comparison.

Fixtures are evidence of the original application. Do not overwrite them to match Go output. Capture a new version when initial state, the contract, or the original source revision changes.
