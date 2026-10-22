# Behavior Contract Draft v0

This is a draft format for human review. No machine-validation schema or execution tool is provided yet. Assertion names in the YAML example are identifiers for future adapters to implement, not currently executable commands.

## Required Information

| Field | Contents |
| --- | --- |
| `contract_version`, `id`, `revision` | Format version, stable contract ID, and change revision |
| `feature`, `reference` | Feature ID, original source revision, and evidence locations |
| `initial_state` | Immutable snapshot identifier/hash, runtime/configuration, and clock/RNG policy |
| `scenarios` | Unique IDs, ordered input steps, authentication, and preconditions |
| `observable` | Capture scope and comparison method per channel, including explicit no-effect claims |
| `invariants` | Requirements, applicable scenarios, assertions, and required observations |
| `normalization` | Scoped transformations, rationale, target fields, and preserved relationships |
| `coverage_gaps` | Missing scenarios, unconfirmed behavior, and unsupported observation scopes |

The following values are illustrative. If the actual feature updates view counts or sessions, replace the no-effect claims accordingly. Initial state must include data that distinguishes ordering on page 2 and contains private posts.

```yaml
contract_version: 0
id: board.read
revision: 1
feature: board.read
reference:
  source_revision: example-php-revision
  evidence: [bbs/board.php]
initial_state:
  snapshot: board-public-page2-v1
  manifest: fixtures/board-public-page2-v1/manifest.json
  runtime_profile: php-reference-v1
  clock: fixed-in-fixture
  randomness: seeded-in-fixture
scenarios:
  - id: public-page-2
    auth: anonymous
    steps:
      - id: read-page
        request:
          method: GET
          path: /bbs/board.php
          query: [[bo_table, notice], [page, "2"]]
        observable:
          http:
            status: {expected: 200, compare: exact}
            headers:
              compare: multimap
              content_type: text/html
            body: {compare: exact-bytes}
          database: {compare: scoped-state-delta, expected: none}
          sessions: {compare: logical-state-delta, expected: none}
          cookies: {compare: attributes-and-value-relations, expected: none}
          files: {compare: scoped-tree-delta, expected: none}
          external_effects: {compare: recorded-events, expected: none}
invariants:
  - id: original-order
    scenario: public-page-2
    assertion: board.original-order
    description: Preserve original sorting rules and tie handling.
  - id: pagination
    scenario: public-page-2
    assertion: board.page-links
    description: Preserve next/previous page links and query parameters.
  - id: hidden-posts
    scenario: public-page-2
    assertion: board.hidden-posts-absent
    description: Do not expose private post content or identifying information.
normalization: []
coverage_gaps:
  - Other pages, authenticated users, and invalid page values have no scenarios yet.
```

## Comparison Semantics

- Preserve duplicate query parameters and headers and any required order. Do not lose repeated inputs by converting them into JSON objects. Retain original multipart/binary data or reproducible byte references.
- Distinguish header name case rules from value semantics. Do not merge repeated `Set-Cookie` headers. Check cookie Path/Domain/SameSite/Secure/HttpOnly/expiry and subsequent request behavior.
- Specify observed database tables, keys, schemas, types, NULL handling, and ordering rules in the manifest. Capturing only some tables does not establish that the entire database was unaffected. Add event observations for important intermediate writes or rollbacks invisible in final state.
- Define ID creation points and one-to-one mappings and verify relationships across responses, database references, URLs, sessions, and filenames. Do not delete ID fields.
- Apply only explicitly permitted HTML normalization. Transformations that change text/pre/script content, escaping, links, or form values may alter meaning. Preserve original content too.
- Record omitted comparison fields with rationale and coverage gaps. No effect (`none`) and inability to observe are different states.

Link each assertion to its actual implementation version and execution result. Report unimplemented assertions as `UNSUPPORTED` and unexecuted assertions as `NOT COVERED`.
