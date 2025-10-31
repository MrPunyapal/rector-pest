# Usage Examples

This document provides detailed examples of how to use Rector Pest to migrate from PHPUnit to Pest.

## Basic Usage

### Step 1: Install the Package

```bash
composer require --dev mrpunyapal/rector-pest
```

### Step 2: Create Rector Configuration

Create a `rector.php` file in your project root:

```php
<?php

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // Import the Pest rule set
    $rectorConfig->import(__DIR__ . '/vendor/mrpunyapal/rector-pest/config/pest-set.php');
    
    // Specify the paths to process
    $rectorConfig->paths([
        __DIR__ . '/tests',
    ]);
};
```

### Step 3: Run Rector

Preview the changes (dry-run):
```bash
vendor/bin/rector process --dry-run
```

Apply the changes:
```bash
vendor/bin/rector process
```

## Migration Examples

### Example 1: Simple Assertion Migration

**Before (PHPUnit):**
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): void
    {
        $user = new User('John Doe');
        
        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->getName());
        $this->assertTrue($user->isActive());
    }
}
```

**After (Pest):**
```php
<?php

test('user can be created', function () {
    $user = new User('John Doe');
    
    expect($user)->not->toBeNull();
    expect($user->getName())->toEqual('John Doe');
    expect($user->isActive())->toBeTrue();
});
```

### Example 2: Data Provider Migration

**Before (PHPUnit):**
```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new Calculator();
    }
    
    public function testAddition(): void
    {
        $result = $this->calculator->add(2, 3);
        $this->assertEquals(5, $result);
        $this->assertIsInt($result);
    }
    
    public function testMultiplication(): void
    {
        $result = $this->calculator->multiply(4, 5);
        $this->assertEquals(20, $result);
    }
}
```

**After (Pest):**
```php
<?php

beforeEach(function () {
    $this->calculator = new Calculator();
});

test('addition', function () {
    $result = $this->calculator->add(2, 3);
    expect($result)->toEqual(5);
    expect($result)->toBeInt();
});

test('multiplication', function () {
    $result = $this->calculator->multiply(4, 5);
    expect($result)->toEqual(20);
});
```

### Example 3: Complex Test with Setup and Teardown

**Before (PHPUnit):**
```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\User;
use App\Models\Post;

class PostTest extends TestCase
{
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    
    public function test_it_creates_a_post(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'user_id' => $this->user->id,
        ]);
        
        $this->assertNotNull($post->id);
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertSame($this->user->id, $post->user_id);
    }
    
    public function test_it_can_delete_a_post(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'user_id' => $this->user->id,
        ]);
        
        $post->delete();
        
        $this->assertTrue($post->trashed());
    }
    
    protected function tearDown(): void
    {
        $this->user->delete();
        parent::tearDown();
    }
}
```

**After (Pest):**
```php
<?php

use App\Models\User;
use App\Models\Post;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('creates a post', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'user_id' => $this->user->id,
    ]);
    
    expect($post->id)->not->toBeNull();
    expect($post)->toBeInstanceOf(Post::class);
    expect($post->title)->toEqual('Test Post');
    expect($post->user_id)->toBe($this->user->id);
});

it('can delete a post', function () {
    $post = Post::create([
        'title' => 'Test Post',
        'user_id' => $this->user->id,
    ]);
    
    $post->delete();
    
    expect($post->trashed())->toBeTrue();
});

afterEach(function () {
    $this->user->delete();
});
```

## Selective Rule Usage

You can choose to use only specific rules:

```php
<?php

use Rector\Config\RectorConfig;
use MrPunyapal\RectorPest\Rules\ConvertAssertToExpectRule;

return static function (RectorConfig $rectorConfig): void {
    // Use only the assertion conversion rule
    $rectorConfig->rule(ConvertAssertToExpectRule::class);
    
    $rectorConfig->paths([
        __DIR__ . '/tests',
    ]);
};
```

## Tips for Migration

1. **Start with a backup**: Always commit your changes before running Rector
2. **Run in dry-run mode first**: Use `--dry-run` to see what will change
3. **Migrate incrementally**: Process one directory at a time
4. **Review the changes**: Carefully review all automated changes
5. **Run your tests**: Ensure all tests still pass after migration

## Common Patterns

### Array Assertions
```php
// Before
$this->assertIsArray($data);
$this->assertCount(3, $data);
$this->assertContains('value', $data);

// After
expect($data)->toBeArray();
expect($data)->toHaveCount(3);
expect($data)->toContain('value');
```

### Type Assertions
```php
// Before
$this->assertIsString($value);
$this->assertIsInt($count);
$this->assertIsBool($flag);
$this->assertIsFloat($price);

// After
expect($value)->toBeString();
expect($count)->toBeInt();
expect($flag)->toBeBool();
expect($price)->toBeFloat();
```

### Comparison Assertions
```php
// Before
$this->assertGreaterThan(10, $value);
$this->assertLessThanOrEqual(100, $value);

// After
expect($value)->toBeGreaterThan(10);
expect($value)->toBeLessThanOrEqual(100);
```

## Next Steps

After migrating to Pest, you can:

1. Remove PHPUnit TestCase base class
2. Remove the PHPUnit dependency (if not used elsewhere)
3. Explore Pest's additional features like datasets, custom expectations, and plugins
4. Clean up your test directory structure to follow Pest conventions

For more information, visit:
- [Pest Documentation](https://pestphp.com/docs)
- [Rector Documentation](https://getrector.com/documentation)
