# 21 Rules Overview

## ChainExpectCallsRector

Chains multiple `expect()` calls on the same value into a single chained call.

- class: [`MrPunyapal\RectorPest\Rules\ChainExpectCallsRector`](../src/Rules/ChainExpectCallsRector.php)

```diff
-expect($value)->toBe(10);
-expect($value)->toBeInt();
+expect($value)->toBe(10)->toBeInt();
```

<br>

## SimplifyExpectNotRector

Converts `expect(!$x)->toBeTrue()` to `expect($x)->toBeFalse()` by flipping the matcher.

- class: [`MrPunyapal\RectorPest\Rules\SimplifyExpectNotRector`](../src/Rules/SimplifyExpectNotRector.php)

```diff
-expect(!$condition)->toBeTrue();
-expect(!$value)->toBeFalse();
+expect($condition)->toBeFalse();
+expect($value)->toBeTrue();
```

<br>

## ToBeTrueNotFalseRector

Simplifies `->not->toBeFalse()` to `->toBeTrue()` and vice versa.

- class: [`MrPunyapal\RectorPest\Rules\ToBeTrueNotFalseRector`](../src/Rules/ToBeTrueNotFalseRector.php)

```diff
-expect($value)->not->toBeFalse();
+expect($value)->toBeTrue();
```

<br>

## UseEachModifierRector

Converts `foreach` loops with `expect()` to use the `->each` modifier.

- class: [`MrPunyapal\RectorPest\Rules\UseEachModifierRector`](../src/Rules/UseEachModifierRector.php)

```diff
-foreach ($items as $item) {
-    expect($item)->toBeString();
-}
+expect($items)->each->toBeString();
```

<br>

## SimplifyToLiteralBooleanRector

Converts `->toBe(true)` to `->toBeTrue()` and similar literal comparisons.

- class: [`MrPunyapal\RectorPest\Rules\SimplifyToLiteralBooleanRector`](../src/Rules/SimplifyToLiteralBooleanRector.php)

```diff
-expect($value)->toBe(true);
-expect($value)->toBe(false);
-expect($value)->toBe(null);
-expect($array)->toEqual([]);
+expect($value)->toBeTrue();
+expect($value)->toBeFalse();
+expect($value)->toBeNull();
+expect($array)->toBeEmpty();
```

<br>

## UseTypeMatchersRector

Converts `is_*()` function calls to appropriate type matchers.

- class: [`MrPunyapal\RectorPest\Rules\UseTypeMatchersRector`](../src/Rules/UseTypeMatchersRector.php)

```diff
-expect(is_array($value))->toBeTrue();
-expect(is_string($value))->toBeTrue();
+expect($value)->toBeArray();
+expect($value)->toBeString();
```

<br>

## UseToHaveCountRector

Converts `expect(count($arr))->toBe(n)` to `expect($arr)->toHaveCount(n)`.

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveCountRector`](../src/Rules/UseToHaveCountRector.php)

```diff
-expect(count($array))->toBe(5);
+expect($array)->toHaveCount(5);
```

<br>

## UseInstanceOfMatcherRector

Converts `instanceof` checks to `->toBeInstanceOf()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseInstanceOfMatcherRector`](../src/Rules/UseInstanceOfMatcherRector.php)

```diff
-expect($user instanceof User)->toBeTrue();
+expect($user)->toBeInstanceOf(User::class);
```

<br>

## SimplifyComparisonExpectationsRector

Converts comparison expressions to appropriate matchers.

- class: [`MrPunyapal\RectorPest\Rules\SimplifyComparisonExpectationsRector`](../src/Rules/SimplifyComparisonExpectationsRector.php)

```diff
-expect($value > 10)->toBeTrue();
-expect($value >= 10)->toBeTrue();
-expect($value < 5)->toBeTrue();
-expect($value <= 5)->toBeTrue();
+expect($value)->toBeGreaterThan(10);
+expect($value)->toBeGreaterThanOrEqual(10);
+expect($value)->toBeLessThan(5);
+expect($value)->toBeLessThanOrEqual(5);
```

<br>

## UseStrictEqualityMatchersRector

Converts strict equality expressions to appropriate matchers.

- class: [`MrPunyapal\RectorPest\Rules\UseStrictEqualityMatchersRector`](../src/Rules/UseStrictEqualityMatchersRector.php)

```diff
-expect($a === $b)->toBeTrue();
-expect($a !== $b)->toBeTrue();
+expect($a)->toBe($b);
+expect($a)->not->toBe($b);
```

<br>

## UseToContainRector

Converts `in_array()` checks to `->toContain()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToContainRector`](../src/Rules/UseToContainRector.php)

```diff
-expect(in_array($item, $array))->toBeTrue();
+expect($array)->toContain($item);
```

<br>

## UseToHaveKeyRector

Converts `array_key_exists()` checks to `->toHaveKey()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveKeyRector`](../src/Rules/UseToHaveKeyRector.php)

```diff
-expect(array_key_exists('id', $array))->toBeTrue();
+expect($array)->toHaveKey('id');
```

<br>

## UseToStartWithRector

Converts `str_starts_with()` checks to `->toStartWith()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToStartWithRector`](../src/Rules/UseToStartWithRector.php)

```diff
-expect(str_starts_with($string, 'Hello'))->toBeTrue();
+expect($string)->toStartWith('Hello');
```

<br>

## UseToEndWithRector

Converts `str_ends_with()` checks to `->toEndWith()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToEndWithRector`](../src/Rules/UseToEndWithRector.php)

```diff
-expect(str_ends_with($filename, '.php'))->toBeTrue();
+expect($filename)->toEndWith('.php');
```

<br>

## UseToHaveLengthRector

Converts `strlen()` / `mb_strlen()` checks to `->toHaveLength()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveLengthRector`](../src/Rules/UseToHaveLengthRector.php)

```diff
-expect(strlen($string))->toBe(10);
+expect($string)->toHaveLength(10);
```

<br>

## UseToMatchRector

Converts `preg_match()` checks to `->toMatch()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToMatchRector`](../src/Rules/UseToMatchRector.php)

```diff
-expect(preg_match('/pattern/', $string))->toBe(1);
+expect($string)->toMatch('/pattern/');
```

<br>

## UseToBeJsonRector

Converts `json_decode() !== null` checks to `->toBeJson()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToBeJsonRector`](../src/Rules/UseToBeJsonRector.php)

```diff
-expect(json_decode($string) !== null)->toBeTrue();
+expect($string)->toBeJson();
```

<br>

## UseToBeFileRector

Converts `is_file()` checks to `->toBeFile()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToBeFileRector`](../src/Rules/UseToBeFileRector.php)

```diff
-expect(is_file($path))->toBeTrue();
+expect($path)->toBeFile();
```

<br>

## UseToBeDirectoryRector

Converts `is_dir()` checks to `->toBeDirectory()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToBeDirectoryRector`](../src/Rules/UseToBeDirectoryRector.php)

```diff
-expect(is_dir($path))->toBeTrue();
+expect($path)->toBeDirectory();
```

<br>

## UseToBeReadableWritableRector

Converts `is_readable()` / `is_writable()` checks to `->toBeReadable()` / `->toBeWritable()` matchers.

- class: [`MrPunyapal\RectorPest\Rules\UseToBeReadableWritableRector`](../src/Rules/UseToBeReadableWritableRector.php)

```diff
-expect(is_readable($path))->toBeTrue();
-expect(is_writable($file))->toBeTrue();
+expect($path)->toBeReadable();
+expect($file)->toBeWritable();
```

<br>

## UseToHavePropertyRector

Converts `property_exists()` checks to `->toHaveProperty()` matcher.

- class: [`MrPunyapal\RectorPest\Rules\UseToHavePropertyRector`](../src/Rules/UseToHavePropertyRector.php)

```diff
-expect(property_exists($object, 'name'))->toBeTrue();
+expect($object)->toHaveProperty('name');
```

<br>
