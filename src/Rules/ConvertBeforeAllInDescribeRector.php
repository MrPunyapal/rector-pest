<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use RectorPest\AbstractSemanticPestRector;
use RectorPest\Support\PestFunctionDetector;
use RectorPest\ValueObject\PestSemanticCategory;
use RectorPest\ValueObject\PestSemanticIssue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces invalid describe-scoped beforeAll/afterAll hooks with supported per-test hooks.
 */
final class ConvertBeforeAllInDescribeRector extends AbstractSemanticPestRector
{
    /**
     * @var array<string, string>
     */
    private const HOOK_REPLACEMENTS = [
        'beforeAll' => 'beforeEach',
        'afterAll' => 'afterEach',
    ];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces invalid beforeAll() and afterAll() hooks inside describe() with beforeEach() and afterEach()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
describe('users', function (): void {
    beforeAll(function (): void {
        refreshDatabase();
    });
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
describe('users', function (): void {
    beforeEach(function (): void {
        refreshDatabase();
    });
});
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return new PestSemanticIssue(
            ['pest.beforeAllInDescribe', 'pest.afterAllInDescribe'],
            PestSemanticCategory::LIFECYCLE,
            'Describe-scoped all-hooks should be converted to each-hooks.',
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! PestFunctionDetector::isDescribeFunction($node)) {
            return null;
        }

        $closure = PestFunctionDetector::extractClosure($node);

        if (! $closure instanceof Closure) {
            return null;
        }

        return $this->refactorDescribeClosure($closure) ? $node : null;
    }

    private function refactorDescribeClosure(Closure $closure): bool
    {
        $hasChanged = false;

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof FuncCall) {
                continue;
            }

            $call = $stmt->expr;
            if (! $call->name instanceof Name) {
                continue;
            }

            $name = $call->name->toString();

            if (isset(self::HOOK_REPLACEMENTS[$name])) {
                $call->name = new Name(self::HOOK_REPLACEMENTS[$name]);
                $hasChanged = true;

                continue;
            }

            if ($name !== 'describe') {
                continue;
            }

            $nestedClosure = PestFunctionDetector::extractClosure($call);

            if (! $nestedClosure instanceof Closure) {
                continue;
            }

            if ($this->refactorDescribeClosure($nestedClosure)) {
                $hasChanged = true;
            }
        }

        return $hasChanged;
    }
}
