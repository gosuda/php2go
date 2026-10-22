#!/bin/sh
# Exercise request parsing and output/header probes through the built-in server.
#
# Usage: scripts/run-request-probe.sh /path/to/php [PORT]
#
# The built-in server is a convenience SAPI. Confirm production behavior with the
# actual web server and SAPI (Apache mod_php, PHP-FPM behind nginx, and so on),
# because php.ini, output buffering, and header handling can differ.
set -eu

php_bin=${1:?usage: run-request-probe.sh PHP_BIN [PORT]}
port=${2:-8089}
probe_dir=$(cd "$(dirname "$0")/probes" && pwd)
base="http://127.0.0.1:$port"

"$php_bin" -n -d session.save_path="${TMPDIR:-/tmp}" -S "127.0.0.1:$port" -t "$probe_dir" > /dev/null 2>&1 &
server=$!
trap 'kill "$server" 2>/dev/null || true' EXIT INT TERM
i=0
until curl -s -o /dev/null "$base/request.php"; do
    i=$((i + 1))
    [ "$i" -lt 50 ] || { echo "server did not start" >&2; exit 1; }
    sleep 0.1
done

show() {
    echo
    echo "### $*"
    curl -sS -g -i "$@" | tr -d '\r'
    echo
}

show "$base/request.php?a=1&a=2&b[]=1&b[]=2&c.d=3&e%20f=4&g[x][y]=5"
show "$base/request.php?k=get" -H 'Cookie: k=cookie; a.b=1; a.b=2' --data 'k=post&p[]=1'
show "$base/request.php" -H 'Content-Type: application/json' --data '{"a":1}'
show "$base/request.php" -F 'up=@/dev/null;filename=../../x.txt' -F 'f[a]=1'
for case in location location201 notice late_header exit_shutdown fatal fatal_no_output duplicate_header session; do
    show "$base/output_headers.php?case=$case"
done
