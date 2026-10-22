<?php
require __DIR__ . '/_lib.php';

date_default_timezone_set('Asia/Seoul');

section('parsing');
p('strtotime("2024-02-30")', function () { return date('Y-m-d', strtotime('2024-02-30')); });
p('strtotime("2024-13-01")', function () { return strtotime('2024-13-01'); });
p('strtotime("next monday", fixed)', function () { return date('Y-m-d', strtotime('next monday', 1704067200)); });
p('strtotime("+1 month" from Jan 31)', function () { return date('Y-m-d', strtotime('+1 month', mktime(0, 0, 0, 1, 31, 2024))); });
p('strtotime("")', function () { return strtotime(''); });
p('strtotime("0000-00-00 00:00:00")', function () { return strtotime('0000-00-00 00:00:00'); });
p('date of 0000-00-00', function () { $t = strtotime('0000-00-00 00:00:00'); return $t === false ? false : date('Y-m-d', $t); });
p('strtotime("garbage")', function () { return strtotime('garbage'); });
p('strtotime("1 hour ago" style)', function () { return strtotime('-1 hour', 1704067200); });
p('mktime month 13', function () { return date('Y-m-d', mktime(0, 0, 0, 13, 1, 2024)); });
p('mktime day 0', function () { return date('Y-m-d', mktime(0, 0, 0, 3, 0, 2024)); });
p('mktime 2-digit year 70', function () { return date('Y', mktime(0, 0, 0, 1, 1, 70)); });
p('mktime 2-digit year 69', function () { return date('Y', mktime(0, 0, 0, 1, 1, 69)); });
p('checkdate(2,30,2024)', function () { return checkdate(2, 30, 2024); });
p('DateTime 2024-02-30', function () { $d = new DateTime('2024-02-30'); return $d->format('Y-m-d'); });
p('DateTime invalid', function () { $d = new DateTime('garbage'); return $d->format('Y-m-d'); });
p('date_create invalid', function () { return date_create('garbage'); });

section('formatting');
p('date("D, d M Y H:i:s O", 0)', function () { return date('D, d M Y H:i:s O', 0); });
p('date("N jS z t L W")', function () { return date('N jS z t L W', 1704067200); });
p('date("W") year boundary', function () { return date('o-W', mktime(0, 0, 0, 12, 30, 2024)); });
p('gmdate vs date', function () { return gmdate('H', 0) . '/' . date('H', 0); });
p('DST transition America/New_York', function () { date_default_timezone_set('America/New_York'); $r = date('Y-m-d H:i', mktime(2, 30, 0, 3, 10, 2024)); date_default_timezone_set('Asia/Seoul'); return $r; });

section('default timezone');
p('ini date.timezone (CLI default)', function () { return ini_get('date.timezone'); });
