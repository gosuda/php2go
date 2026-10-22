<?php
require __DIR__ . '/_lib.php';

function q($s)
{
    $out = array();
    parse_str($s, $out);
    return $out;
}

section('query parsing (parse_str uses the same parser as $_GET)');
p('a=1&a=2', function () { return q('a=1&a=2'); });
p('a[]=1&a[]=2', function () { return q('a[]=1&a[]=2'); });
p('a[x][y]=1', function () { return q('a[x][y]=1'); });
p('a[=1', function () { return q('a[=1'); });
p('a]=1', function () { return q('a]=1'); });
p('a[b=1', function () { return q('a[b=1'); });
p('a b=1&a.b=2&a[b.c]=3', function () { return q('a b=1&a.b=2&a[b.c]=3'); });
p('a+b=1', function () { return q('a+b=1'); });
p('a%20b=1', function () { return q('a%20b=1'); });
p('a=1&a[]=2', function () { return q('a=1&a[]=2'); });
p('a[]=1&a=2', function () { return q('a[]=1&a=2'); });
p('a[1]=x&a[]=y', function () { return q('a[1]=x&a[]=y'); });
p('a[01]=x', function () { return q('a[01]=x'); });
p('a', function () { return q('a'); });
p('a=', function () { return q('a='); });
p('=1', function () { return q('=1'); });
p('a=1;b=2', function () { return q('a=1;b=2'); });
p('a=%FF', function () { return q('a=%FF'); });
p('a=%zz', function () { return q('a=%zz'); });
p('a=x+y', function () { return q('a=x+y'); });
p('a%00b=1', function () { return q('a%00b=1'); });
p('a=1%00b', function () { return q('a=1%00b'); });
p('[]=1', function () { return q('[]=1'); });
p('a[b][]=1&a[b][]=2', function () { return q('a[b][]=1&a[b][]=2'); });
p('64 bracket levels kept (count)', function () { return count(q('a' . str_repeat('[x]', 64) . '=1')); });
p('65 bracket levels dropped (count)', function () { return count(q('a' . str_repeat('[x]', 65) . '=1')); });
p('max_input_nesting_level ini', function () { return ini_get('max_input_nesting_level'); });
p('max_input_vars ini', function () { return ini_get('max_input_vars'); });
