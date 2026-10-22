# Float Output, JSON, and Serialization

## Float to String

PHP converts floats to strings with the `precision` setting (default 14 significant digits) in `echo`, string concatenation, `(string)`, `strval`, and `sprintf('%s')`. `var_export`, `json_encode`, and `serialize` use `serialize_precision`, which defaults to 17 on 5.6 and to `-1` (shortest round-trip) on 7.1+.

**Observed:**

| Expression | 5.6 | 7.4 | 8.x |
| --- | --- | --- | --- |
| `(string)(0.1 + 0.2)` | `0.3` | `0.3` | `0.3` |
| `(string)(7 / 3)` | `2.3333333333333` | same | same |
| `(string)1e15`, `(string)1e14` | `1.0E+15`, `1.0E+14` | same | same |
| `(string)0.00001` | `1.0E-5` | same | same |
| `(string)1.0` | `1` | `1` | `1` |
| `(string)-0.0` | `0` | `-0` | `-0` |
| `json_encode(0.1 + 0.2)` | `0.3` | `0.30000000000000004` | `0.30000000000000004` |
| `json_encode(10.0 / 3)` | `3.3333333333333` | `3.3333333333333335` | same |
| `json_encode(1.0)` | `1` | `1` | `1` |
| `var_export(0.1)` | `0.10000000000000001` | `0.1` | `0.1` |

- Go's `fmt.Println(a + b)` with runtime float values prints `0.30000000000000004`, and `strconv.FormatFloat(f, 'f', -1, 64)` never uses exponent notation. Neither matches PHP `echo` output.
- PHP uses exponent notation at 1e15 and above, and for small values, with the `1.0E+15` mantissa format.
- 5.6 `json_encode` uses `precision` (14) unless `serialize_precision` is changed, so 5.6 JSON output differs from 7.1+ for most non-trivial floats.
- `setlocale(LC_NUMERIC, ...)` before 8.0 can make `(string)1.5` output `1,5`. 8.0+ is locale-independent.

**Find:** `echo` or concatenation with computed floats (averages, ratings, percentages, money), `round(` results that are printed, `ini_set('precision'`, and `ini_set('serialize_precision'`.

**Go:** Write `phpFloatToString(f float64, precision int)` that formats with `%.*G` semantics and PHP's `E+` formatting, then test it against probe output from the reference version and `php.ini`. Use a separate function for `serialize_precision`.

## `json_encode`

**Observed (all versions unless noted):**

- Default output escapes `/` as `\/` and non-ASCII as `\uXXXX`, and keeps `<`, `>`, `&`, and `'` literal: `{"u":"\/\ud55c<>&'\""}`. Go's `json.Marshal` keeps `/` and UTF-8 literal and escapes `<`, `>`, and `&` as `\u003c`, `\u003e`, and `\u0026`.
- `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` gives `"/한<>"`. Go's `json.Encoder` with `SetEscapeHTML(false)` gives the same bytes but appends a newline.
- Arrays with keys `0..n-1` in order become JSON lists. Other arrays, including lists after `unset` or `array_filter`, become objects: `{"1":2,"2":3}`. Empty arrays become `[]`, and `new stdClass` becomes `{}`. Go encodes nil slices as `null`, and maps with sorted keys.
- Invalid UTF-8 makes `json_encode` return `false` (`json_last_error() === 5`). Go replaces invalid bytes with U+FFFD and succeeds. `JSON_INVALID_UTF8_SUBSTITUTE` and `JSON_PARTIAL_OUTPUT_ON_ERROR` change PHP behavior. `JSON_THROW_ON_ERROR` (7.3+) throws.
- `NAN` and `INF` make `json_encode` return `false`. Go returns an error.
- `JSON_NUMERIC_CHECK` converts `"0x1A"` to `26` on 5.6 only.
- `JSON_PRETTY_PRINT` indents with four spaces and uses `"a": []`. Go's `MarshalIndent` uses the indentation you pass.

**Go:** Implement `phpJSONEncode(v, flags)` over an ordered value model, or post-process `json.Encoder` output only where escaping rules are known to be the sole difference. Treat a `false` result (often echoed as an empty body) as a real observable behavior.

## `json_decode`

**Observed:**

- Objects decode to `stdClass` unless `$assoc` is true. With `$assoc`, `{"a":1,"1":2}` becomes an array with integer key `1`.
- Large integers become floats unless `JSON_BIGINT_AS_STRING` is set. Go's `any` decoding produces `float64` for all numbers unless `UseNumber()` is set.
- Duplicate keys: the last value wins in both PHP and Go.
- An empty key becomes the property `_empty_` on 5.6 objects and `""` on 7.1+.
- `json_decode('')`, `'[1,]'`, and `"{'a':1}"` return `null`, the same value as valid `null` JSON. Check `json_last_error()` usage in the PHP code before mapping errors.

## `serialize` and `unserialize`

**Observed:** `serialize` output depends on `serialize_precision`: `d:0.10000000000000001;` on 5.6 and `d:0.1;` on 7.1+. `unserialize('garbage')` returns `false` with E_NOTICE on 5.6 and 7.4 and E_WARNING on 8.4. `unserialize('b:0;')` also returns `false`.

- Serialized data stored in the database, cache, or cookies must be decoded by a Go port of the format. Session data uses a related format; see [output-session.md](output-session.md).
- `unserialize` of user-controlled data is an object injection risk. Record it as a security finding, and do not reproduce object instantiation in Go.
