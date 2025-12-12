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
 * Converts is_file() checks to Pest's toBeFile() matcher.
 *
 * Before: expect(is_file($path))->toBeTrue()
 * After:  expect($path)->toBeFile()
 */
final class UseToBeFileRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_file() checks to toBeFile() matcher',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_file($path))->toBeTrue();
expect(is_file('/tmp/file.txt'))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($path)->toBeFile();
expect('/tmp/file.txt')->toBeFile();
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

        if ($funcCall->name->toString() !== 'is_file') {
            return null;
        }

        if (count($funcCall->args) !== 1) {
            return null;
        }

        $pathArg = $funcCall->args[0];
        if (! $pathArg instanceof Arg) {
            return null;
        }

        // Update expect() to use the path directly
        $expectCall->args[0] = new Arg($pathArg->value);

        // Check if we need ->not
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, 'toBeFile');
        }

        return new MethodCall($expectCall, 'toBeFile');
    }
}
