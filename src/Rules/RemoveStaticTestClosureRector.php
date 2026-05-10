<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use RectorPest\AbstractSemanticPestRector;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Support\PestFunctionDetector;
use RectorPest\ValueObject\PestSemanticIssue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes unnecessary static modifiers from Pest callbacks.
 */
final class RemoveStaticTestClosureRector extends AbstractSemanticPestRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes static from Pest test and hook callbacks',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
it('uses the test case instance', static function (): void {
    expect($this)->not->toBeNull();
});
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
it('uses the test case instance', function (): void {
    expect($this)->not->toBeNull();
});
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::staticTestClosure();
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
        if (PestFunctionDetector::getFunctionName($node) === null) {
            return null;
        }

        $closure = PestFunctionDetector::extractClosure($node);
        if ($closure === null || ! $closure->static) {
            return null;
        }

        $closure->static = false;

        return $node;
    }
}
