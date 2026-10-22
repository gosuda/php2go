<?php
require __DIR__ . '/_lib.php';

section('key normalization');
p('["1"=>a]', function () { return array("1" => 'a'); });
p('["01"=>a]', function () { return array("01" => 'a'); });
p('["-0"=>a]', function () { return array("-0" => 'a'); });
p('["-5"=>a]', function () { return array("-5" => 'a'); });
p('[1.7=>a]', function () { return array(1.7 => 'a'); });
p('[true=>a]', function () { return array(true => 'a'); });
p('[null=>a]', function () { return array(null => 'a'); });
p('["9223372036854775808"=>a]', function () { return array("9223372036854775808" => 'a'); });
p('array_keys(["1"=>a,"x"=>b])', function () { return array_keys(array("1" => 'a', "x" => 'b')); });

section('ordering and append');
p('insertion order', function () { $a = array(); $a['b'] = 1; $a['a'] = 2; $a[5] = 3; $a[] = 4; return $a; });
p('negative first key then append', function () { $a = array(-5 => 'a'); $a[] = 'b'; return $a; });
p('unset then append', function () { $a = array(1, 2, 3); unset($a[2]); $a[] = 'x'; return $a; });
p('array_merge renumbers int keys', function () { return array_merge(array(5 => 'a'), array(5 => 'b')); });
p('+ keeps left keys', function () { return array(5 => 'a') + array(5 => 'b', 6 => 'c'); });
p('array_merge string keys overwrite', function () { return array_merge(array('k' => 1), array('k' => 2)); });
p('array_filter keeps keys', function () { return array_filter(array(1, 0, 2)); });
p('array_unique keeps first keys', function () { return array_unique(array('b', 'a', 'b')); });

section('sorting');
p('sort mixed', function () { $a = array("10", 9, "9a", "abc", 1.5, true); sort($a); return $a; });
p('sort numeric strings', function () { $a = array("10", "9", "1e1", "010"); sort($a); return $a; });
p('stable usort', function () { $a = array(array('k' => 1, 'v' => 'a'), array('k' => 0, 'v' => 'b'), array('k' => 1, 'v' => 'c'), array('k' => 0, 'v' => 'd')); usort($a, function ($x, $y) { return $x['k'] - $y['k']; }); $o = array(); foreach ($a as $r) { $o[] = $r['v']; } return implode('', $o); });
p('usort bool comparator', function () { $a = array(3, 1, 2); usort($a, function ($x, $y) { return $x > $y; }); return $a; });
p('ksort mixed keys', function () { $a = array('b' => 1, 10 => 2, 'a' => 3, 2 => 4, '1a' => 5); ksort($a); return array_keys($a); });

section('copy and reference');
p('array assignment copies', function () { $a = array(1); $b = $a; $b[] = 2; return $a; });
p('foreach by ref leftover', function () { $a = array(1, 2, 3); foreach ($a as &$v) {} foreach ($a as $v) {} return $a; });
p('reference survives copy', function () { $x = 1; $a = array(&$x); $b = $a; $b[0] = 9; return $a; });

section('list/json');
p('json_encode list', function () { return json_encode(array(1, 2)); });
p('json_encode gap', function () { return json_encode(array(0 => 1, 2 => 2)); });
p('json_encode empty array', function () { return json_encode(array()); });
p('json_encode string keys numeric', function () { return json_encode(array("0" => 'a', "1" => 'b')); });
p('json_encode after unset', function () { $a = array(1, 2, 3); unset($a[0]); return json_encode($a); });
