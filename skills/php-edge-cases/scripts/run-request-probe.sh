#!/bin/sh
# Exercise request parsing and output/header probes through the built-in server.
#
# Usage: scripts/run-request-probe.sh /path/to/php [PORT]
#
# Configuration: by default PHP runs with `-n` (no php.ini) so that results
# reflect engine defaults. To reproduce the reference runtime, set
# PHP_PROBE_ARGS to the PHP options that select its configuration, for example
# PHP_PROBE_ARGS="-c /etc/php/7.4/apache2/php.ini". When PHP_PROBE_ARGS is set,
# `-n` is not added. Shared builds of mbstring, iconv, and ctype are loaded only
# when they are missing under the selected configuration.
# With the default `-n`, session.save_path is set to $TMPDIR (or /tmp). With
# PHP_PROBE_ARGS, the configured session settings are used unchanged; an
# unwritable save_path is itself an observation of that configuration.
#
# The built-in server is a convenience SAPI. Confirm production behavior with the
# actual web server and SAPI (Apache mod_php, PHP-FPM behind nginx, and so on),
# because output buffering and header handling can differ.
#
# Exit status is nonzero if the server cannot start or any request fails at the
# transport level. HTTP error statuses are observations, not failures.
set -eu

php_bin=${1:?usage: run-request-probe.sh PHP_BIN [PORT]}
port=${2:-8089}
probe_dir=$(cd "$(dirname "$0")/probes" && pwd)
base="http://127.0.0.1:$port"

if [ -n "${PHP_PROBE_ARGS:-}" ]; then
    php_args=$PHP_PROBE_ARGS
    session_args=""
else
    php_args="-n"
    session_args="-d session.save_path=${TMPDIR:-/tmp}"
fi
ext_args=""
# shellcheck disable=SC2086
modules=$("$php_bin" $php_args -m 2>/dev/null) || { echo "cannot run $php_bin $php_args -m" >&2; exit 1; }
for ext in mbstring iconv ctype; do
    if ! printf '%s\n' "$modules" | grep -qx "$ext"; then
        ext_args="$ext_args -d extension=$ext"
    fi
done

log=$(mktemp "${TMPDIR:-/tmp}/php-probe-server.XXXXXX")
# shellcheck disable=SC2086
"$php_bin" $php_args $ext_args $session_args -S "127.0.0.1:$port" -t "$probe_dir" > "$log" 2>&1 &
server=$!
trap 'kill "$server" 2>/dev/null || true; rm -f "$log"' EXIT INT TERM
i=0
until curl -s -o /dev/null "$base/request.php"; do
    i=$((i + 1))
    if [ "$i" -ge 50 ] || ! kill -0 "$server" 2>/dev/null; then
        echo "server did not start; log follows" >&2
        cat "$log" >&2
        exit 1
    fi
    sleep 0.1
done

echo "# php_args: $php_args"
echo "# ext_args:$ext_args"

failed=0
show() {
    echo
    echo "### $*"
    if response=$(curl -sS -g -i "$@" 2>&1); then
        printf '%s\n' "$response" | tr -d '\r'
    else
        printf '%s\n' "$response"
        echo "request failed: $*" >&2
        failed=$((failed + 1))
    fi
}

show "$base/request.php?a=1&a=2&b[]=1&b[]=2&c.d=3&e%20f=4&g[x][y]=5"
show "$base/request.php?k=get" -H 'Cookie: k=cookie; a.b=1; a.b=2' --data 'k=post&p[]=1'
show "$base/request.php" -H 'Cookie: p=a+b%20c; q[x]=1; q[]=2; r=1, s=2;t = 3 ;u=%zz'
show "$base/request.php" -H 'Content-Type: application/json' --data '{"a":1}'
show "$base/request.php" -F 'up=@/dev/null;filename=../../x.txt' -F 'f[a]=1'
for case in location location201 notice late_header exit_shutdown fatal fatal_no_output duplicate_header session; do
    show "$base/output_headers.php?case=$case"
done

if [ "$failed" -ne 0 ]; then
    echo "$failed request(s) failed" >&2
    exit 1
fi
