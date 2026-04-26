# Rector Pest

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/rector-pest.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/rector-pest)
[![Total Downloads on Packagist](https://img.shields.io/packagist/dt/mrpunyapal/rector-pest.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/rector-pest)
[![CI](https://github.com/mrpunyapal/rector-pest/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mrpunyapal/rector-pest/actions/workflows/ci.yml)

Rector rules for [PestPHP](https://pestphp.com/) to improve code quality and help with version upgrades.

## Available Rules

See all available Pest rules [here](/docs/rules.md).

## Installation

```bash
composer require --dev mrpunyapal/rector-pest
```

## Available Rule Sets

### Code Quality

Improve your Pest tests with better readability and expressiveness.

```php
// rector.php
use RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_CODE_QUALITY,
    ]);
```

| Set | Description |
|-----|-------------|
| [`PestSetList::PEST_CODE_QUALITY`](config/sets/pest-code-quality.php) | Converts expect() assertions to use Pest's built-in matchers for better readability |
| [`PestSetList::PEST_CHAIN`](config/sets/pest-chain.php) | Merges multiple expect() calls into chained expectations and optimizes their order. |
| [`PestSetList::PEST_LARAVEL`](config/sets/pest-laravel.php) | Laravel-specific rules (requires `illuminate/support`): converts `Str::` equality checks to Pest string case matchers |
| [`PestSetList::PEST_MIGRATION`](config/sets/pest-migration.php) | PHPUnit → Pest migration rules (opt-in): converts assertions, data providers, and test structure |
| [`PestSetList::PEST_BROWSER`](config/sets/pest-browser.php) | Pest Browser code-quality rules (requires `pestphp/pest-plugin-browser`): converts `expect($page->getter())` patterns to dedicated browser assertion methods |

### Version Upgrade Sets

Use `PestLevelSetList` to automatically upgrade to a specific Pest version. Sets for higher versions include sets for lower versions.

```php
// rector.php
use RectorPest\Set\PestLevelSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestLevelSetList::UP_TO_PEST_40,
    ]);
```

| Set | Description |
|-----|-------------|
| `PestLevelSetList::UP_TO_PEST_30` | Upgrade from Pest v2 to v3 |
| `PestLevelSetList::UP_TO_PEST_40` | Upgrade from Pest v2/v3 to v4 (includes v3 changes) |

### Manual Version Configuration

Use `PestSetList` if you only want changes for a specific version:

```php
// rector.php
use RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_30, // Only v2→v3 changes
    ]);
```

| Set | Description |
|-----|-------------|
| [`PestSetList::PEST_30`](config/sets/pest30.php) | Pest v2 → v3 migration rules |
| [`PestSetList::PEST_40`](config/sets/pest40.php) | Pest v3 → v4 migration rules |

## Chaining Expectations

The `PEST_CHAIN` set automatically merges multiple `expect()` calls into a single chained expression.

```php
// rector.php
use RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_CODE_QUALITY,
        PestSetList::PEST_CHAIN,
    ]);
```

**Before:**
```php
expect($value1)->toBe(10);
expect($value1)->toBeInt();
expect($value2)->toBe(20);
expect($value2)->toBeString();
expect($value3)->toBe(30);
```

**After:**
```php
expect($value1)->toBe(10)
    ->toBeInt()
    ->and($value2)->toBe(20)
    ->toBeString()
    ->and($value3)->toBe(30);
```

**Formatting rules** (requires `rector/rector` 2.4.1+):

- The **first matcher after `expect()`** stays on the same line as `expect()`
- The **first matcher after `->and()`** stays on the same line as `->and()`
- Every **additional matcher** in a segment goes on its own indented line
- `->not->toBeX()` is treated as a single unit and stays inline

> **Note:** On `rector/rector` versions older than 2.4.1, chaining still works but all method calls are printed inline on a single line.

## PHPUnit to Pest Migration

The `PEST_MIGRATION` set helps convert PHPUnit test patterns to Pest equivalents. This is an opt-in set — review changes carefully after applying.

```php
// rector.php
use RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withSets([
        PestSetList::PEST_MIGRATION,
    ]);
```

**Included rules:**

| Rule | Description |
|------|-------------|
| `ConvertAssertToExpectRector` | Converts `$this->assert*()` calls to `expect()->` chains |

## Pest Browser Testing

The `PEST_BROWSER` set improves code quality of tests written with [`pestphp/pest-plugin-browser`](https://pestphp.com/docs/browser-testing). It converts verbose `expect($page->getter())` patterns into the plugin's dedicated browser assertion methods, producing clearer failure messages and more readable tests.

> **Requirement:** The target project must have `pestphp/pest-plugin-browser` installed.

```php
// rector.php
use RectorPest\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests/Browser',
    ])
    ->withSets([
        PestSetList::PEST_BROWSER,
    ]);
```

**Included rules:**

| Rule | Transforms |
|------|------------|
| `UseBrowserValueAssertionsRector` | `expect($page->value($sel))->toBe($v)` → `$page->assertValue($sel, $v)` and negated form → `assertValueIsNot` |
| `UseBrowserAttributeAssertionsRector` | `expect($page->attribute($sel, $attr))->toBe/toContain/not->toContain/toBeNull` → `assertAttribute`, `assertAttributeContains`, `assertAttributeDoesntContain`, `assertAttributeMissing` |
| `UseBrowserSourceAssertionsRector` | `expect($page->content())->toContain($html)` → `assertSourceHas` and negated form → `assertSourceMissing` |
| `UseBrowserScriptAssertionsRector` | `expect($page->script($expr))->toBe/toEqual($v)` → `$page->assertScript($expr, $v)` |
| `UseBrowserUrlAssertionsRector` | `expect($page->url())->toBe($url)` → `$page->assertUrlIs($url)` |

> **URL assertions scope:** only `assertUrlIs` is covered because it is the only URL-related assertion that has a direct `expect($page->getter())->toBe()` equivalent. Path, scheme, host, port, query-string, and fragment assertions (`assertPathIs`, `assertSchemeIs`, `assertHostIs`, etc.) have no `expect()` counterparts in the plugin and are out of scope.

> **Attribute assertions scope:** `assertAriaAttribute` and `assertDataAttribute` are not covered. Those methods accept the attribute name _without_ the `aria-`/`data-` prefix, making a safe automatic transformation ambiguous. Use them manually.

**Before:**
```php
expect($page->value('input[name=email]'))->toBe('test@example.com');
expect($page->attribute('img', 'alt'))->toBe('Profile Picture');
expect($page->attribute('div', 'class'))->toContain('container');
expect($page->attribute('div', 'class'))->not->toContain('hidden');
expect($page->attribute('button', 'disabled'))->toBeNull();
expect($page->content())->toContain('<h1>Welcome</h1>');
expect($page->content())->not->toContain('<div class="error">');
expect($page->script('document.title'))->toBe('Home Page');
expect($page->script('window.scrollY'))->toEqual(0);
expect($page->url())->toBe('https://example.com/home');
```

**After:**
```php
$page->assertValue('input[name=email]', 'test@example.com');
$page->assertAttribute('img', 'alt', 'Profile Picture');
$page->assertAttributeContains('div', 'class', 'container');
$page->assertAttributeDoesntContain('div', 'class', 'hidden');
$page->assertAttributeMissing('button', 'disabled');
$page->assertSourceHas('<h1>Welcome</h1>');
$page->assertSourceMissing('<div class="error">');
$page->assertScript('document.title', 'Home Page');
$page->assertScript('window.scrollY', 0);
$page->assertUrlIs('https://example.com/home');
```

## Using Individual Rules

You can also use individual rules instead of sets:

```php
// rector.php
use RectorPest\Rules\ChainExpectCallsRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/tests',
    ])
    ->withRules([
        ChainExpectCallsRector::class,
    ]);
```

## Running Rector

```bash
# Preview changes
vendor/bin/rector process --dry-run

# Apply changes
vendor/bin/rector process
```

## Requirements

- PHP 8.2+
- Rector 2.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
