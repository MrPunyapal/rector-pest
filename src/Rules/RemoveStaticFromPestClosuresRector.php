<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes unnecessary static modifiers from Pest callbacks.
 */
final class RemoveStaticFromPestClosuresRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const PEST_CALLBACK_FUNCTIONS = [
        'test',
        'it',
        'describe',
        'todo',
        'beforeEach',
        'afterEach',
        'beforeAll',
        'afterAll',
    ];

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
        if (! $this->isNames($node, self::PEST_CALLBACK_FUNCTIONS)) {
            return null;
        }

        $hasChanged = false;

        foreach ($node->args as $arg) {
            if (! $arg instanceof Arg) {
                continue;
            }

            if ($this->removeStaticModifier($arg->value)) {
                $hasChanged = true;
            }
        }

        return $hasChanged ? $node : null;
    }

    private function removeStaticModifier(Node $node): bool
    {
        if (! $node instanceof Closure && ! $node instanceof ArrowFunction) {
            return false;
        }

        if (! $node->static) {
            return false;
        }

        $node->static = false;

        return true;
    }
}