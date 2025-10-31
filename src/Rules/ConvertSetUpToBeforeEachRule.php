<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name;
use PhpParser\Node\Arg;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit setUp/tearDown methods to Pest beforeEach/afterEach
 * 
 * Examples:
 * - protected function setUp(): void { ... } => beforeEach(function() { ... })
 * - protected function tearDown(): void { ... } => afterEach(function() { ... })
 */
final class ConvertSetUpToBeforeEachRule extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const METHOD_MAP = [
        'setUp' => 'beforeEach',
        'tearDown' => 'afterEach',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts PHPUnit setUp/tearDown methods to Pest beforeEach/afterEach',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->value = 42;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
beforeEach(function () {
    $this->value = 42;
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
        $methodName = $this->getName($node);
        if ($methodName === null) {
            return null;
        }

        // Check if it's a setUp or tearDown method
        if (!isset(self::METHOD_MAP[$methodName])) {
            return null;
        }

        $pestFunctionName = self::METHOD_MAP[$methodName];

        // Get method body, filtering out parent calls
        $stmts = $this->filterOutParentCalls($node->stmts ?? []);

        // Create closure from method body
        $closure = new Closure([
            'stmts' => $stmts,
        ]);

        // Create Pest beforeEach/afterEach function call
        $pestFunction = new Expression(
            new FuncCall(
                new Name($pestFunctionName),
                [
                    new Arg($closure),
                ]
            )
        );

        return $pestFunction;
    }

    /**
     * Filter out parent::setUp() and parent::tearDown() calls
     * 
     * @param array<Node\Stmt> $stmts
     * @return array<Node\Stmt>
     */
    private function filterOutParentCalls(array $stmts): array
    {
        return array_filter($stmts, function ($stmt) {
            if (!$stmt instanceof Expression) {
                return true;
            }

            $expr = $stmt->expr;
            if (!$expr instanceof Node\Expr\StaticCall) {
                return true;
            }

            if (!$expr->class instanceof Node\Name) {
                return true;
            }

            $className = $expr->class->toString();
            $methodName = $this->getName($expr->name);

            // Filter out parent::setUp() and parent::tearDown()
            return !($className === 'parent' && in_array($methodName, ['setUp', 'tearDown'], true));
        });
    }
}
