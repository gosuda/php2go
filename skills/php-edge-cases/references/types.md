# Types, Comparison, and Numeric Strings

PHP converts between types implicitly in comparisons, arithmetic, array keys, and function arguments. Go requires explicit conversion and rejects input that PHP accepts. Most migration bugs in validation, authorization checks, and pagination come from this gap.

## Loose Comparison (`==`, `switch`, `in_array`, `array_search`)

**Observed** (5.6.40 / 7.4.33 / 8.4.27):

| Expression | 5.6 | 7.4 | 8.x |
| --- | --- | --- | --- |
| `"abc" == 0` | true | true | false |
| `switch ("abc") { case 0: }` | matches `case 0` | matches `case 0` | falls through to `default` |
| `in_array("abc", [0])` | true | true | false |
| `"abc" < 1` | true | true | false |
| `"0x1A" == 26` | true | false | false |
| `"1" == "01"`, `"10" == "1e1"`, `100 == "1e2"` | true | true | true |
| `" 1" == 1`, `"1 " == 1` | true | true | true |
| `null == 0`, `null == false`, `"" == null`, `[] == false`, `"0" == false` | true | true | true |
| `"9223372036854775807" == "9223372036854775808"` | false | false | false |

- PHP 8 compares a number with a non-numeric string as strings. Earlier versions convert the string to a number, so any non-numeric string equals `0`.
- Two numeric strings compare numerically in every version. `"1e3" == "1000"` is true. Hash comparisons such as `md5($a) == md5($b)` are true when both hashes look like `0e...` digits.
- `in_array` and `array_search` are loose unless the third argument is `true`. `switch` always uses `==`.

**Find:** `==`, `!=`, `<>`, `switch`, `in_array(` and `array_search(` without `, true`, `array_keys($a, $v)`, comparisons against `0`, `''`, `null`, or `false`, and `md5(`/`sha1(` compared with `==`.

**Scenarios:** non-numeric strings where integers are expected (`id=abc`), leading and trailing spaces, leading zeros, exponent notation (`1e1`), hex-looking strings, empty string, missing parameter, and `0e` hash collisions.

**Go:** Implement a `phpLooseEquals(a, b)` that follows the reference version's comparison table only where needed. Translate `===` to typed comparison. Never rely on `strconv` errors to imitate loose comparison, because PHP treats many invalid strings as `0` instead of failing.

## Truthiness and `empty()`

**Observed (all versions):** `"0"`, `""`, `0`, `0.0`, `null`, `[]`, and `false` are falsy. `"0.0"`, `" "`, `"false"`, and `"00"` are truthy. `empty($x)` equals `!isset($x) || !$x`.

**Find:** `if ($x)`, `!$x`, `empty(`, `?:`, `||` defaults such as `$x = $_GET['p'] ?: 1`.

**Go:** Write `phpTruthy(v)` with the exact falsy set. A string-only helper must treat `"0"` as false.

## String to Number Conversion

**Observed:**

| Expression | 5.6 | 7.4 | 8.x |
| --- | --- | --- | --- |
| `(int)"12abc"` | 12 | 12 | 12 |
| `(int)" 12"`, `(int)"12 "` | 12 | 12 | 12 |
| `(int)"1e3"` | 1 | 1000 | 1000 |
| `(int)"0x1A"`, `(int)"012"` | 0, 12 | 0, 12 | 0, 12 |
| `intval("012", 0)` | 10 | 10 | 10 |
| `(int)"9999999999999999999"` | `PHP_INT_MAX` | `PHP_INT_MAX` | `PHP_INT_MAX` |
| `"12abc" + 1` | 13 | 13 + E_NOTICE | 13 + E_WARNING |
| `"abc" + 1`, `"" + 1` | 1 | 1 + E_WARNING | `TypeError` |
| `is_numeric("1 ")` | false | false | true |
| `is_numeric("0x1A")` | true | false | false |
| `is_numeric(" 1")`, `is_numeric(".5")`, `is_numeric("1e3")` | true | true | true |
| `ctype_digit("")` | false | false | false |

- `(int)` and `intval()` read a leading numeric prefix and ignore the rest. Go's `strconv.Atoi("12abc")` and `strconv.Atoi(" 12")` both return errors.
- Integer strings beyond the platform range saturate at `PHP_INT_MAX` when cast. Floats beyond range cast to platform-dependent values: all three probed versions on 64-bit Linux gave `(int)1e19 === -8446744073709551616` (modular), and `(int)NAN === 0`. Go float-to-int conversion of out-of-range values is implementation-defined. On arm64, `int64(1e19)` produced `9223372036854775807`.
- PHP 8 raises `TypeError` for arithmetic on non-numeric strings. Earlier versions silently use `0`. A request with `page=abc` can therefore produce a page of results on PHP 5/7 and a fatal error on PHP 8.

**Find:** `(int)`, `intval(`, `+ 0`, `* 1`, arithmetic on request values, `is_numeric(`, `ctype_digit(`, `settype(`, `abs(`, and `floor(` on request values.

**Go:** Implement `phpIntval(s string) int64` that parses optional leading whitespace (`" \t\n\r\v\f"`), an optional sign, digits, and for 7.1+ an optional fraction and exponent, then truncates and saturates as the reference version does. Keep `strconv` for inputs whose format is already validated.

## Integer and Float Arithmetic

**Observed:**

- `PHP_INT_MAX + 1` becomes a float (`9.2233720368547758E+18`) in every version. Go integers wrap to `math.MinInt64`.
- `7 / 2` returns `float(3.5)`. `6 / 2` returns `int(3)`. Go integer division truncates.
- `-7 % 2 === -1`, as in Go. `%` converts operands to integers first. `fmod` handles floats.
- `1 / 0`: 5.6 returns `false` with E_WARNING, 7.4 returns `INF` with E_WARNING, and 8.x throws `DivisionByZeroError`. `1 % 0` returns `false` in 5.6 and throws `DivisionByZeroError` in 7.0+.
- `1 << 64` is `1` in 5.6 (the shift count wraps) and `0` in 7.0+. `1 << -1` returns `PHP_INT_MIN` in 5.6 and throws `ArithmeticError` in 7.0+.
- `round()` rounds half away from zero (`round(2.5) === 3.0`, `round(-2.5) === -3.0`) and pre-rounds, so `round(1.955, 2) === 1.96`. Go's `math.Round` also rounds half away from zero, but `math.Round(1.955*100)/100` yields `1.95` because of binary representation.

**Find:** division used as an integer (page counts, offsets), `round(`, `ceil(`, `floor(`, money arithmetic, and counters near the integer limit.

**Go:** Return float results where PHP returns floats, and convert explicitly where the PHP code later casts. Reproduce `round()` precision semantics with decimal arithmetic or a ported algorithm when outputs depend on it.

## String Increment

**Observed:** `"a9"++` gives `"b0"`, `"Az"++` gives `"Ba"`, and `"zz"++` gives `"aaa"`. `""++` gives `"1"` (8.4 adds E_DEPRECATED). `null++` gives `1`, but `null--` stays `null` (8.4 adds E_WARNING).

**Find:** `++` or `--` on values that may be strings or null, such as generated codes or counters read from the database.

## Undefined Values and Null Access

**Observed:**

- Reading an undefined variable or array key returns `null` with E_NOTICE in 5.6 and 7.4, and E_WARNING in 8.x. Execution continues.
- Indexing `null` or an integer (`$n['x']`) returns `null` silently in 5.6, with E_NOTICE in 7.4, and with E_WARNING in 8.x.
- Reading a string offset past the end returns `""` with a diagnostic.
- `strlen(null)` returns `0` (8.1+ adds E_DEPRECATED).
- `isset($a['k'])` is false when the value is `null`. `array_key_exists('k', $a)` is true.
- `??` (7.0+) treats `null` values like missing keys.

When `display_errors` is enabled, these diagnostics appear in the response body. See [output-session.md](output-session.md).

**Go:** Distinguish missing, `null`, and empty string in the input model, for example with `(value string, present bool)` or pointers. Do not let Go zero values stand in for PHP `null` without checking how the PHP code consumes them.
