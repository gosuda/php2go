# Regular Expressions

PHP `preg_*` functions use PCRE (PCRE 8.x in 5.6 and 7.2-, PCRE2 in 7.3+). Go's `regexp` uses RE2, which guarantees linear time and does not support some PCRE features. Legacy `ereg*` functions (POSIX, removed in 7.0) use yet another syntax.

## Unsupported in Go RE2

**Observed:** Go rejects backreferences (`(a)\1`), lookahead (`a(?=b)`), and lookbehind (`(?<=a)b`) at compile time. Possessive quantifiers (`a++`), atomic groups, recursion, conditionals, `\K`, and callouts are also unsupported in RE2.

**Go:** Rewrite these patterns with explicit code (for example, match then compare groups in Go), or use a PCRE-compatible library only after reviewing its safety and maintenance status. Test rewritten patterns against inputs captured from the reference.

## Semantics That Differ Without Errors

**Observed (all versions):**

- `$` matches before a final newline in PCRE unless the `D` modifier is set: `preg_match('/^abc$/', "abc\n") === 1`. Go's `$` matches only at the end of the text without the `m` flag. Validation code like `/^\d+$/` therefore accepts a trailing newline in PHP and rejects it in Go.
- `.` does not match newline without `s` in both engines.
- Without `u`, PCRE works on bytes: `\d` and `\w` are ASCII-only and `\w` does not match Korean. With `u`, `\d` matches Unicode digits such as Arabic-Indic digits, and `\w` matches Korean. Go's `\d` and `\w` are always ASCII-only, and Go always works on UTF-8.
- With `u`, invalid UTF-8 in the subject makes `preg_match` return `false` and `preg_replace` return `null` (`preg_last_error() === 4`). A `null` echoed becomes an empty string.
- Exceeding `pcre.backtrack_limit` makes `preg_match` return `false` (`preg_last_error() === 2`). `/(a+)+$/` against 30 `a` characters followed by `b` failed this way. Go would return no match without an error. Treat `false` as a third outcome in the contract.
- Alternation is leftmost-first in both engines (`a|ab` matches `a`). Go's `regexp/syntax` `Longest()` changes this.
- In `preg_match`, an unmatched group in the middle produces `""`, and unmatched trailing groups are omitted from `$matches`. `PREG_UNMATCHED_AS_NULL` (7.2+) changes this.
- `preg_quote` escapes `#` from 7.3 onward.
- The `e` modifier evaluates the replacement as PHP code in 5.6 (E_DEPRECATED) and fails in 7.0+. Treat any `/e` usage as a code execution risk.

**Find:** `preg_match(`, `preg_match_all(`, `preg_replace(`, `preg_replace_callback(`, `preg_split(`, `preg_grep(`, patterns with `$` and no `D`, patterns with the `u`, `x`, `s`, `m`, `i`, `U`, or `e` modifiers, `ereg(`, `eregi(`, `split(`.

**Go:** Translate each pattern deliberately: add `\z` or `(?:\n)?\z` to reproduce `$`, decide byte versus UTF-8 handling, and report PCRE failure cases as explicit errors when the PHP code checks for `false`.
