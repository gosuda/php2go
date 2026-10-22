<?php
// Run through the built-in web server: see scripts/run-request-probe.sh.
// Query selects the case; output is inspected with response headers.
$case = isset($_GET['case']) ? $_GET['case'] : '';
switch ($case) {
    case 'location':
        header('Location: /elsewhere');
        echo 'body after location';
        break;
    case 'location201':
        http_response_code(201);
        header('Location: /elsewhere');
        break;
    case 'notice':
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
        echo 'start;';
        $a = array();
        echo @$undefined_but_suppressed;
        echo $a['missing'];
        echo ';end';
        break;
    case 'late_header':
        echo 'x';
        header('X-Late: 1');
        break;
    case 'session':
        session_start();
        $_SESSION['n'] = isset($_SESSION['n']) ? $_SESSION['n'] + 1 : 1;
        $_SESSION['s'] = 'str';
        $_SESSION['f'] = 0.1;
        echo session_encode();
        break;
    case 'exit_shutdown':
        register_shutdown_function(function () { echo '[shutdown]'; });
        ob_start(function ($b) { return strtoupper($b); });
        echo 'before exit';
        exit('exit message');
    case 'fatal':
        ini_set('display_errors', '1');
        echo 'before fatal;';
        undefined_function_call();
        break;
    case 'fatal_no_output':
        ini_set('display_errors', '0');
        undefined_function_call();
        break;
    case 'duplicate_header':
        header('X-Dup: a');
        header('X-Dup: b');
        header('X-Multi: a');
        header('X-Multi: b', false);
        setcookie('c1', 'v 1');
        setcookie('c1', 'v2');
        setcookie('c2', 'a;b,c', 0, '/', '', false, true);
        echo 'ok';
        break;
    default:
        echo 'unknown case';
}
