<?php
// Run through the built-in web server: see scripts/run-request-probe.sh.
if (PHP_SAPI !== 'cli-server') {
    fwrite(STDERR, "run with php -S via scripts/run-request-probe.sh\n");
    exit(1);
}
header('Content-Type: text/plain');
require __DIR__ . '/_lib.php';

section('superglobals for ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);
echo 'GET => ', d($_GET), "\n";
echo 'POST => ', d($_POST), "\n";
echo 'COOKIE => ', d($_COOKIE), "\n";
echo 'REQUEST => ', d($_REQUEST), "\n";
echo 'FILES => ', d($_FILES), "\n";
echo 'request_order => ', d(ini_get('request_order')), "\n";
echo 'variables_order => ', d(ini_get('variables_order')), "\n";
echo 'max_input_vars => ', d(ini_get('max_input_vars')), "\n";
echo 'QUERY_STRING => ', d(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null), "\n";
echo 'php://input length => ', strlen(file_get_contents('php://input')), "\n";
