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
| [`PestSetList::PEST_CHAIN`](config/sets/pest-chain.php) | Merges multiple expect() calls into chained expectations and optimizes their order. Includes automatic formatting for better readability. |

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

## Formatting Chained Expectations

The `PEST_CHAIN` set automatically chains multiple `expect()` calls and formats them for better readability. Each chained method call appears on a separate line with proper indentation.

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
expect($value2)->toBe(20);
expect($value3)->toBe(30);
```

**After:**
```php
expect($value1)->toBe(10)
    ->and($value2)
    ->toBe(20)
    ->and($value3)
    ->toBe(30);
```

### Custom Formatting Configuration

If you want to use chaining rules but control the formatting yourself, you can configure the individual rules:

```php
// rector.php
use RectorPest\Rules\ChainExpectCallsRector;
use RectorPest\Rules\EnsureTypeChecksFirstRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/tests',
    ]);
    
    $rectorConfig->rules([
        ChainExpectCallsRector::class,
        EnsureTypeChecksFirstRector::class,
    ]);
    
    // Optional: Enable formatting for chained calls
    $rectorConfig->newLineOnFluentCall();
};
```

**Note:** The `newLineOnFluentCall()` option affects all fluent calls in your codebase, not just Pest expectations. The `PEST_CHAIN` set includes this option by default. If you want to keep this formatting only for your tests, make sure to configure your paths accordingly.

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
