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
 * Converts array_key_exists() checks to Pest's toHaveKey() matcher.
 *
 * Before: expect(array_key_exists('key', $array))->toBeTrue()
 * After:  expect($array)->toHaveKey('key')
 */
final class UseToHaveKeyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts array_key_exists() checks to toHaveKey() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(array_key_exists('id', $array))->toBeTrue();
expect(array_key_exists($key, $data))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($array)->toHaveKey('id');
expect($data)->toHaveKey($key);
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

        if ($funcCall->name->toString() !== 'array_key_exists') {
            return null;
        }

        // array_key_exists requires 2 arguments: key, array
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $keyArg = $funcCall->args[0];
        $arrayArg = $funcCall->args[1];

        if (! $keyArg instanceof Arg || ! $arrayArg instanceof Arg) {
            return null;
        }

        // Update expect() to use the array
        $expectCall->args[0] = new Arg($arrayArg->value);

        // Check if we need ->not
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        // Build the new method call chain
        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toHaveKey', [new Arg($keyArg->value)]);
        }

        return new MethodCall($expectCall, 'toHaveKey', [new Arg($keyArg->value)]);
    }
}
