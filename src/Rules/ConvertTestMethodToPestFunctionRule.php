<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\Closure;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit test methods to Pest test functions
 * 
 * Examples:
 * - public function testSomething() { ... } => test('something', function() { ... })
 * - public function test_it_does_something() { ... } => it('does something', function() { ... })
 */
final class ConvertTestMethodToPestFunctionRule extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts PHPUnit test methods to Pest test functions',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MyTest extends TestCase
{
    public function testItWorks(): void
    {
        $this->assertTrue(true);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
test('it works', function () {
    expect(true)->toBeTrue();
});
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // Only process public test methods
        if (!$node->isPublic()) {
            return null;
        }

        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        // Check if it's a test method (starts with 'test')
        if (!str_starts_with($methodName, 'test')) {
            return null;
        }

        // Extract test name from method name
        $testName = $this->extractTestName($methodName);
        $functionName = $this->determineFunctionName($methodName);

        // Create closure from method body
        $closure = new Closure([
            'stmts' => $node->stmts,
            'params' => $node->params,
        ]);

        // Create Pest test function call
        $pestTest = new Expression(
            new FuncCall(
                new Name($functionName),
                [
                    new Arg(new String_($testName)),
                    new Arg($closure),
                ]
            )
        );

        return $pestTest;
    }

    private function extractTestName(string $methodName): string
    {
        // Remove 'test' prefix
        $name = preg_replace('/^test_?/', '', $methodName);
        
        if ($name === null || $name === '') {
            return $methodName;
        }

        // Convert camelCase or snake_case to readable string
        // testItWorks => it works
        // test_it_works => it works
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        $name = str_replace('_', ' ', $name);
        
        return strtolower($name);
    }

    private function determineFunctionName(string $methodName): string
    {
        // If method name is like test_it_... or testIt..., use 'it'
        if (preg_match('/^test_?it[A-Z_]/', $methodName)) {
            return 'it';
        }

        // Otherwise use 'test'
        return 'test';
    }
}
