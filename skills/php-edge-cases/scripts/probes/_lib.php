<?php
// Shared helpers for php-edge-cases probes.
// Syntax must stay compatible with PHP 5.6 through current PHP 8.x.

error_reporting(E_ALL);
ini_set('display_errors', '0');

$GLOBALS['__probe_diag'] = array();

function __probe_level($no)
{
    $names = array(
        E_WARNING => 'E_WARNING',
        E_NOTICE => 'E_NOTICE',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    );
    return isset($names[$no]) ? $names[$no] : 'E_' . $no;
}

set_error_handler(function ($no, $str) {
    $GLOBALS['__probe_diag'][] = __probe_level($no);
    return true;
});

// Deterministic one-line dump with explicit types.
function d($v)
{
    if ($v === null) {
        return 'null';
    }
    if (is_bool($v)) {
        return $v ? 'true' : 'false';
    }
    if (is_int($v)) {
        return 'int(' . $v . ')';
    }
    if (is_float($v)) {
        if (is_nan($v)) {
            return 'float(NAN)';
        }
        if (is_infinite($v)) {
            return $v > 0 ? 'float(INF)' : 'float(-INF)';
        }
        // repr with 17 significant digits, independent of ini precision settings.
        return 'float(' . sprintf('%.17g', $v) . ')';
    }
    if (is_string($v)) {
        return 'string(' . strlen($v) . ') "' . addcslashes($v, "\0..\37\"\\\177..\377") . '"';
    }
    if (is_array($v)) {
        $parts = array();
        foreach ($v as $k => $x) {
            $parts[] = (is_int($k) ? $k : '"' . addcslashes($k, "\0..\37\"\\\177..\377") . '"') . '=>' . d($x);
        }
        return '[' . implode(', ', $parts) . ']';
    }
    if (is_object($v)) {
        return 'object(' . get_class($v) . ')';
    }
    return gettype($v);
}

// Evaluate a closure, print its value plus diagnostics and exceptions.
function p($label, $fn)
{
    $GLOBALS['__probe_diag'] = array();
    $out = null;
    try {
        $out = d($fn());
    } catch (Throwable $t) {
        $out = 'throws ' . get_class($t);
    } catch (Exception $e) {
        $out = 'throws ' . get_class($e);
    }
    $diag = $GLOBALS['__probe_diag'];
    echo $label, ' => ', $out, $diag ? ' [' . implode(',', array_unique($diag)) . ']' : '', "\n";
}

function section($name)
{
    echo "\n## ", $name, "\n";
}

echo '# PHP ', PHP_VERSION, ' (', PHP_INT_SIZE * 8, "-bit)\n";
