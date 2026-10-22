<?php
require __DIR__ . '/_lib.php';

section('byte vs character');
p('strlen("한글")', function () { return strlen("한글"); });
p('mb_strlen("한글", UTF-8)', function () { return mb_strlen("한글", 'UTF-8'); });
p('substr("한글", 0, 4)', function () { return substr("한글", 0, 4); });
p('mb_substr("한글", 0, 1, UTF-8)', function () { return mb_substr("한글", 0, 1, 'UTF-8'); });
p('strtoupper("é")', function () { return strtoupper("é"); });
p('ucfirst("élan")', function () { return ucfirst("élan"); });
p('str_pad("한", 5, "*")', function () { return str_pad("한", 5, "*"); });
p('strrev("한")', function () { return strrev("한"); });
p('mb_internal_encoding()', function () { return mb_internal_encoding(); });
p('substr("abc", 3)', function () { return substr("abc", 3); });
p('substr("abc", 5)', function () { return substr("abc", 5); });
p('strpos("abc", "")', function () { return strpos("abc", ""); });
p('strpos("abc", "d")', function () { return strpos("abc", "d"); });
p('explode(",", "")', function () { return explode(",", ""); });
p('trim default chars', function () { return trim("\x00\t\n\x0B\r a \xC2\xA0"); });
p('wordwrap bytes', function () { return wordwrap("가나다라", 6, "|", true); });

section('html escaping');
p('htmlspecialchars quote', function () { return htmlspecialchars("<a href='x'>\"&amp;"); });
p('htmlspecialchars ENT_QUOTES', function () { return htmlspecialchars("'", ENT_QUOTES); });
p('htmlspecialchars invalid utf8', function () { return htmlspecialchars("a\xC3("); });
p('htmlspecialchars invalid utf8 ENT_SUBSTITUTE', function () { return htmlspecialchars("a\xC3(", ENT_QUOTES | ENT_SUBSTITUTE); });
p('htmlspecialchars double_encode', function () { return htmlspecialchars("&amp; &lt;"); });
p('htmlentities("é")', function () { return htmlentities("é"); });
p('strip_tags', function () { return strip_tags("a<b>c</b><script>x</script>d<"); });
p('nl2br', function () { return nl2br("a\r\nb\nc"); });
p('addslashes', function () { return addslashes("O'Re\"i\\l\0y"); });
p('urlencode space', function () { return urlencode("a b+c~"); });
p('rawurlencode space', function () { return rawurlencode("a b+c~"); });
p('http_build_query', function () { return http_build_query(array('a' => array(1, 2), 'b' => null, 'c' => true, 'd' => 'x y')); });

section('legacy encodings');
p('iconv CP949->UTF-8', function () { return iconv('CP949', 'UTF-8', "\xC7\xD1\xB1\xDB"); });
p('mb_convert_encoding EUC-KR->UTF-8', function () { return mb_convert_encoding("\xC7\xD1\xB1\xDB", 'UTF-8', 'EUC-KR'); });
p('iconv CP949 ext char', function () { return iconv('CP949', 'UTF-8', "\x8C\x63"); });
p('mb EUC-KR ext char', function () { return mb_convert_encoding("\x8C\x63", 'UTF-8', 'EUC-KR'); });
p('mb CP949 ext char', function () { return mb_convert_encoding("\x8C\x63", 'UTF-8', 'CP949'); });
p('iconv invalid input', function () { return iconv('UTF-8', 'CP949', "a\xFFb"); });
p('iconv //IGNORE', function () { return iconv('UTF-8', 'CP949//IGNORE', "a\xFFb"); });
p('strlen of CP949 text', function () { return strlen("\xC7\xD1\xB1\xDB"); });

section('printf and formatting');
p('number_format(1234.5)', function () { return number_format(1234.5); });
p('number_format(0.5)', function () { return number_format(0.5); });
p('number_format(1.005, 2)', function () { return number_format(1.005, 2); });
p('sprintf %d with "12abc"', function () { return sprintf('%d', "12abc"); });
p('sprintf %s float', function () { return sprintf('%s', 0.1 + 0.2); });
p('sprintf %.2f', function () { return sprintf('%.2f', 2.675); });
p('sprintf %05.1f', function () { return sprintf('%05.1f', -2.35); });
p('sprintf %\'*10s', function () { return sprintf("%'*10s", "x"); });
p('sprintf %u negative', function () { return sprintf('%u', -1); });
