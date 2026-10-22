# Arrays

A PHP array is an ordered hash map with integer and string keys. Iteration follows insertion order. Go maps iterate in random order, and Go slices cannot represent sparse or mixed keys. Choose a Go representation per use instead of using `map[string]any` everywhere.

## Key Normalization

**Observed (all versions unless noted):**

- Decimal integer strings become integer keys: `"1"` becomes `1` and `"-5"` becomes `-5`. `"01"`, `"-0"`, `"1.5"`, `" 1"`, and integer strings outside the platform range (`"9223372036854775808"`) stay strings.
- Float keys truncate (`1.7` becomes `1`; 8.1+ adds E_DEPRECATED for fractional floats). `true` becomes `1`, and `null` becomes `""`.
- `array_keys()` returns integers for normalized keys, so `array_keys(["1" => 'a'])[0] === 1`.

This affects request data: `?a[1]=x` and `?a[01]=x` produce different keys. Rows fetched with numeric-looking string values used as keys turn into integers.

**Go:** Model normalized keys explicitly, for example `type phpKey struct { isInt bool; i int64; s string }`. Apply the same canonicalization when building lookup tables from request or database data.

## Ordering and Append

**Observed:**

- Iteration follows insertion order regardless of key type: `$a['b']=1; $a['a']=2; $a[5]=3; $a[]=4` iterates b, a, 5, 6.
- Append uses the largest integer key ever used plus one. Unsetting the last element does not reset it (`[1,2,3]`, `unset($a[2])`, `$a[]='x'` gives key 3).
- When the only integer key is negative, 5.6 and 7.4 append at `0`, and 8.3+ append at the next integer (`-4` after `-5`).
- `array_merge` renumbers integer keys and overwrites string keys. `+` keeps the left operand's keys. `array_filter`, `array_unique`, and `unset` keep the original keys, and later `json_encode` then emits an object. See [numbers-json.md](numbers-json.md).

**Go:** Use an ordered map type (keys slice plus map) when order or sparse keys are observable. Use a slice only when the PHP code guarantees list semantics (`array_values`, or append-only lists that are never unset).

## Sorting

**Observed:**

- `sort` on mixed types depends on the version's comparison rules. `["10", 9, "9a", "abc", 1.5, true]` sorted into three different orders on 5.6, 7.4, and 8.x.
- `sort(["10", "9", "1e1", "010"])` produced `9, 010, 1e1, 10` on 5.6 and `9, 10, 1e1, 010` on 7.4 and 8.x. Equal numeric strings keep an unspecified relative order before 8.0.
- Sorting is guaranteed stable from 8.0 onward and not guaranteed before 8.0. A four-element `usort` with ties produced `dbac` on 5.6 and `bdac` on 7.4 and 8.x. Ties such as same-date posts can reorder between versions or input sizes.
- `ksort` with mixed integer and string keys ordered `1a, a, b, 2, 10` on 5.6 and 7.4 and `2, 10, 1a, a, b` on 8.4.
- A boolean comparator (`return $a > $b;`) works but is E_DEPRECATED in 8.x.
- `sort` flags (`SORT_STRING`, `SORT_NUMERIC`, `SORT_NATURAL`, `SORT_FLAG_CASE`) and `setlocale` with `SORT_LOCALE_STRING` change results.

**Find:** `sort(`, `rsort(`, `usort(`, `uasort(`, `uksort(`, `ksort(`, `krsort(`, `asort(`, `arsort(`, `array_multisort(`, `natsort(`.

**Go:** Use `sort.SliceStable` for PHP 8 targets. For PHP 5 and 7 targets with ties, capture the observed order as a fixture and make tie-breaking explicit in the contract. Sort SQL results with `ORDER BY` columns that break ties only after recording the original behavior.

## Copy and Reference Semantics

**Observed:**

- Arrays are values: `$b = $a; $b[] = 2;` leaves `$a` unchanged. Go slices and maps alias.
- After `foreach ($a as &$v)`, `$v` remains a reference to the last element. A later `foreach ($a as $v)` overwrites it: `[1,2,3]` becomes `[1,2,2]`.
- A reference stored inside an array survives copying: `$a = [&$x]; $b = $a; $b[0] = 9;` changes `$a[0]` too.

**Find:** `&$` in `foreach`, function signatures, and array literals; `$GLOBALS`; `static` variables; `global`.

**Go:** Copy slices and maps explicitly where PHP code mutates a copy. Reproduce `foreach` reference bugs only where they are observable in output.
