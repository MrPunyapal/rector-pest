<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts in_array() checks to Pest's toContain() matcher.
 *
 * Before: expect(in_array($item, $array))->toBeTrue()
 * After:  expect($array)->toContain($item)
 */
final class UseToContainRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts in_array() checks to toContain() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(in_array($item, $array))->toBeTrue();
expect(in_array($item, $array, true))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toContain($item);
expect($array)->toContain($item);
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
        if (! $this->isExpectChain($node)) {
            return null;
        }

        if (! $node->name instanceof Identifier) {
            return null;
        }

        $methodName = $node->name->name;

        // Handle toBeTrue() and toBeFalse()
        if ($methodName !== 'toBeTrue' && $methodName !== 'toBeFalse') {
            return null;
        }

        $expectCall = $this->getExpectFuncCall($node);
        if (! $expectCall instanceof FuncCall) {
            return null;
        }

        if (! isset($expectCall->args[0])) {
            return null;
        }

        $arg = $expectCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        // Check if the argument is in_array() call
        if (! $arg->value instanceof FuncCall) {
            return null;
        }

        $funcCall = $arg->value;
        if (! $funcCall->name instanceof Name) {
            return null;
        }

        if ($funcCall->name->toString() !== 'in_array') {
            return null;
        }

        // in_array requires at least 2 arguments: needle, haystack
        if (count($funcCall->args) < 2) {
            return null;
        }

        $needleArg = $funcCall->args[0];
        $haystackArg = $funcCall->args[1];

        if (! $needleArg instanceof Arg || ! $haystackArg instanceof Arg) {
            return null;
        }

        // Update expect() to use the array (haystack)
        $expectCall->args[0] = new Arg($haystackArg->value);

        // Check if we need ->not based on toBeFalse or hasNotModifier
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        // Build the new method call chain
        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');
            return new MethodCall($notProperty, 'toContain', [new Arg($needleArg->value)]);
        }

        return new MethodCall($expectCall, 'toContain', [new Arg($needleArg->value)]);
    }
}
