<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Nop;
use RectorPest\AbstractSemanticPestRector;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Support\PestFunctionDetector;
use RectorPest\ValueObject\PestSemanticIssue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes truly empty Pest test closures and falls back to Pest's pending placeholder form.
 */
final class RemoveEmptyTestClosureRector extends AbstractSemanticPestRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces empty Pest test closures with the shorter pending-style test form',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
it('works', function (): void {});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
it('works');
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::emptyTestClosure();
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
        if (! PestFunctionDetector::isTestFunction($node)) {
            return null;
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if (! $closure instanceof Closure) {
            return null;
        }

        if ($this->shouldSkipClosureRemoval($node, $closure)) {
            return null;
        }

        unset($node->args[1]);
        $node->args = array_values($node->args);

        return $node;
    }

    private function shouldSkipClosureRemoval(FuncCall $funcCall, Closure $closure): bool
    {
        if ($closure->getComments() !== []) {
            return true;
        }

        if (isset($funcCall->args[1]) && $funcCall->args[1] instanceof Arg && $funcCall->args[1]->getComments() !== []) {
            return true;
        }

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Nop) {
                return true;
            }

            if ($stmt->getComments() !== []) {
                return true;
            }
        }

        return false;
    }
}
