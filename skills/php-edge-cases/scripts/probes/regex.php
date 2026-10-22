<?php
require __DIR__ . '/_lib.php';

section('PCRE behavior');
p('backreference', function () { return preg_match('/(a)\1/', 'aa'); });
p('lookahead', function () { return preg_replace('/a(?=b)/', 'X', 'abac'); });
p('lookbehind', function () { return preg_replace('/(?<=a)b/', 'X', 'abcb'); });
p('possessive', function () { return preg_match('/a++b/', 'aaab'); });
p('dot without /s', function () { return preg_match('/a.b/', "a\nb"); });
p('$ before trailing newline', function () { return preg_match('/^abc$/', "abc\n"); });
p('$ with /D', function () { return preg_match('/^abc$/D', "abc\n"); });
p('\d without /u', function () { return preg_match('/^\d$/', "\xD9\xA3"); });
p('\d with /u', function () { return preg_match('/^\d$/u', "\xD9\xA3"); });
p('\w on Korean without /u', function () { return preg_match_all('/\w/', "가"); });
p('\w on Korean with /u', function () { return preg_match_all('/\w/u', "가"); });
p('/u invalid utf8 subject', function () { return preg_match('/a/u', "a\xFF"); });
p('/u invalid utf8 error', function () { preg_match('/a/u', "a\xFF"); return preg_last_error(); });
p('/u replace invalid returns', function () { return preg_replace('/a/u', 'b', "a\xFF"); });
p('backtrack limit', function () { return preg_match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar'); });
p('catastrophic backtracking result', function () { return preg_match('/(a+)+$/', str_repeat('a', 30) . 'b'); });
p('catastrophic backtracking error', function () { preg_match('/(a+)+$/', str_repeat('a', 30) . 'b'); return preg_last_error(); });
p('alternation leftmost-first', function () { preg_match('/a|ab/', 'ab', $m); return $m[0]; });
p('preg_split empty', function () { return preg_split('//', 'abc', -1, PREG_SPLIT_NO_EMPTY); });
p('preg_match unmatched group', function () { preg_match('/(a)(b)?(c)/', 'ac', $m); return $m; });
p('preg_match trailing unmatched group', function () { preg_match('/(a)(b)?/', 'a', $m); return $m; });
p('preg_quote', function () { return preg_quote('a.b*c#/'); });
p('/e modifier', function () { return @preg_replace('/a/e', 'strtoupper("$0")', 'abc'); });
