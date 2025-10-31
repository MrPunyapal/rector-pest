<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit assertions to Pest expectations
 * 
 * Examples:
 * - $this->assertTrue($value) => expect($value)->toBeTrue()
 * - $this->assertEquals($expected, $actual) => expect($actual)->toBe($expected)
 * - $this->assertSame($expected, $actual) => expect($actual)->toBe($expected)
 */
final class ConvertAssertToExpectRule extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const ASSERTION_MAP = [
        'assertTrue' => 'toBeTrue',
        'assertFalse' => 'toBeFalse',
        'assertNull' => 'toBeNull',
        'assertEmpty' => 'toBeEmpty',
        'assertNotEmpty' => 'not->toBeEmpty',
        'assertNotNull' => 'not->toBeNull',
        'assertCount' => 'toHaveCount',
        'assertInstanceOf' => 'toBeInstanceOf',
        'assertIsArray' => 'toBeArray',
        'assertIsString' => 'toBeString',
        'assertIsInt' => 'toBeInt',
        'assertIsBool' => 'toBeBool',
        'assertIsFloat' => 'toBeFloat',
        'assertIsObject' => 'toBeObject',
    ];

    /**
     * @var array<string, string>
     */
    private const COMPARISON_MAP = [
        'assertEquals' => 'toEqual',
        'assertSame' => 'toBe',
        'assertNotEquals' => 'not->toEqual',
        'assertNotSame' => 'not->toBe',
        'assertGreaterThan' => 'toBeGreaterThan',
        'assertGreaterThanOrEqual' => 'toBeGreaterThanOrEqual',
        'assertLessThan' => 'toBeLessThan',
        'assertLessThanOrEqual' => 'toBeLessThanOrEqual',
        'assertContains' => 'toContain',
        'assertNotContains' => 'not->toContain',
        'assertStringContainsString' => 'toContain',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts PHPUnit assertions to Pest expectations',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$this->assertTrue($value);
$this->assertEquals($expected, $actual);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBeTrue();
expect($actual)->toEqual($expected);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('PHPUnit\Framework\TestCase'))) {
            return null;
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        // Handle simple assertions (single argument)
        if (isset(self::ASSERTION_MAP[$methodName])) {
            return $this->createExpectCall($node, self::ASSERTION_MAP[$methodName], true);
        }

        // Handle comparison assertions (two arguments)
        if (isset(self::COMPARISON_MAP[$methodName])) {
            return $this->createExpectCall($node, self::COMPARISON_MAP[$methodName], false);
        }

        return null;
    }

    private function createExpectCall(MethodCall $node, string $expectMethod, bool $isSingleArg): Node
    {
        $args = $node->getArgs();
        
        if ($isSingleArg) {
            // For single argument assertions: assertTrue($value) => expect($value)->toBeTrue()
            $expectArg = $args[0] ?? null;
            if ($expectArg === null) {
                return $node;
            }

            $expectCall = new \PhpParser\Node\Expr\FuncCall(
                new \PhpParser\Node\Name('expect'),
                [$expectArg]
            );

            // Handle chained methods (e.g., 'not->toBeEmpty')
            if (str_contains($expectMethod, '->')) {
                $methods = explode('->', $expectMethod);
                $result = $expectCall;
                foreach ($methods as $method) {
                    $result = new MethodCall($result, new Identifier($method));
                }
                return $result;
            }

            return new MethodCall($expectCall, new Identifier($expectMethod));
        }

        // For comparison assertions: assertEquals($expected, $actual) => expect($actual)->toEqual($expected)
        if (count($args) < 2) {
            return $node;
        }

        $expectedArg = $args[0];
        $actualArg = $args[1];

        $expectCall = new \PhpParser\Node\Expr\FuncCall(
            new \PhpParser\Node\Name('expect'),
            [$actualArg]
        );

        // Handle chained methods
        if (str_contains($expectMethod, '->')) {
            $methods = explode('->', $expectMethod);
            $result = $expectCall;
            foreach ($methods as $i => $method) {
                if ($i === count($methods) - 1) {
                    // Last method gets the expected argument
                    $result = new MethodCall($result, new Identifier($method), [$expectedArg]);
                } else {
                    $result = new MethodCall($result, new Identifier($method));
                }
            }
            return $result;
        }

        return new MethodCall($expectCall, new Identifier($expectMethod), [$expectedArg]);
    }
}
