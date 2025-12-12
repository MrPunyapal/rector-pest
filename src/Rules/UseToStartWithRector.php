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
 * Converts str_starts_with() checks to Pest's toStartWith() matcher.
 *
 * Before: expect(str_starts_with($string, 'prefix'))->toBeTrue()
 * After:  expect($string)->toStartWith('prefix')
 */
final class UseToStartWithRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts str_starts_with() checks to toStartWith() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(str_starts_with($string, 'Hello'))->toBeTrue();
expect(str_starts_with($text, $prefix))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($string)->toStartWith('Hello');
expect($text)->toStartWith($prefix);
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

        if (! $arg->value instanceof FuncCall) {
            return null;
        }

        $funcCall = $arg->value;
        if (! $funcCall->name instanceof Name) {
            return null;
        }

        if ($funcCall->name->toString() !== 'str_starts_with') {
            return null;
        }

        // str_starts_with requires 2 arguments: haystack, needle
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $haystackArg = $funcCall->args[0];
        $needleArg = $funcCall->args[1];

        if (! $haystackArg instanceof Arg || ! $needleArg instanceof Arg) {
            return null;
        }

        // Update expect() to use the string (haystack)
        $expectCall->args[0] = new Arg($haystackArg->value);

        // Check if we need ->not
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toStartWith', [new Arg($needleArg->value)]);
        }

        return new MethodCall($expectCall, 'toStartWith', [new Arg($needleArg->value)]);
    }
}
