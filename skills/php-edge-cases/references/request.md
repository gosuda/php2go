# Request Parsing

PHP parses query strings, form bodies, cookies, and uploads into superglobals before the script runs. Go's `net/url` and `net/http` keep raw values and fail on input that PHP accepts. Reproduce PHP's parser when the application consumes `$_GET`, `$_POST`, `$_COOKIE`, `$_REQUEST`, or `$_FILES`.

## Query and Form Parsing

`parse_str()` uses the same parser as `$_GET` and `$_POST`. **Observed (5.6 / 7.4 / 8.x identical):**

| Input | Result |
| --- | --- |
| `a=1&a=2` | `a => "2"` (last wins) |
| `a[]=1&a[]=2` | `a => ["1", "2"]` |
| `a[x][y]=1` | nested arrays |
| `a=1&a[]=2` | `a => ["2"]` (later replaces earlier) |
| `a[]=1&a=2` | `a => "2"` |
| `a[1]=x&a[]=y` | `a => [1 => "x", 2 => "y"]` |
| `a[01]=x` | string key `"01"` |
| `a b=1`, `a.b=1`, `a+b=1`, `a%20b=1` | key `a_b` |
| `a[b.c]=3` | inner key keeps `.`: `a => ["b.c" => "3"]` |
| `a[=1`, `a[b=1` | keys `a_` and `a_b` |
| `a]=1` | key `a]` |
| `a`, `a=` | `a => ""` |
| `=1`, `[]=1` | ignored |
| `a=1;b=2` | `a => "1;b=2"` (`;` is not a separator by default) |
| `a=%FF` | raw byte `\xFF` (no UTF-8 validation) |
| `a=%zz` | literal `%zz` |
| `a%00b=1` | key truncated at NUL: `a` |
| `a=1%00b` | value keeps NUL byte |
| `a[x]` nested 64 levels | kept; 65 or more levels dropped (`parse_str` emits E_WARNING; `max_input_nesting_level=64`) |

- Only top-level key names have spaces and dots (and `[` when unbalanced) converted to `_`. Within brackets, characters are kept.
- Go's `url.ParseQuery` keeps every repeated value, keeps key names unchanged, returns an error on `;` (Go 1.17+) and on invalid escapes, and does not build nested structures. `Values.Get` returns the first value, while PHP keeps the last.
- `arg_separator.input` changes the separator set (for example `&;`).
- `max_input_vars` (default 1000, 5.3.9+) silently drops variables beyond the limit with a warning. Large forms such as bulk-edit pages hit this limit.

**Go:** Implement a `phpParseQuery(raw string) orderedValue` and reuse it for query, `application/x-www-form-urlencoded` bodies, and cookies. Test it against `parse_str` output from the reference version, including the cases above.

## Superglobal Merging and Bodies

**Observed (built-in server, `-n`):**

- `$_REQUEST` follows `request_order`, or `variables_order` when `request_order` is empty (default `GPC`). With `?k=get`, body `k=post`, and cookie `k=cookie`, `$_REQUEST['k']` was `"cookie"`. Production `php.ini` files usually set `request_order = "GP"`, which excludes cookies. Read the deployed value.
- Repeated cookie names (`a.b=1; a.b=2`) keep the first value (`a_b => "1"`), the opposite of query parameters. Cookie names also have `.` and space replaced with `_`.
- JSON bodies are not parsed into `$_POST`. Code reads `php://input`. `$_POST` is filled only for `application/x-www-form-urlencoded` and `multipart/form-data`.
- `post_max_size` exceeded: `$_POST` and `$_FILES` are empty, with a warning. `upload_max_filesize` exceeded: the file entry has `error => UPLOAD_ERR_INI_SIZE`.
- `$_SERVER['QUERY_STRING']` and `REQUEST_URI` keep the raw form, so code that reparses them behaves differently from code that uses `$_GET`.

## Uploads

**Observed:** For a part named `up` with `filename=../../x.txt`, `$_FILES['up']['name']` was `x.txt` (basename) in all versions, and 8.1+ also added `full_path => "../../x.txt"`. `type` comes from the client. `f[a]` multipart fields nest like query strings, and array upload fields (`up[]`) transpose to `$_FILES['up']['name'][0]`.

**Go:** Go's `multipart.FileHeader.Filename` also strips directories, but the rules differ for backslashes and other edge cases. Reproduce PHP's `$_FILES` shape, including the transposed array layout, error codes, and `is_uploaded_file` and `move_uploaded_file` behavior. Record any original upload validation weaknesses as security findings.

**Find:** `$_GET`, `$_POST`, `$_REQUEST`, `$_COOKIE`, `$_FILES`, `$_SERVER['QUERY_STRING']`, `$_SERVER['REQUEST_URI']`, `parse_str(`, `php://input`, `extract($_`, `import_request_variables(`, and `register_globals` assumptions (see [runtime-config.md](runtime-config.md)).
