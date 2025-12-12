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
 * Converts property_exists() checks to Pest's toHaveProperty() matcher.
 *
 * Before: expect(property_exists($object, 'name'))->toBeTrue()
 * After:  expect($object)->toHaveProperty('name')
 */
final class UseToHavePropertyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts property_exists() checks to toHaveProperty() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(property_exists($object, 'name'))->toBeTrue();
expect(property_exists($user, 'email'))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($object)->toHaveProperty('name');
expect($user)->toHaveProperty('email');
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

        if ($funcCall->name->toString() !== 'property_exists') {
            return null;
        }

        // property_exists requires 2 arguments: object|class, property
        if (count($funcCall->args) !== 2) {
            return null;
        }

        $objectArg = $funcCall->args[0];
        $propertyArg = $funcCall->args[1];

        if (! $objectArg instanceof Arg || ! $propertyArg instanceof Arg) {
            return null;
        }

        // Update expect() to use the object
        $expectCall->args[0] = new Arg($objectArg->value);

        // Check if we need ->not
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toHaveProperty', [new Arg($propertyArg->value)]);
        }

        return new MethodCall($expectCall, 'toHaveProperty', [new Arg($propertyArg->value)]);
    }
}
