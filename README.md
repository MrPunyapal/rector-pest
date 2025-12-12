# Rector Pest

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/rector-pest.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/rector-pest)

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
