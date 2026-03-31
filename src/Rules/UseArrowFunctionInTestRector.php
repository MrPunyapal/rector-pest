<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts single-expression test closures to arrow functions
 */
final class UseArrowFunctionInTestRector extends AbstractRector
{
    /**
     * Pest functions where closures can be converted to arrow functions.
     *
     * @var string[]
     */
    private const CONVERTIBLE_FUNCTIONS = ['test', 'it'];

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts single-expression test closures to arrow functions',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
test('example', function () {
    expect(true)->toBeTrue();
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
test('example', fn () => expect(true)->toBeTrue());
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

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
        if (! $this->isNames($node, self::CONVERTIBLE_FUNCTIONS)) {
            return null;
        }

        $closureArgIndex = $this->findClosureArgIndex($node);
        if ($closureArgIndex === null) {
            return null;
        }

        $arg = $node->args[$closureArgIndex];
        if (! $arg instanceof Arg) {
            return null;
        }

        $closure = $arg->value;
        if (! $closure instanceof Closure) {
            return null;
        }

        if (count($closure->stmts) !== 1) {
            return null;
        }

        $stmt = $closure->stmts[0];
        if (! $stmt instanceof Expression) {
            return null;
        }

        if ($closure->uses !== []) {
            return null;
        }

        $arrowFunction = new ArrowFunction([
            'expr' => $stmt->expr,
            'params' => $closure->params,
            'static' => $closure->static,
            'returnType' => $closure->returnType,
        ]);

        $node->args[$closureArgIndex] = new Arg($arrowFunction);

        return $node;
    }

    private function findClosureArgIndex(FuncCall $node): ?int
    {
        foreach ($node->args as $index => $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            if ($arg->value instanceof Closure) {
                return $index;
            }
        }

        return null;
    }
}
