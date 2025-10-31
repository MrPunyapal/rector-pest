# Quick Start Guide

Get started with Rector Pest in 5 minutes!

## 1. Install

```bash
composer require --dev mrpunyapal/rector-pest
```

## 2. Configure

Create `rector.php` in your project root:

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

## 3. Preview Changes

See what will be changed without modifying files:

```bash
vendor/bin/rector process --dry-run
```

## 4. Apply Changes

Apply the transformations:

```bash
vendor/bin/rector process
```

## 5. Verify

Run your tests to ensure everything still works:

```bash
vendor/bin/pest
# or
vendor/bin/phpunit
```

## What Gets Changed?

### Assertions → Expectations
```php
// Before
$this->assertTrue($value);

// After
expect($value)->toBeTrue();
```

### Test Methods → Test Functions
```php
// Before
public function testUserLogin(): void {
    // ...
}

// After
test('user login', function () {
    // ...
});
```

### setUp/tearDown → beforeEach/afterEach
```php
// Before
protected function setUp(): void {
    parent::setUp();
    $this->user = User::factory()->create();
}

// After
beforeEach(function () {
    $this->user = User::factory()->create();
});
```

## Next Steps

- Check out [USAGE.md](USAGE.md) for detailed examples
- Read the [README.md](README.md) for all available rules
- See [CONTRIBUTING.md](CONTRIBUTING.md) to add your own rules

## Tips

1. **Always commit before running Rector** - You can easily revert if needed
2. **Use `--dry-run` first** - Preview changes before applying
3. **Test after migration** - Ensure all tests still pass
4. **Migrate incrementally** - Process one directory at a time if needed

## Need Help?

- 📖 [Full Documentation](README.md)
- 💡 [Usage Examples](USAGE.md)
- 🐛 [Report Issues](https://github.com/MrPunyapal/rector-pest/issues)
