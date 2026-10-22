# Strings and Encodings

PHP strings are byte arrays. Unless code uses `mb_*` or `iconv`, every string function works on bytes. Go strings are also bytes, but `range`, `strings.ToUpper`, `unicode`, and `html/template` assume UTF-8 and runes.

## Byte Functions

**Observed (all versions):**

- `strlen("한글") === 6`. `substr("한글", 0, 4)` returns 4 bytes and cuts a character. `strrev`, `str_pad`, and `wordwrap` count bytes.
- `strtoupper("é")` and `ucfirst("élan")` leave non-ASCII bytes unchanged. Go's `strings.ToUpper("é")` returns `"É"`. PHP 8.2+ made these functions locale-insensitive. Earlier versions follow `setlocale(LC_CTYPE)`.
- `trim()` strips `" \t\n\r\0\x0B"` only. It does not strip U+00A0 or other Unicode spaces. Go's `strings.TrimSpace` strips Unicode whitespace.
- `explode(",", "")` returns `[""]`, as does Go's `strings.Split`.

**Observed version changes:**

| Expression | 5.6 | 7.4 | 8.x |
| --- | --- | --- | --- |
| `substr("abc", 3)` | false | `""` | `""` |
| `substr("abc", 5)` | false | false | `""` |
| `strpos("abc", "")` | false + E_WARNING | false + E_WARNING | 0 |

A `false` return turns into `""` when echoed and into `0` in arithmetic. Code such as `if (strpos($s, $x))` fails when the match is at offset 0.

**Find:** `strlen(`, `substr(`, `str_pad(`, `strtoupper(`, `strtolower(`, `ucfirst(`, `ucwords(`, `strrev(`, `wordwrap(`, `str_split(`, `trim(`, `strpos(` compared without `!== false`, and length limits on titles or nicknames.

**Go:** Use byte-based operations (`s[i:j]`, `len`, `bytes`) for PHP byte functions, and rune-based operations only for `mb_*` equivalents. Check `mbstring.func_overload` (removed in 8.0), which silently replaces byte functions with `mb_*` versions.

## Multibyte Functions

- `mb_*` functions use `mb_internal_encoding()`, which defaults to the `default_charset` setting (UTF-8 since 5.6). Legacy sites often set it to EUC-KR in `php.ini` or in a bootstrap file.
- Invalid byte sequences are replaced or counted differently by `mb_strlen`, `mb_substr`, and Go's rune decoding (Go returns U+FFFD per invalid byte). Capture examples when the input can contain invalid bytes.
- `mb_strimwidth`, `mb_strwidth`, and East Asian width rules are commonly used for truncating Korean titles. Port them with tests against the reference output.

## HTML Escaping

**Observed:**

| Call | 5.6 / 7.4 | 8.1+ |
| --- | --- | --- |
| `htmlspecialchars("<a href='x'>\"&amp;")` | `&lt;a href='x'&gt;&quot;&amp;amp;` | `&lt;a href=&#039;x&#039;&gt;&quot;&amp;amp;` |
| `htmlspecialchars("a\xC3(")` (invalid UTF-8) | `""` | `a\u{FFFD}(` |

- PHP's default flags changed in 8.1 from `ENT_COMPAT` to `ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401`. Before 8.1, single quotes are not escaped by default, and invalid UTF-8 makes the whole result an empty string. This can blank out user content.
- The default charset became UTF-8 in 5.4. For EUC-KR content, code must pass the charset explicitly, or `default_charset` must be set.
- PHP emits `&#039;` and `&quot;`. Go's `html.EscapeString` and `html/template` emit `&#39;` and `&#34;`. They are equivalent to browsers but differ byte for byte.
- `double_encode` defaults to true: `&amp;` becomes `&amp;amp;`.
- `htmlentities("é")` gives `&eacute;`. Go has no equivalent in the standard library.
- `strip_tags("a<b>c</b><script>x</script>d<")` gives `acxd`. It removes tags but keeps script content, and it is not a sanitizer.
- `nl2br` inserts `<br />` before each line break and keeps the original `\r\n` or `\n`.

**Go:** For byte-exact output, write `phpHTMLSpecialChars(s, flags, charset)` that matches the reference version. If the Go templates use `html/template`, decide in the contract whether to compare HTML bytes exactly or with a declared entity normalization, and record the decision. Do not normalize away escaping differences in attribute values or script contexts.

## URL Encoding

**Observed:** `urlencode("a b+c~")` gives `a+b%2Bc%7E` (PHP encodes `~` with `urlencode`). `rawurlencode` gives `a%20b%2Bc~`. Go's `url.QueryEscape` gives `a+b%2Bc~`, and `url.PathEscape` gives `a%20b+c~`. `http_build_query` drops `null` values, converts `true` to `1`, and encodes brackets as `%5B0%5D`.

## EUC-KR and CP949

**Observed (all versions with iconv and mbstring):**

- `iconv('CP949', 'UTF-8', ...)` and `mb_convert_encoding(..., 'UTF-8', 'CP949')` decode extended Hangul (for example `\x8C\x63`, U+B620).
- `mb_convert_encoding(..., 'UTF-8', 'EUC-KR')` does not decode extended CP949 characters and produced `?c`. Legacy data labeled EUC-KR often contains CP949 bytes.
- `iconv` with invalid input returns `false` with E_NOTICE. `//IGNORE` drops invalid bytes. `//TRANSLIT` depends on the C library.

**Find:** `iconv(`, `mb_convert_encoding(`, `mb_internal_encoding(`, `default_charset`, `SET NAMES`, `charset=euc-kr` in `<meta>` or headers, and `.htaccess` `AddDefaultCharset`.

**Go:** Use `golang.org/x/text/encoding/korean.EUCKR`, which implements the CP949 superset, only after confirming that it matches the reference output for the data's byte ranges. Preserve the database connection charset and the response `Content-Type` charset. Verify both directions for request input (form posts in EUC-KR pages) and output.

## printf and Number Formatting

**Observed (all versions):** `number_format(1234.5)` gives `1,235`, `number_format(0.5)` gives `1`, and `number_format(1.005, 2)` gives `1.01`, because PHP rounds half away from zero after pre-rounding. `sprintf('%.2f', 2.675)` gives `2.67`, as Go does. `sprintf('%d', "12abc")` gives `12`. `sprintf('%u', -1)` gives `18446744073709551615`. `sprintf('%s', 0.1 + 0.2)` gives `0.3` because it uses `precision`.

**Go:** Implement `number_format` with PHP's rounding, not `fmt.Sprintf`. Convert `%s` with float arguments through the PHP float-to-string rules in [numbers-json.md](numbers-json.md).
