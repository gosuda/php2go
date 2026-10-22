# Dates and Times

PHP date functions use the default time zone and a lenient parser. Go's `time.Parse` requires exact layouts.

## Default Time Zone

**Observed:** With `-n`, `date.timezone` was empty on 5.6 and 7.4 and `UTC` on 8.4 (Debian build). **Documented:** PHP 5.x raises an E_WARNING when `date.timezone` is unset and then falls back (5.4+ uses UTC); 7.0+ falls back to UTC without the warning. The effective zone usually comes from `php.ini`, `date_default_timezone_set()` in a bootstrap file, or `.htaccess` `php_value`.

**Go:** Load the reference zone explicitly with `time.LoadLocation`, and embed `time/tzdata` or pin the system tzdata version. Do not rely on `time.Local` in containers.

## Lenient Parsing

**Observed (Asia/Seoul):**

- `strtotime("2024-02-30")` gives 2024-03-01 (overflow normalization). `new DateTime("2024-02-30")` does the same. Go's `time.Parse("2006-01-02", "2024-02-30")` returns an error. `time.Date(2024, 2, 30, ...)` normalizes to 2024-03-01.
- `strtotime("2024-13-01")`, `strtotime("")`, and `strtotime("garbage")` return `false`. `date('Y-m-d', false)` then formats the epoch (1970-01-01 in the local zone).
- `strtotime("0000-00-00 00:00:00")` returns `-62170014472` (Asia/Seoul), and `date('Y-m-d')` of it gives `-0001-11-30`. MySQL zero dates reach PHP as this string.
- `strtotime("+1 month", <Jan 31>)` gives March 2 in 2024. Go's `AddDate(0, 1, 0)` gives the same result.
- Relative formats (`next monday`, `last day of next month`, `-1 hour`, `tomorrow`) have no Go standard-library equivalent.
- `new DateTime("garbage")` throws `Exception` before 8.3 and `DateMalformedStringException` on 8.3+. `date_create("garbage")` returns `false`.

## `mktime` and Formatting

**Observed:**

- `mktime` normalizes out-of-range values: month 13 gives January of the next year, and day 0 gives the last day of the previous month.
- Two-digit years 0-69 map to 2000-2069 and 70-100 map to 1970-2000.
- `date('o-W')` uses ISO-8601 week-year (2024-12-30 is week `2025-01`). Go's `ISOWeek()` agrees, but Go's layout strings have no `W`, `N`, `jS`, `z`, `t`, or `L` equivalents.
- A nonexistent local time during a DST transition shifts forward (`02:30` on 2024-03-10 in America/New_York gave `03:30`). Go's `time.Date` also normalizes nonexistent times but documents the result as unspecified for ambiguous times. Korea has no DST now, but historical dates (1987-1988) do.

**Find:** `date(`, `gmdate(`, `mktime(`, `strtotime(`, `checkdate(`, `DateTime`, `date_default_timezone_set(`, `time()` in SQL strings, `NOW()` in SQL, and `0000-00-00` checks.

**Go:** Write a `phpDate(format, t)` formatter for the format characters in use and test it against PHP output. Port only the `strtotime` forms that the application uses, and list them in the contract. Inject the clock for both implementations as required by the fixture rules.
