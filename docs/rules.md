# 38 Rules Overview

## ChainExpectCallsRector

Chains multiple `expect()` calls on the same value into a single chained expectation

- class: [`RectorPest\Rules\ChainExpectCallsRector`](../src/Rules/ChainExpectCallsRector.php)

```diff
-expect($a)->toBe(10);
-expect($a)->toBeInt();
+expect($a)->toBe(10)
+    ->toBeInt();
```

<br>

```diff
-expect($a)->toBe(10);
-expect($b)->toBe(10);
+expect($a)->toBe(10)
+    ->and($b)
+    ->toBe(10);
```

<br>

```diff
-expect($a)->toBe(10);
-expect($a)->toBeInt();
-expect($b)->toBe(10);
-expect($b)->toBeInt();
+expect($a)->toBe(10)
+    ->toBeInt()
+    ->and($b)
+    ->toBe(10)
+    ->toBeInt();
```

<br>

## EnsureTypeChecksFirstRector

Ensure type-check matchers (e.g. toBeInt, toBeInstanceOf) appear before value assertions in `expect()` chains and consecutive expects

- class: [`RectorPest\Rules\EnsureTypeChecksFirstRector`](../src/Rules/EnsureTypeChecksFirstRector.php)

```diff
-expect($a)->toBe(10)->toBeInt();
+expect($a)->toBeInt()->toBe(10);
```

<br>

```diff
-expect($a)->toBe(10);
-expect($a)->toBeInt();
+expect($a)->toBeInt();
+expect($a)->toBe(10);
```

<br>

## RemoveOnlyRector

Removes `only()` from all tests

- class: [`RectorPest\Rules\RemoveOnlyRector`](../src/Rules/RemoveOnlyRector.php)

```diff
-test()->only();
+test();
```

<br>

## SimplifyComparisonExpectationsRector

Converts expect($x > `10)->toBeTrue()` to expect($x)->toBeGreaterThan(10)

- class: [`RectorPest\Rules\SimplifyComparisonExpectationsRector`](../src/Rules/SimplifyComparisonExpectationsRector.php)

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

- class: [`RectorPest\Rules\SimplifyExpectNotRector`](../src/Rules/SimplifyExpectNotRector.php)

```diff
-expect(!$condition)->toBeTrue();
-expect(!$value)->toBeFalse();
+expect($condition)->toBeFalse();
+expect($value)->toBeTrue();
```

<br>

## SimplifyToLiteralBooleanRector

Simplifies expect($x)->toBe(true) to `expect($x)->toBeTrue()` and similar patterns

- class: [`RectorPest\Rules\SimplifyToLiteralBooleanRector`](../src/Rules/SimplifyToLiteralBooleanRector.php)

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

- class: [`RectorPest\Rules\Pest2ToPest3\TapToDeferRector`](../src/Rules/Pest2ToPest3/TapToDeferRector.php)

```diff
-expect($value)->tap(fn ($value) => dump($value))->toBe(10);
+expect($value)->defer(fn ($value) => dump($value))->toBe(10);
```

<br>

## ToBeTrueNotFalseRector

Simplifies double-negative expectations like `->not->toBeFalse()` to `->toBeTrue()`

- class: [`RectorPest\Rules\ToBeTrueNotFalseRector`](../src/Rules/ToBeTrueNotFalseRector.php)

```diff
-expect($value)->not->toBeFalse();
-expect($value)->not->toBeTrue();
+expect($value)->toBeTrue();
+expect($value)->toBeFalse();
```

<br>

## ToHaveMethodOnClassRector

Changes `expect($object)->toHaveMethod()` to `expect($object::class)->toHaveMethod()` for Pest v3

- class: [`RectorPest\Rules\Pest2ToPest3\ToHaveMethodOnClassRector`](../src/Rules/Pest2ToPest3/ToHaveMethodOnClassRector.php)

```diff
-expect($user)->toHaveMethod('getName');
-expect($user)->toHaveMethods(['getName', 'getEmail']);
+expect($user::class)->toHaveMethod('getName');
+expect($user::class)->toHaveMethods(['getName', 'getEmail']);
```

<br>

## UseEachModifierRector

Converts foreach loops with `expect()` calls to use the ->each modifier

- class: [`RectorPest\Rules\UseEachModifierRector`](../src/Rules/UseEachModifierRector.php)

```diff
-foreach ($items as $item) {
-    expect($item)->toBeString();
-}
+expect($items)->each->toBeString();
```

<br>

## UseInstanceOfMatcherRector

Converts expect($obj instanceof `User)->toBeTrue()` to expect($obj)->toBeInstanceOf(User::class)

- class: [`RectorPest\Rules\UseInstanceOfMatcherRector`](../src/Rules/UseInstanceOfMatcherRector.php)

```diff
-expect($user instanceof User)->toBeTrue();
-expect($object instanceof DateTime)->toBeTrue();
+expect($user)->toBeInstanceOf(User::class);
+expect($object)->toBeInstanceOf(DateTime::class);
```

<br>

## UseStrictEqualityMatchersRector

Converts strict equality expressions to `toBe()` matcher

- class: [`RectorPest\Rules\UseStrictEqualityMatchersRector`](../src/Rules/UseStrictEqualityMatchersRector.php)

```diff
-expect($a === $b)->toBeTrue();
-expect($value === 'expected')->toBeTrue();
-expect($a !== $b)->toBeTrue();
+expect($a)->toBe($b);
+expect($value)->toBe('expected');
+expect($a)->not->toBe($b);
```

<br>

## UseToBeAlphaNumericRector

Converts `ctype_alnum()` checks to `toBeAlphaNumeric()` matcher

- class: [`RectorPest\Rules\UseToBeAlphaNumericRector`](../src/Rules/UseToBeAlphaNumericRector.php)

```diff
-expect(ctype_alnum($value))->toBeTrue();
+expect($value)->toBeAlphaNumeric();
```

<br>

## UseToBeAlphaRector

Converts `ctype_alpha()` checks to `toBeAlpha()` matcher

- class: [`RectorPest\Rules\UseToBeAlphaRector`](../src/Rules/UseToBeAlphaRector.php)

```diff
-expect(ctype_alpha($value))->toBeTrue();
+expect($value)->toBeAlpha();
```

<br>

## UseToBeBetweenRector

Converts expect($value >= `$min` && `$value` <= `$max)->toBeTrue()` to expect($value)->toBeBetween($min, `$max)`

- class: [`RectorPest\Rules\UseToBeBetweenRector`](../src/Rules/UseToBeBetweenRector.php)

```diff
-expect($value >= 1 && $value <= 10)->toBeTrue();
-expect($age >= 18 && $age <= 65)->toBeTrue();
+expect($value)->toBeBetween(1, 10);
+expect($age)->toBeBetween(18, 65);
```

<br>

## UseToBeDirectoryRector

Converts `is_dir()` checks to `toBeDirectory()` matcher

- class: [`RectorPest\Rules\UseToBeDirectoryRector`](../src/Rules/UseToBeDirectoryRector.php)

```diff
-expect(is_dir($path))->toBeTrue();
-expect(is_dir('/tmp'))->toBeTrue();
+expect($path)->toBeDirectory();
+expect('/tmp')->toBeDirectory();
```

<br>

## UseToBeFileRector

Converts `is_file()` checks to `toBeFile()` matcher

- class: [`RectorPest\Rules\UseToBeFileRector`](../src/Rules/UseToBeFileRector.php)

```diff
-expect(is_file($path))->toBeTrue();
-expect(is_file('/tmp/file.txt'))->toBeTrue();
+expect($path)->toBeFile();
+expect('/tmp/file.txt')->toBeFile();
```

<br>

## UseToBeJsonRector

Converts `json_decode()` null checks to `toBeJson()` matcher

- class: [`RectorPest\Rules\UseToBeJsonRector`](../src/Rules/UseToBeJsonRector.php)

```diff
-expect(json_decode($string) !== null)->toBeTrue();
-expect(json_decode($json) === null)->toBeFalse();
+expect($string)->toBeJson();
+expect($json)->toBeJson();
```

<br>

## UseToBeLowercaseRector

Converts `strtolower()` equality checks to `toBeLowercase()` matcher

- class: [`RectorPest\Rules\UseToBeLowercaseRector`](../src/Rules/UseToBeLowercaseRector.php)

```diff
-expect(strtolower($value) === $value)->toBeTrue();
-expect($value === strtolower($value))->toBeTrue();
+expect($value)->toBeLowercase();
+expect($value)->toBeLowercase();
```

<br>

## UseToBeReadableWritableRector

Converts `is_readable()/is_writable()` checks to `toBeReadable()/toBeWritable()` matchers

- class: [`RectorPest\Rules\UseToBeReadableWritableRector`](../src/Rules/UseToBeReadableWritableRector.php)

```diff
-expect(is_readable($path))->toBeTrue();
-expect(is_writable($file))->toBeTrue();
+expect($path)->toBeReadable();
+expect($file)->toBeWritable();
```

<br>

## UseToBeUppercaseRector

Converts `strtoupper()` equality checks to `toBeUppercase()` matcher

- class: [`RectorPest\Rules\UseToBeUppercaseRector`](../src/Rules/UseToBeUppercaseRector.php)

```diff
-expect(strtoupper($value) === $value)->toBeTrue();
-expect($value === strtoupper($value))->toBeTrue();
+expect($value)->toBeUppercase();
+expect($value)->toBeUppercase();
```

<br>

## UseToBeUrlRector

Converts filter_var($url, FILTER_VALIDATE_URL) checks to `toBeUrl()` matcher

- class: [`RectorPest\Rules\UseToBeUrlRector`](../src/Rules/UseToBeUrlRector.php)

```diff
-expect(filter_var($url, FILTER_VALIDATE_URL))->not->toBeFalse();
-expect(filter_var($url, FILTER_VALIDATE_URL) !== false)->toBeTrue();
+expect($url)->toBeUrl();
+expect($url)->toBeUrl();
```

<br>

## UseToBeUuidRector

Converts UUID regex validation to `toBeUuid()` matcher

- class: [`RectorPest\Rules\UseToBeUuidRector`](../src/Rules/UseToBeUuidRector.php)

```diff
-expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value))->toBe(1);
-expect(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid))->toBeGreaterThan(0);
+expect($value)->toBeUuid();
+expect($uuid)->toBeUuid();
```

<br>

## UseToContainOnlyInstancesOfRector

Converts `->each->toBeInstanceOf()` pattern to `toContainOnlyInstancesOf()` matcher

- class: [`RectorPest\Rules\UseToContainOnlyInstancesOfRector`](../src/Rules/UseToContainOnlyInstancesOfRector.php)

```diff
-expect($items)->each->toBeInstanceOf(User::class);
+expect($items)->toContainOnlyInstancesOf(User::class);
```

<br>

## UseToContainRector

Converts `in_array()` checks to `toContain()` matcher

- class: [`RectorPest\Rules\UseToContainRector`](../src/Rules/UseToContainRector.php)

```diff
-expect(in_array($item, $array))->toBeTrue();
-expect(in_array($item, $array, true))->toBeTrue();
+expect($array)->toContain($item);
+expect($array)->toContain($item);
```

<br>

## UseToEndWithRector

Converts `str_ends_with()` checks to `toEndWith()` matcher

- class: [`RectorPest\Rules\UseToEndWithRector`](../src/Rules/UseToEndWithRector.php)

```diff
-expect(str_ends_with($string, 'World'))->toBeTrue();
-expect(str_ends_with($text, $suffix))->toBeTrue();
+expect($string)->toEndWith('World');
+expect($text)->toEndWith($suffix);
```

<br>

## UseToHaveCountRector

Converts expect(count($arr))->toBe(5) to expect($arr)->toHaveCount(5)

- class: [`RectorPest\Rules\UseToHaveCountRector`](../src/Rules/UseToHaveCountRector.php)

```diff
-expect(count($array))->toBe(5);
-expect(count($items))->toEqual(3);
+expect($array)->toHaveCount(5);
+expect($items)->toHaveCount(3);
```

<br>

## UseToHaveKeyRector

Converts `array_key_exists()` checks to `toHaveKey()` matcher

- class: [`RectorPest\Rules\UseToHaveKeyRector`](../src/Rules/UseToHaveKeyRector.php)

```diff
-expect(array_key_exists('id', $array))->toBeTrue();
-expect(array_key_exists($key, $data))->toBeTrue();
+expect($array)->toHaveKey('id');
+expect($data)->toHaveKey($key);
```

<br>

## UseToHaveKeysRector

Converts chained `toHaveKey()` calls to `toHaveKeys()` with array of keys

- class: [`RectorPest\Rules\UseToHaveKeysRector`](../src/Rules/UseToHaveKeysRector.php)

```diff
-expect($array)->toHaveKey('id')->toHaveKey('name')->toHaveKey('email');
-expect($data)->toHaveKey('foo')->toHaveKey('bar');
+expect($array)->toHaveKeys(['id', 'name', 'email']);
+expect($data)->toHaveKeys(['foo', 'bar']);
```

<br>

## UseToHaveLengthRector

Converts `strlen()/mb_strlen()` comparisons to `toHaveLength()` matcher

- class: [`RectorPest\Rules\UseToHaveLengthRector`](../src/Rules/UseToHaveLengthRector.php)

```diff
-expect(strlen($string))->toBe(10);
-expect(mb_strlen($text))->toBe(5);
+expect($string)->toHaveLength(10);
+expect($text)->toHaveLength(5);
```

<br>

## UseToHavePropertiesRector

Converts chained `toHaveProperty()` calls to `toHaveProperties()` with array of properties

- class: [`RectorPest\Rules\UseToHavePropertiesRector`](../src/Rules/UseToHavePropertiesRector.php)

```diff
-expect($user)->toHaveProperty('name')->toHaveProperty('email');
-expect($object)->toHaveProperty('foo')->toHaveProperty('bar')->toHaveProperty('baz');
+expect($user)->toHaveProperties(['name', 'email']);
+expect($object)->toHaveProperties(['foo', 'bar', 'baz']);
```

<br>

## UseToHavePropertyRector

Converts `property_exists()` checks to `toHaveProperty()` matcher

- class: [`RectorPest\Rules\UseToHavePropertyRector`](../src/Rules/UseToHavePropertyRector.php)

```diff
-expect(property_exists($object, 'name'))->toBeTrue();
-expect(property_exists($user, 'email'))->toBeTrue();
+expect($object)->toHaveProperty('name');
+expect($user)->toHaveProperty('email');
```

<br>

## UseToHaveSameSizeRector

Converts expect(count($a))->toBe(count($b)) to expect($a)->toHaveSameSize($b)

- class: [`RectorPest\Rules\UseToHaveSameSizeRector`](../src/Rules/UseToHaveSameSizeRector.php)

```diff
-expect(count($array1))->toBe(count($array2));
-expect($items)->toHaveCount(count($other));
+expect($array1)->toHaveSameSize($array2);
+expect($items)->toHaveSameSize($other);
```

<br>

## UseToMatchArrayRector

Converts multiple array element assertions to `toMatchArray()` matcher

- class: [`RectorPest\Rules\UseToMatchArrayRector`](../src/Rules/UseToMatchArrayRector.php)

```diff
-expect($array['name'])->toBe('Nuno');
-expect($array['email'])->toBe('nuno@example.com');
+expect($array)->toMatchArray(['name' => 'Nuno', 'email' => 'nuno@example.com']);
```

<br>

## UseToMatchRector

Converts expect(preg_match("/pattern/", `$str))->toBe(1)` to expect($str)->toMatch("/pattern/")

- class: [`RectorPest\Rules\UseToMatchRector`](../src/Rules/UseToMatchRector.php)

```diff
-expect(preg_match('/pattern/', $string))->toBe(1);
-expect(preg_match('/^hello/', $text))->toEqual(1);
+expect($string)->toMatch('/pattern/');
+expect($text)->toMatch('/^hello/');
```

<br>

## UseToStartWithRector

Converts `str_starts_with()` checks to `toStartWith()` matcher

- class: [`RectorPest\Rules\UseToStartWithRector`](../src/Rules/UseToStartWithRector.php)

```diff
-expect(str_starts_with($string, 'Hello'))->toBeTrue();
-expect(str_starts_with($text, $prefix))->toBeTrue();
+expect($string)->toStartWith('Hello');
+expect($text)->toStartWith($prefix);
```

<br>

## UseTypeMatchersRector

Converts `expect(is_array($x))->toBeTrue()` to `expect($x)->toBeArray()`

- class: [`RectorPest\Rules\UseTypeMatchersRector`](../src/Rules/UseTypeMatchersRector.php)

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

## UsesToExtendRector

Converts `uses()` and `pest()->uses()` to `pest()->extend()` for classes and `pest()->use()` for traits

- class: [`RectorPest\Rules\Pest2ToPest3\UsesToExtendRector`](../src/Rules/Pest2ToPest3/UsesToExtendRector.php)

```diff
-uses(Tests\TestCase::class)->in('Feature');
-uses(Illuminate\Foundation\Testing\RefreshDatabase::class);
-pest()->uses(Tests\TestCase::class)->in('Feature');
+pest()->extend(Tests\TestCase::class)->in('Feature');
+pest()->use(Illuminate\Foundation\Testing\RefreshDatabase::class);
+pest()->extend(Tests\TestCase::class)->in('Feature');
```

<br>
