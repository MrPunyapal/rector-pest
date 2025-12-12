# 24 Rules Overview

## ChainExpectCallsRector

Chains multiple `expect()` calls on the same value into a single chained expectation

- class: [`MrPunyapal\RectorPest\Rules\ChainExpectCallsRector`](../src/Rules/ChainExpectCallsRector.php)

```diff
-expect($a)->toBe(10);
-expect($a)->toBeInt();
+expect($a)->toBe(10)->toBeInt();
```

<br>

```diff
-expect($a)->toBe(10);
-expect($b)->toBe(10);
+expect($a)->toBe(10)->and($b)->toBe(10);
```

<br>

```diff
-expect($a)->toBe(10);
-expect($a)->toBeInt();
-expect($b)->toBe(10);
-expect($b)->toBeInt();
+expect($a)->toBe(10)->toBeInt()->and($b)->toBe(10)->toBeInt();
```

<br>

## RemoveOnlyRector

Removes `only()` from all tests

- class: [`MrPunyapal\RectorPest\Rules\RemoveOnlyRector`](../src/Rules/RemoveOnlyRector.php)

```diff
-test()->only();
+test();
```

<br>

## SimplifyComparisonExpectationsRector

Converts expect($x > `10)->toBeTrue()` to expect($x)->toBeGreaterThan(10)

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

## SimplifyExpectNotRector

Simplifies negated expectations by flipping the matcher (e.g., `expect(!$x)->toBeTrue()` becomes `expect($x)->toBeFalse())`

- class: [`MrPunyapal\RectorPest\Rules\SimplifyExpectNotRector`](../src/Rules/SimplifyExpectNotRector.php)

```diff
-expect(!$condition)->toBeTrue();
-expect(!$value)->toBeFalse();
+expect($condition)->toBeFalse();
+expect($value)->toBeTrue();
```

<br>

## SimplifyToLiteralBooleanRector

Simplifies expect($x)->toBe(true) to `expect($x)->toBeTrue()` and similar patterns

- class: [`MrPunyapal\RectorPest\Rules\SimplifyToLiteralBooleanRector`](../src/Rules/SimplifyToLiteralBooleanRector.php)

```diff
-expect($value)->toBe(true);
-expect($value)->toBe(false);
-expect($value)->toBe(null);
-expect($value)->toEqual([]);
-expect($value)->toBe('');
+expect($value)->toBeTrue();
+expect($value)->toBeFalse();
+expect($value)->toBeNull();
+expect($value)->toBeEmpty();
+expect($value)->toBeEmpty();
```

<br>

## TapToDeferRector

Replaces deprecated `->tap()` method with `->defer()` for Pest v3 migration

- class: [`MrPunyapal\RectorPest\Rules\Pest2ToPest3\TapToDeferRector`](../src/Rules/Pest2ToPest3/TapToDeferRector.php)

```diff
-expect($value)->tap(fn ($value) => dump($value))->toBe(10);
+expect($value)->defer(fn ($value) => dump($value))->toBe(10);
```

<br>

## ToBeTrueNotFalseRector

Simplifies double-negative expectations like `->not->toBeFalse()` to `->toBeTrue()`

- class: [`MrPunyapal\RectorPest\Rules\ToBeTrueNotFalseRector`](../src/Rules/ToBeTrueNotFalseRector.php)

```diff
-expect($value)->not->toBeFalse();
-expect($value)->not->toBeTrue();
+expect($value)->toBeTrue();
+expect($value)->toBeFalse();
```

<br>

## ToHaveMethodOnClassRector

Changes `expect($object)->toHaveMethod()` to `expect($object::class)->toHaveMethod()` for Pest v3

- class: [`MrPunyapal\RectorPest\Rules\Pest2ToPest3\ToHaveMethodOnClassRector`](../src/Rules/Pest2ToPest3/ToHaveMethodOnClassRector.php)

```diff
-expect($user)->toHaveMethod('getName');
-expect($user)->toHaveMethods(['getName', 'getEmail']);
+expect($user::class)->toHaveMethod('getName');
+expect($user::class)->toHaveMethods(['getName', 'getEmail']);
```

<br>

## UseEachModifierRector

Converts foreach loops with `expect()` calls to use the ->each modifier

- class: [`MrPunyapal\RectorPest\Rules\UseEachModifierRector`](../src/Rules/UseEachModifierRector.php)

```diff
-foreach ($items as $item) {
-    expect($item)->toBeString();
-}
+expect($items)->each->toBeString();
```

<br>

## UseInstanceOfMatcherRector

Converts expect($obj instanceof `User)->toBeTrue()` to expect($obj)->toBeInstanceOf(User::class)

- class: [`MrPunyapal\RectorPest\Rules\UseInstanceOfMatcherRector`](../src/Rules/UseInstanceOfMatcherRector.php)

```diff
-expect($user instanceof User)->toBeTrue();
-expect($object instanceof DateTime)->toBeTrue();
+expect($user)->toBeInstanceOf(User::class);
+expect($object)->toBeInstanceOf(DateTime::class);
```

<br>

## UseStrictEqualityMatchersRector

Converts strict equality expressions to `toBe()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseStrictEqualityMatchersRector`](../src/Rules/UseStrictEqualityMatchersRector.php)

```diff
-expect($a === $b)->toBeTrue();
-expect($value === 'expected')->toBeTrue();
-expect($a !== $b)->toBeTrue();
+expect($a)->toBe($b);
+expect($value)->toBe('expected');
+expect($a)->not->toBe($b);
```

<br>

## UseToBeDirectoryRector

Converts `is_dir()` checks to `toBeDirectory()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToBeDirectoryRector`](../src/Rules/UseToBeDirectoryRector.php)

```diff
-expect(is_dir($path))->toBeTrue();
-expect(is_dir('/tmp'))->toBeTrue();
+expect($path)->toBeDirectory();
+expect('/tmp')->toBeDirectory();
```

<br>

## UseToBeFileRector

Converts `is_file()` checks to `toBeFile()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToBeFileRector`](../src/Rules/UseToBeFileRector.php)

```diff
-expect(is_file($path))->toBeTrue();
-expect(is_file('/tmp/file.txt'))->toBeTrue();
+expect($path)->toBeFile();
+expect('/tmp/file.txt')->toBeFile();
```

<br>

## UseToBeJsonRector

Converts `json_decode()` null checks to `toBeJson()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToBeJsonRector`](../src/Rules/UseToBeJsonRector.php)

```diff
-expect(json_decode($string) !== null)->toBeTrue();
-expect(json_decode($json) === null)->toBeFalse();
+expect($string)->toBeJson();
+expect($json)->toBeJson();
```

<br>

## UseToBeReadableWritableRector

Converts `is_readable()/is_writable()` checks to `toBeReadable()/toBeWritable()` matchers

- class: [`MrPunyapal\RectorPest\Rules\UseToBeReadableWritableRector`](../src/Rules/UseToBeReadableWritableRector.php)

```diff
-expect(is_readable($path))->toBeTrue();
-expect(is_writable($file))->toBeTrue();
+expect($path)->toBeReadable();
+expect($file)->toBeWritable();
```

<br>

## UseToContainRector

Converts `in_array()` checks to `toContain()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToContainRector`](../src/Rules/UseToContainRector.php)

```diff
-expect(in_array($item, $array))->toBeTrue();
-expect(in_array($item, $array, true))->toBeTrue();
+expect($array)->toContain($item);
+expect($array)->toContain($item);
```

<br>

## UseToEndWithRector

Converts `str_ends_with()` checks to `toEndWith()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToEndWithRector`](../src/Rules/UseToEndWithRector.php)

```diff
-expect(str_ends_with($string, 'World'))->toBeTrue();
-expect(str_ends_with($text, $suffix))->toBeTrue();
+expect($string)->toEndWith('World');
+expect($text)->toEndWith($suffix);
```

<br>

## UseToHaveCountRector

Converts expect(count($arr))->toBe(5) to expect($arr)->toHaveCount(5)

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveCountRector`](../src/Rules/UseToHaveCountRector.php)

```diff
-expect(count($array))->toBe(5);
-expect(count($items))->toEqual(3);
+expect($array)->toHaveCount(5);
+expect($items)->toHaveCount(3);
```

<br>

## UseToHaveKeyRector

Converts `array_key_exists()` checks to `toHaveKey()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveKeyRector`](../src/Rules/UseToHaveKeyRector.php)

```diff
-expect(array_key_exists('id', $array))->toBeTrue();
-expect(array_key_exists($key, $data))->toBeTrue();
+expect($array)->toHaveKey('id');
+expect($data)->toHaveKey($key);
```

<br>

## UseToHaveLengthRector

Converts `strlen()/mb_strlen()` comparisons to `toHaveLength()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToHaveLengthRector`](../src/Rules/UseToHaveLengthRector.php)

```diff
-expect(strlen($string))->toBe(10);
-expect(mb_strlen($text))->toBe(5);
+expect($string)->toHaveLength(10);
+expect($text)->toHaveLength(5);
```

<br>

## UseToHavePropertyRector

Converts `property_exists()` checks to `toHaveProperty()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToHavePropertyRector`](../src/Rules/UseToHavePropertyRector.php)

```diff
-expect(property_exists($object, 'name'))->toBeTrue();
-expect(property_exists($user, 'email'))->toBeTrue();
+expect($object)->toHaveProperty('name');
+expect($user)->toHaveProperty('email');
```

<br>

## UseToMatchRector

Converts expect(preg_match("/pattern/", `$str))->toBe(1)` to expect($str)->toMatch("/pattern/")

- class: [`MrPunyapal\RectorPest\Rules\UseToMatchRector`](../src/Rules/UseToMatchRector.php)

```diff
-expect(preg_match('/pattern/', $string))->toBe(1);
-expect(preg_match('/^hello/', $text))->toEqual(1);
+expect($string)->toMatch('/pattern/');
+expect($text)->toMatch('/^hello/');
```

<br>

## UseToStartWithRector

Converts `str_starts_with()` checks to `toStartWith()` matcher

- class: [`MrPunyapal\RectorPest\Rules\UseToStartWithRector`](../src/Rules/UseToStartWithRector.php)

```diff
-expect(str_starts_with($string, 'Hello'))->toBeTrue();
-expect(str_starts_with($text, $prefix))->toBeTrue();
+expect($string)->toStartWith('Hello');
+expect($text)->toStartWith($prefix);
```

<br>

## UseTypeMatchersRector

Converts `expect(is_array($x))->toBeTrue()` to `expect($x)->toBeArray()`

- class: [`MrPunyapal\RectorPest\Rules\UseTypeMatchersRector`](../src/Rules/UseTypeMatchersRector.php)

```diff
-expect(is_array($value))->toBeTrue();
-expect(is_string($value))->toBeTrue();
-expect(is_int($value))->toBeTrue();
-expect(is_bool($value))->toBeTrue();
+expect($value)->toBeArray();
+expect($value)->toBeString();
+expect($value)->toBeInt();
+expect($value)->toBeBool();
```

<br>
