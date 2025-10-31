# Rector Pest

Rector rules for [PestPHP](https://pestphp.com) - Automated refactoring and best practices enforcement for the Pest testing framework.

## Installation

Install the package via Composer:

```bash
composer require --dev mrpunyapal/rector-pest
```

## Usage

Create or update your `rector.php` configuration file:

```php
<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/vendor/mrpunyapal/rector-pest/config/pest-set.php');
    
    $rectorConfig->paths([
        __DIR__ . '/tests',
    ]);
};
```

Then run Rector:

```bash
vendor/bin/rector process
```

## Available Rules

### ConvertAssertToExpectRule

Converts PHPUnit assertions to Pest expectations.

**Before:**
```php
$this->assertTrue($value);
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);
$this->assertNull($value);
```

**After:**
```php
expect($value)->toBeTrue();
expect($actual)->toEqual($expected);
expect($actual)->toBe($expected);
expect($value)->toBeNull();
```

**Supported Assertions:**

- `assertTrue()` → `toBeTrue()`
- `assertFalse()` → `toBeFalse()`
- `assertNull()` → `toBeNull()`
- `assertEmpty()` → `toBeEmpty()`
- `assertNotEmpty()` → `not->toBeEmpty()`
- `assertNotNull()` → `not->toBeNull()`
- `assertCount()` → `toHaveCount()`
- `assertInstanceOf()` → `toBeInstanceOf()`
- `assertIsArray()` → `toBeArray()`
- `assertIsString()` → `toBeString()`
- `assertIsInt()` → `toBeInt()`
- `assertIsBool()` → `toBeBool()`
- `assertIsFloat()` → `toBeFloat()`
- `assertIsObject()` → `toBeObject()`
- `assertEquals()` → `toEqual()`
- `assertSame()` → `toBe()`
- `assertNotEquals()` → `not->toEqual()`
- `assertNotSame()` → `not->toBe()`
- `assertGreaterThan()` → `toBeGreaterThan()`
- `assertGreaterThanOrEqual()` → `toBeGreaterThanOrEqual()`
- `assertLessThan()` → `toBeLessThan()`
- `assertLessThanOrEqual()` → `toBeLessThanOrEqual()`
- `assertContains()` → `toContain()`
- `assertNotContains()` → `not->toContain()`
- `assertStringContainsString()` → `toContain()`

### ConvertTestMethodToPestFunctionRule

Converts PHPUnit test methods to Pest test functions.

**Before:**
```php
class MyTest extends TestCase
{
    public function testItWorks(): void
    {
        $this->assertTrue(true);
    }
    
    public function test_user_can_login(): void
    {
        $this->assertTrue(true);
    }
}
```

**After:**
```php
it('works', function () {
    expect(true)->toBeTrue();
});

test('user can login', function () {
    expect(true)->toBeTrue();
});
```

### ConvertSetUpToBeforeEachRule

Converts PHPUnit `setUp()` and `tearDown()` methods to Pest `beforeEach()` and `afterEach()`.

**Before:**
```php
class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    
    protected function tearDown(): void
    {
        $this->user->delete();
        parent::tearDown();
    }
}
```

**After:**
```php
beforeEach(function () {
    $this->user = User::factory()->create();
});

afterEach(function () {
    $this->user->delete();
});
```

## Configuration

You can selectively enable/disable rules by creating a custom configuration:

```php
<?php

use Rector\Config\RectorConfig;
use MrPunyapal\RectorPest\Rules\ConvertAssertToExpectRule;
use MrPunyapal\RectorPest\Rules\ConvertTestMethodToPestFunctionRule;

return static function (RectorConfig $rectorConfig): void {
    // Enable only specific rules
    $rectorConfig->rule(ConvertAssertToExpectRule::class);
    $rectorConfig->rule(ConvertTestMethodToPestFunctionRule::class);
    
    $rectorConfig->paths([
        __DIR__ . '/tests',
    ]);
};
```

## Requirements

- PHP 8.1 or higher
- Rector 1.0 or higher

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Credits

- [MrPunyapal](https://github.com/MrPunyapal)
- [Rector](https://github.com/rectorphp/rector)
- [PestPHP](https://pestphp.com)