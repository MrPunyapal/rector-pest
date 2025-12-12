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
 * Converts is_readable()/is_writable() checks to Pest's toBeReadable()/toBeWritable() matchers.
 *
 * Before: expect(is_readable($path))->toBeTrue()
 * After:  expect($path)->toBeReadable()
 */
final class UseToBeReadableWritableRector extends AbstractRector
{
    /**
     * Map of functions to their matcher methods.
     *
     * @var array<string, string>
     */
    private const FUNCTION_MATCHERS = [
        'is_readable' => 'toBeReadable',
        'is_writable' => 'toBeWritable',
        'is_writeable' => 'toBeWritable', // alias
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts is_readable()/is_writable() checks to toBeReadable()/toBeWritable() matchers',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect(is_readable($path))->toBeTrue();
expect(is_writable($file))->toBeTrue();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($path)->toBeReadable();
expect($file)->toBeWritable();
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

        $funcName = $funcCall->name->toString();
        if (! isset(self::FUNCTION_MATCHERS[$funcName])) {
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

        $matcherMethod = self::FUNCTION_MATCHERS[$funcName];

        // Check if we need ->not
        $needsNot = $methodName === 'toBeFalse';
        if ($this->hasNotModifier($node)) {
            $needsNot = ! $needsNot;
        }

        if ($needsNot) {
            $notProperty = new PropertyFetch($expectCall, 'not');

            return new MethodCall($notProperty, $matcherMethod);
        }

        return new MethodCall($expectCall, $matcherMethod);
    }
}
