<?php
require __DIR__ . '/_lib.php';

section('float to string');
p('(string)(0.1+0.2)', function () { return (string)(0.1 + 0.2); });
p('(string)1e15', function () { return (string)1e15; });
p('(string)1e14', function () { return (string)1e14; });
p('(string)1.0', function () { return (string)1.0; });
p('(string)-0.0', function () { return (string)-0.0; });
p('(string)0.00001', function () { return (string)0.00001; });
p('(string)123456789012345678.0', function () { return (string)123456789012345678.0; });
p('(string)(7/3)', function () { return (string)(7 / 3); });
p('ini precision', function () { return ini_get('precision'); });
p('ini serialize_precision', function () { return ini_get('serialize_precision'); });
p('var_export(0.1)', function () { return var_export(0.1, true); });
p('float key string', function () { return "x" . 1.5e-7; });
p('setlocale-independent float', function () { return (string)1.5; });

section('json_encode');
p('slash and unicode', function () { return json_encode(array('u' => "/한<>&'\"")); });
p('JSON_UNESCAPED flags', function () { return json_encode("/한<>", JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); });
p('float 0.1', function () { return json_encode(0.1); });
p('float 0.1+0.2', function () { return json_encode(0.1 + 0.2); });
p('float 1.0', function () { return json_encode(1.0); });
p('float 10.0/3', function () { return json_encode(10.0 / 3); });
p('invalid utf8', function () { return json_encode("a\xFF"); });
p('invalid utf8 last error', function () { json_encode("a\xFF"); return json_last_error(); });
p('NAN', function () { return json_encode(NAN); });
p('empty object', function () { return json_encode(new stdClass()); });
p('null', function () { return json_encode(null); });
p('pretty print', function () { return json_encode(array('a' => array()), JSON_PRETTY_PRINT); });
p('numeric string', function () { return json_encode("12"); });
p('JSON_NUMERIC_CHECK', function () { return json_encode(array("12", "1e3", "0x1A", "012"), JSON_NUMERIC_CHECK); });

section('json_decode');
p('object default', function () { return gettype(json_decode('{"a":1}')); });
p('assoc', function () { return json_decode('{"a":1,"1":2}', true); });
p('big int', function () { return json_decode('12345678901234567890'); });
p('big int as string', function () { return json_decode('12345678901234567890', false, 512, JSON_BIGINT_AS_STRING); });
p('float 1.0', function () { return json_decode('1.0'); });
p('empty string', function () { return json_decode(''); });
p('trailing comma', function () { return json_decode('[1,]'); });
p('single quotes', function () { return json_decode("{'a':1}"); });
p('duplicate keys', function () { return json_decode('{"a":1,"a":2}', true); });
p('empty key', function () { $o = json_decode('{"":1}'); return array_keys(get_object_vars($o)); });

section('serialize');
p('serialize array', function () { return serialize(array('a' => 1, 2 => 'b', 'f' => 0.1, 'n' => null, 't' => true)); });
p('unserialize false', function () { return unserialize('b:0;'); });
p('unserialize garbage', function () { return unserialize('garbage'); });
