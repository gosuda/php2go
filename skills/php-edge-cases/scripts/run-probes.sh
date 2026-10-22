#!/bin/sh
# Run every CLI probe with one PHP binary and write one output file per probe.
#
# Usage: scripts/run-probes.sh /path/to/php OUTPUT_DIR
#
# Probes run with `-n` (no php.ini) so that results reflect engine defaults.
# Shared builds of mbstring, iconv, and ctype are loaded when the binary does
# not already include them. Production behavior also depends on the deployed
# php.ini: rerun with `-c /path/to/php.ini` via PHP_PROBE_ARGS to capture it.
set -eu

php_bin=${1:?usage: run-probes.sh PHP_BIN OUTPUT_DIR}
out_dir=${2:?usage: run-probes.sh PHP_BIN OUTPUT_DIR}
probe_dir=$(cd "$(dirname "$0")/probes" && pwd)
mkdir -p "$out_dir"

ext_args=""
for ext in mbstring iconv ctype; do
    if ! "$php_bin" -n -m | grep -qx "$ext"; then
        ext_args="$ext_args -d extension=$ext"
    fi
done

for probe in "$probe_dir"/*.php; do
    name=$(basename "$probe" .php)
    case "$name" in
        _lib|request|output_headers) continue ;;
    esac
    # shellcheck disable=SC2086
    if ! "$php_bin" -n $ext_args ${PHP_PROBE_ARGS:-} "$probe" > "$out_dir/$name.txt" 2>&1; then
        echo "probe failed: $name (see $out_dir/$name.txt)" >&2
    fi
done

"$php_bin" -n $ext_args -m > "$out_dir/modules.txt" 2>&1 || true
echo "wrote $out_dir"
