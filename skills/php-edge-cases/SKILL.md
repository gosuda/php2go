---
name: php-edge-cases
description: Use when analyzing, contracting, porting, or diffing PHP code for Go migration. Catalogs version-dependent PHP runtime semantics that differ from Go defaults, with source patterns, boundary scenarios, and executable probes.
---

# PHP Semantics and Go Divergence

PHP and Go disagree in many places where a direct translation looks correct. This Skill lists those places, how to find them in legacy source, which boundary inputs expose them, and how to reproduce PHP behavior in Go. It covers PHP 5.x, 7.x, and 8.x because legacy targets often run old runtimes and behavior changed across major versions.

Treat this catalog as a guide to what to check, not as evidence about a particular application. The target's PHP version, `php.ini`, SAPI, extensions, and web server determine actual behavior. Confirm relevant entries by running the bundled probe scripts against the original runtime, and record the observations as fixtures or analysis evidence.

## When to Use

- **Analysis:** Search the source for the patterns in each reference and record matches in the Feature Inventory as potential divergences.
- **Contracts:** Add the listed boundary inputs as scenarios for affected features, so that differences are observable rather than assumed.
- **Implementation:** Follow the Go guidance instead of the nearest standard-library default.
- **Diff interpretation:** When PHP and Go outputs differ, look up the observed symptom here before changing the contract or the candidate.

## Procedure

1. Identify the reference runtime: exact `PHP_VERSION`, integer size, SAPI, loaded extensions, and the effective `php.ini` (`php -i`, or `phpinfo()` in an isolated copy). Note any setting listed in [references/runtime-config.md](references/runtime-config.md) that differs from defaults.
2. Run the probes with the reference binary and configuration. They ship in `scripts/` when this Skill is packaged on its own; a copy of this catalog bundled into another Skill package contains only the documents. Use `scripts/run-probes.sh PHP_BIN OUT_DIR` for engine behavior and `scripts/run-request-probe.sh PHP_BIN [PORT]` for request parsing, headers, output, and sessions through the built-in server. Set `PHP_PROBE_ARGS="-c /path/to/php.ini"` to apply the deployed configuration. Keep the outputs with the analysis evidence. Probe outputs are about the runtime, not the application.
3. For each reference topic, search the source for the listed patterns. Record file and line, the affected feature, and whether the input can reach the divergent case.
4. Add a scenario for each reachable divergence. Prefer inputs that separate behaviors, such as `"abc"` against `0`, `a[]=1&a[]=2`, invalid UTF-8, `0.1+0.2`, and `2024-02-30`.
5. Implement the PHP semantics explicitly in Go helpers with narrow names, such as `phpIntval`, `phpLooseEquals`, and `phpJSONEncode`. Test those helpers against probe outputs from the reference version. Do not spread PHP compatibility logic across handlers.
6. When a divergence is an original bug or a security risk, keep the original behavior in the contract and propose the change through a security or change record. Do not fix it silently during translation.

## Reference Topics

| Topic | Reference | Probe |
| --- | --- | --- |
| Loose comparison, truthiness, numeric strings, integer overflow, null access | [references/types.md](references/types.md) | `types.php` |
| Ordered hash arrays, key normalization, sorting, references | [references/arrays.md](references/arrays.md) | `arrays.php` |
| Byte strings, multibyte functions, HTML escaping, EUC-KR/CP949 | [references/strings-encoding.md](references/strings-encoding.md) | `strings.php` |
| Float output, `json_encode`/`json_decode`, `serialize` | [references/numbers-json.md](references/numbers-json.md) | `numbers_json.php` |
| PCRE versus Go RE2 | [references/regex.md](references/regex.md) | `regex.php` |
| `strtotime`, `mktime`, overflow normalization, time zones | [references/time.md](references/time.md) | `time.php` |
| Superglobals, query parsing, uploads, input limits | [references/request.md](references/request.md) | `parse_str.php`, `request.php` |
| Headers, output buffering, errors in output, `exit`, sessions, cookies | [references/output-session.md](references/output-session.md) | `output_headers.php` |
| `php.ini`, legacy runtime features, database drivers, randomness, hashing | [references/runtime-config.md](references/runtime-config.md) | Partial |

Every entry names the PHP versions it applies to. Entries are labeled **Observed** when the bundled probes confirmed them on PHP 5.6.40, 7.4.33, and 8.4.27 (64-bit Linux, `-n` defaults, built-in server), or **Documented** when they come from PHP documentation or source behavior that the bundled probes do not exercise. Recheck Documented entries on the reference runtime before relying on them. Columns labeled `8.x` show results observed on 8.4.27; the minor version where a change first appeared is given in the text only when documented.

## Boundaries

- Do not build a general PHP runtime in Go. Reproduce only the semantics that reachable features observably depend on, and keep the rest out of scope with a recorded reason.
- Probes reflect specific builds. Minor versions, distribution patches, `php.ini`, extensions (for example `mbstring.func_overload`), and SAPI can change results. Rerun them on the reference runtime instead of trusting the labels in this Skill.
- Matching a catalog entry is an analysis lead, not a finding. Use executable comparisons to determine equivalence.
- Do not copy probe output from production systems that contains secrets or personal data. The probes print runtime configuration and request data that you supply.
