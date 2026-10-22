#!/bin/sh
# Run every CLI probe with one PHP binary and write one output file per probe.
#
# Usage: scripts/run-probes.sh /path/to/php OUTPUT_DIR
#
# Configuration: by default PHP runs with `-n` (no php.ini) so that results
# reflect engine defaults. To reproduce the reference runtime, set
# PHP_PROBE_ARGS to the PHP options that select its configuration, for example
# PHP_PROBE_ARGS="-c /etc/php/7.4/apache2/php.ini". When PHP_PROBE_ARGS is set,
# `-n` is not added. Shared builds of mbstring, iconv, and ctype are loaded only
# when they are missing under the selected configuration.
#
# Exit status is nonzero if any probe fails; its output file keeps the diagnostics.
set -eu

php_bin=${1:?usage: run-probes.sh PHP_BIN OUTPUT_DIR}
out_dir=${2:?usage: run-probes.sh PHP_BIN OUTPUT_DIR}
probe_dir=$(cd "$(dirname "$0")/probes" && pwd)
mkdir -p "$out_dir"

php_args=${PHP_PROBE_ARGS:--n}
ext_args=""
# shellcheck disable=SC2086
modules=$("$php_bin" $php_args -m 2>/dev/null) || { echo "cannot run $php_bin $php_args -m" >&2; exit 1; }
for ext in mbstring iconv ctype; do
    if ! printf '%s\n' "$modules" | grep -qx "$ext"; then
        ext_args="$ext_args -d extension=$ext"
    fi
done

failed=0
for probe in "$probe_dir"/*.php; do
    name=$(basename "$probe" .php)
    case "$name" in
        _lib|request|output_headers) continue ;;
    esac
    # shellcheck disable=SC2086
    if ! "$php_bin" $php_args $ext_args "$probe" > "$out_dir/$name.txt" 2>&1; then
        echo "probe failed: $name (see $out_dir/$name.txt)" >&2
        failed=$((failed + 1))
    fi
done

# shellcheck disable=SC2086
{
    echo "php_args: $php_args"
    echo "ext_args:$ext_args"
    "$php_bin" $php_args $ext_args -m
} > "$out_dir/modules.txt" 2>&1 || true

if [ "$failed" -ne 0 ]; then
    echo "$failed probe(s) failed; outputs in $out_dir" >&2
    exit 1
fi
echo "wrote $out_dir"
