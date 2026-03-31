<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts try/catch patterns in tests to Pest's toThrow() matcher
 */
final class UseToThrowRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts try/catch patterns to expect()->toThrow()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
try {
    doSomething();
} catch (RuntimeException $e) {
    expect($e->getMessage())->toBe('error');
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');
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
        return NodeGroup::STMTS_AWARE;
    }

    /**
     * @param StmtsAwareInterface&Node $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! property_exists($node, 'stmts') || $node->stmts === null) {
            return null;
        }

        $hasChanged = false;
        $newStmts = [];

        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof TryCatch) {
                $newStmts[] = $stmt;

                continue;
            }

            $result = $this->convertTryCatch($stmt);
            if ($result === null) {
                $newStmts[] = $stmt;

                continue;
            }

            $newStmts[] = new Expression($result);
            $hasChanged = true;
        }

        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $newStmts;

        return $node;
    }

    private function convertTryCatch(TryCatch $tryCatch): ?MethodCall
    {
        if (count($tryCatch->catches) !== 1) {
            return null;
        }

        if ($tryCatch->finally !== null) {
            return null;
        }

        $catch = $tryCatch->catches[0];

        if (count($catch->types) !== 1) {
            return null;
        }

        $tryStmts = $tryCatch->stmts;
        if ($tryStmts === []) {
            return null;
        }

        $exceptionClass = $catch->types[0];
        $message = $this->extractMessageAssertion($catch);

        if ($catch->stmts !== [] && $message === null) {
            return null;
        }

        $arrowFunction = new ArrowFunction([
            'expr' => $this->buildTryExpression($tryStmts),
        ]);

        $expectCall = new FuncCall(new Name('expect'), [new Arg($arrowFunction)]);

        $toThrowArgs = [new Arg(new Node\Expr\ClassConstFetch($exceptionClass, 'class'))];
        if ($message !== null) {
            $toThrowArgs[] = new Arg($message);
        }

        return new MethodCall($expectCall, 'toThrow', $toThrowArgs);
    }

    /**
     * Build the expression for the try block body
     */
    private function buildTryExpression(array $stmts): Node\Expr
    {
        if (count($stmts) === 1 && $stmts[0] instanceof Expression) {
            return $stmts[0]->expr;
        }

        return new FuncCall(
            new ArrowFunction([
                'expr' => count($stmts) === 1 && $stmts[0] instanceof Expression
                    ? $stmts[0]->expr
                    : new Node\Scalar\Int_(0),
            ])
        );
    }

    /**
     * Extract message from expect($e->getMessage())->toBe('...')
     */
    private function extractMessageAssertion(Catch_ $catch): ?Node\Expr
    {
        if ($catch->stmts === []) {
            return null;
        }

        if (count($catch->stmts) !== 1) {
            return null;
        }

        $stmt = $catch->stmts[0];
        if (! $stmt instanceof Expression) {
            return null;
        }

        if (! $stmt->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $stmt->expr;

        if (! $this->isExpectChain($methodCall)) {
            return null;
        }

        if (! $this->isName($methodCall->name, 'toBe')) {
            return null;
        }

        if (count($methodCall->args) !== 1) {
            return null;
        }

        $expectArg = $this->getExpectArgument($methodCall);
        if (! $expectArg instanceof MethodCall) {
            return null;
        }

        if (! $this->isName($expectArg->name, 'getMessage')) {
            return null;
        }

        if (! $expectArg->var instanceof Variable) {
            return null;
        }

        if ($catch->var === null) {
            return null;
        }

        if (! $catch->var instanceof Variable) {
            return null;
        }

        if (! $this->isName($expectArg->var, $this->getName($catch->var))) {
            return null;
        }

        $arg = $methodCall->args[0];
        if (! $arg instanceof Arg) {
            return null;
        }

        return $arg->value;
    }
}
