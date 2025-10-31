# Contributing to Rector Pest

Thank you for considering contributing to Rector Pest! This document provides guidelines for contributing to this project.

## Development Setup

1. Clone the repository:
```bash
git clone https://github.com/MrPunyapal/rector-pest.git
cd rector-pest
```

2. Install dependencies:
```bash
composer install
```

## Running Tests

Currently, this package uses Rector itself to validate the rules. You can test the rules by:

1. Running Rector in dry-run mode:
```bash
composer rector-dry
```

2. Running PHPStan analysis:
```bash
composer analyse
```

3. Checking code style:
```bash
composer check-style
```

## Creating a New Rule

To create a new Rector rule for Pest:

1. Create a new class in `src/Rules/` that extends `AbstractRector`
2. Implement the required methods:
   - `getRuleDefinition()`: Describe what the rule does
   - `getNodeTypes()`: Specify which AST nodes to process
   - `refactor()`: Implement the transformation logic

3. Add the rule to `config/pest-set.php`

4. Create fixture files in `tests/Fixture/` to demonstrate the rule

Example structure:

```php
<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class YourNewRule extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Description of what this rule does',
            [
                new CodeSample(
                    // Before
                    'code before transformation',
                    // After
                    'code after transformation'
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [/* Node types to process */];
    }

    public function refactor(Node $node): ?Node
    {
        // Transformation logic
        return $node;
    }
}
```

## Code Style

This project follows PSR-12 coding standards. Before submitting a pull request:

1. Fix code style issues:
```bash
composer fix-style
```

2. Run static analysis:
```bash
composer analyse
```

## Pull Request Process

1. Fork the repository
2. Create a new branch for your feature/fix
3. Make your changes
4. Ensure all checks pass
5. Update README.md if you've added new rules
6. Submit a pull request with a clear description of your changes

## Adding Documentation

When adding new rules, please:

1. Update the README.md with examples
2. Include code samples showing before/after transformations
3. Document any configuration options

## Questions?

Feel free to open an issue if you have questions or need help!
