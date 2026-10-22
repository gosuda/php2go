# Runtime Configuration and Legacy Features

Most entries here are **Documented**: they depend on `php.ini`, extensions, or removed features that the bundled probes do not exercise. Confirm them on the reference runtime.

## Configuration That Changes Semantics

Record the effective values of these settings from the reference runtime and include them in the runtime profile:

- Input: `request_order`, `variables_order`, `arg_separator.input`, `max_input_vars`, `max_input_nesting_level`, `post_max_size`, `upload_max_filesize`, `max_file_uploads`, `file_uploads`.
- Output: `output_buffering`, `output_handler`, `zlib.output_compression`, `implicit_flush`, `default_charset`, `default_mimetype`, `expose_php`.
- Errors: `error_reporting`, `display_errors`, `display_startup_errors`, `html_errors`, `log_errors`, `error_log`.
- Numbers and text: `precision`, `serialize_precision`, `mbstring.func_overload` (removed in 8.0), `mbstring.internal_encoding`, `mbstring.language`, `iconv.*`, `setlocale` calls.
- Time: `date.timezone`.
- Sessions: `session.save_handler`, `session.save_path`, `session.serialize_handler`, `session.name`, `session.cookie_*`, `session.use_strict_mode`, `session.use_only_cookies`, `session.use_trans_sid`, `session.cache_limiter`, `session.gc_*`, `session.sid_length`.
- Limits: `max_execution_time`, `memory_limit`, `pcre.backtrack_limit`, `pcre.recursion_limit`, `pcre.jit`.
- Paths and security: `open_basedir`, `include_path`, `auto_prepend_file`, `auto_append_file`, `allow_url_fopen`, `allow_url_include`, `disable_functions`.

Settings can come from the main `php.ini`, `conf.d` files, `.user.ini`, `.htaccess` `php_value`/`php_flag` (Apache mod_php), PHP-FPM pool `php_admin_value`, or `ini_set()` at runtime. `auto_prepend_file` can inject code before every request; include it in the analysis.

## Removed Legacy Features (PHP 5.x Targets)

- `register_globals` (off by default since 4.2, removed in 5.4): request variables become globals. Uninitialized variables can be set by any request parameter, which is a common authorization bypass.
- `magic_quotes_gpc` (removed in 5.4): request values arrive with `addslashes` already applied. Code may call `stripslashes` conditionally with `get_magic_quotes_gpc()`. Removing magic quotes without matching escaping changes SQL behavior and stored data.
- `extract($_GET)`, `extract($_POST)`, `$$name` (variable variables), `compact`, and `$GLOBALS[$name]` emulate `register_globals` in many legacy apps. Treat them as dynamic data flow during analysis.
- `ereg*` and `split()` (removed in 7.0) use POSIX regular expressions. `preg_replace` with `/e` was removed in 7.0.
- `mysql_*` (removed in 7.0): `mysql_real_escape_string` depends on the connection charset, and `addslashes` escaping is vulnerable with multibyte charsets whose trail bytes include `0x5C`, such as GBK, Big5, and SJIS. EUC-KR and CP949 trail bytes do not include `0x5C`, but confirm the charset that the connection actually negotiates (`SET NAMES`, `mysql_set_charset`, server defaults).
- `each()`, `create_function()`, and `call_user_method()` were removed in 8.0 or earlier. Their presence identifies the minimum runtime required.

## Database Drivers

- `mysql_*`, non-prepared `mysqli` queries without `MYSQLI_OPT_INT_AND_FLOAT_NATIVE`, and PDO MySQL with emulated prepares before 8.1 return every column as a string, and SQL `NULL` as PHP `null`. PHP code then relies on loose comparison of those strings (`$row['cnt'] == 0`). With mysqlnd, `mysqli` prepared statements and PDO native prepares return integers and floats, and PDO emulated prepares do the same from 8.1. Check the driver, client library, and attributes in use, and see the sql-extract Skill for Go mapping rules.
- `PDO::ATTR_EMULATE_PREPARES` defaults to true for MySQL. Emulated prepares interpolate values client-side, which changes the type and charset handling of bound parameters.
- `PDO::ATTR_ERRMODE` defaults to silent (`ERRMODE_SILENT`) before 8.0 and to exceptions in 8.0+. The `mysql_*` and `mysqli` functions return `false` on errors, which code often ignores.
- `mysql_insert_id()` and `lastInsertId()` are connection-local. `mysql_affected_rows()` counts changed rows by default, not matched rows, unless `CLIENT_FOUND_ROWS` is set.

## Randomness, IDs, and Hashing

- `rand()` is an alias of `mt_rand()` since 7.1. `mt_rand` with a fixed seed produces a different sequence in 7.1+ than in 5.x because the Mersenne Twister implementation was fixed. Before 7.1, `rand()` used the C library. `shuffle`, `array_rand`, and `str_shuffle` use the same generator. Go cannot reproduce these sequences without a port. Control randomness in fixtures instead.
- `uniqid()` is derived from the current time in microseconds and is not unique across processes. `lcg_value()` and `microtime()` are time-based.
- Legacy password storage: `md5($pw)`, `sha1($pw)`, salted variants, `crypt()` with DES, MD5 (`$1$`), or Blowfish (`$2a$`/`$2y$`), MySQL `PASSWORD()`, and `password_hash()` (5.5+, `$2y$` bcrypt by default). Go's `golang.org/x/crypto/bcrypt` verifies `$2a$` and `$2y$`. Plan rehashing on login as a recorded security change, not as part of equivalence.
- `md5()` and `sha1()` return lowercase hexadecimal. With the raw-output argument, they return binary bytes.

## Includes and Dynamic Code

- `include`, `require`, `include_once`, and `require_once` with variable paths, `include_path` resolution, and relative paths resolved against the including script's directory and the current working directory all affect which code runs. The current working directory differs between SAPIs.
- `eval`, `assert` with string arguments (removed in 8.0), `create_function`, `call_user_func` with request-controlled names, `$obj->$method()`, and `new $class` make static analysis incomplete. Record them as unexamined areas or trace them with runtime observation.
- Files can execute top-level code and output when included. Shared bootstrap files often set headers, start sessions, open database connections, or set the time zone.
