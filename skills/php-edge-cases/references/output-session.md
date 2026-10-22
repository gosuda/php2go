# Output, Headers, Errors, and Sessions

## Status and Headers

**Observed (built-in server, all versions):**

- `header('Location: ...')` sets status 302 unless the status was already set to 201 or a 3xx code. With `http_response_code(201)` first, the response stayed 201 with a `Location` header.
- Code after `header('Location: ...')` keeps running and its output is sent in the body unless the script calls `exit`. Missing `exit` after a redirect is a common authorization bug. Record it as a finding, and preserve the observable body in the contract.
- `header('X: a'); header('X: b');` sends only `b`. `header('X: b', false)` adds a second header.
- `setcookie` sends separate `Set-Cookie` headers, including duplicates for the same name. Values are URL-encoded: 5.6 encodes a space as `+`, and 7.x and 8.x encode it as `%20`. The attribute is spelled `httponly` on 5.6 and `HttpOnly` on 7.x and 8.x. `SameSite` options exist from 7.3 onward.
- Calling `header()` after output has been sent fails with "headers already sent" when output buffering is off. The header is lost, and the warning appears in the body when `display_errors` is on. With `output_buffering=4096`, the default in production `php.ini` files, the same code succeeds until the buffer fills. Behavior depends on response size.
- PHP adds `X-Powered-By` (`expose_php`) and `Content-type: text/html; charset=UTF-8` (from `default_mimetype` and `default_charset`) when the script does not set them.

**Find:** `header(`, `http_response_code(`, `setcookie(`, `setrawcookie(`, `headers_sent(`, `ob_start(`, `ob_get_clean(`, `flush(`, files that start with a BOM or whitespace before `<?php`, and files that end with `?>` followed by whitespace or newlines.

**Go:** Preserve header order only where the contract compares it. Combine headers with PHP's replace semantics. Reproduce stray BOM or whitespace output only if the reference emits it, which usually depends on `output_buffering`.

## Errors in the Response

**Observed:**

- With `display_errors=1`, notices and warnings are inserted into the HTML body at the point where they occur. For example, 5.6 and 7.4 print `<b>Notice</b>:  Undefined index: missing`, and 8.x prints `<b>Warning</b>:  Undefined array key "missing"`. Message text and severity differ by version, and messages include absolute file paths.
- `@` suppresses the message only.
- An uncaught fatal error after output gives status 200 with a partial body and the error message. With `display_errors=0` and no output yet, the built-in server returned status 500 with an empty body. Under other SAPIs and web servers, a fatal error can produce 200 with an empty body or a server error page.
- `error_reporting`, `display_errors`, `log_errors`, `html_errors`, and custom `set_error_handler` callbacks control this. Development and production configurations differ.

**Go:** If the reference runs with `display_errors` on, decide in the contract whether error output is part of the observable behavior. Capture the reference fixtures with the production configuration.

## `exit`, Output Buffers, and Shutdown

**Observed:** With `register_shutdown_function` and an `ob_start` callback that converts to uppercase, `echo 'before exit'; exit('exit message');` produced `BEFORE EXITEXIT MESSAGE[SHUTDOWN]`. `exit` prints its string argument, buffer callbacks run on flush, and shutdown functions run after `exit` and after fatal errors. Destructors also run at shutdown.

**Find:** `exit`, `die(`, `register_shutdown_function(`, `ob_start(` with callbacks (often gzip or template post-processing), `__destruct`, `ignore_user_abort(`, `fastcgi_finish_request(`.

**Go:** Model `exit` as an explicit early return that still runs registered output filters and shutdown hooks in PHP's order. Do not rely on `defer` alone without checking the order.

## Sessions

**Observed:**

- `session_start()` sends `Set-Cookie: PHPSESSID=...; path=/` and, with the default `session.cache_limiter=nocache`, adds `Expires: Thu, 19 Nov 1981 08:52:00 GMT`, `Cache-Control: no-store, no-cache, must-revalidate` (5.6 adds `, post-check=0, pre-check=0`), and `Pragma: no-cache`.
- The default `php` serializer stores `n|i:1;s|s:3:"str";f|d:0.1;`. Floats use `serialize_precision`, so 5.6 writes `d:0.10000000000000001;`. `session.serialize_handler` can be `php_binary` or `php_serialize`.

**Documented:**

- The default `files` handler locks the session file for the duration of the request. Concurrent requests from the same session are serialized, which hides race conditions that may appear in Go.
- Session ID length and character set depend on `session.sid_length` and `session.sid_bits_per_character` (7.1+), or `session.hash_function` before 7.1. Garbage collection is probabilistic (`session.gc_probability`, `gc_divisor`, `gc_maxlifetime`).
- `session.use_strict_mode` (off by default) controls whether PHP accepts uninitialized IDs supplied by the client. When it is off, session fixation is possible.
- `session_regenerate_id()` without `true` keeps the old session file.

**Go:** For staged migration with shared sessions, implement a reader and writer for the reference serializer and store, use the same cookie attributes, and reproduce locking or document its absence as a behavior change. Treat session compatibility as its own verified boundary, as the virtual-patch and go-migrate Skills require.
