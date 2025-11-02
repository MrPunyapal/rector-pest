<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use MrPunyapal\RectorPest\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChainExpectCallsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts multiple expect() calls into chained calls using and()',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10);
expect($value)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10)
    ->and($value)->toBeInt();
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
        return [StmtsAwareInterface::class];
    }

    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        $hasChanged = false;
        $stmts = $node->stmts;

        // Keep processing until no more changes
        do {
            $changedInPass = false;

            foreach ($stmts as $key => $stmt) {
                if (! $stmt instanceof Expression) {
                    continue;
                }

                if (! $stmt->expr instanceof MethodCall) {
                    continue;
                }

                $methodCall = $stmt->expr;
                if (! $this->isExpectChain($methodCall)) {
                    continue;
                }

                $firstExpectArg = $this->getExpectArgument($methodCall);
                if ($firstExpectArg === null) {
                    continue;
                }

                if (! isset($stmts[$key + 1])) {
                    continue;
                }

                $nextStmt = $stmts[$key + 1];
                if (! $nextStmt instanceof Expression) {
                    continue;
                }

                if (! $nextStmt->expr instanceof MethodCall) {
                    continue;
                }

                $nextMethodCall = $nextStmt->expr;
                if (! $this->isExpectChain($nextMethodCall)) {
                    continue;
                }

                $nextExpectArg = $this->getExpectArgument($nextMethodCall);
                if ($nextExpectArg === null) {
                    continue;
                }

                if (! $this->nodeComparator->areNodesEqual($firstExpectArg, $nextExpectArg)) {
                    continue;
                }

                $chainedCall = $this->buildChainedCall($methodCall, $nextMethodCall, $nextExpectArg);

                $stmt->expr = $chainedCall;

                unset($stmts[$key + 1]);

                $stmts = array_values($stmts);

                $hasChanged = true;
                $changedInPass = true;

                break;
            }
        } while ($changedInPass);

        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $stmts;

        return $node;
    }

    /**
     * Check if a method call is an expect() chain (expect()->...)
     */
    private function isExpectChain(MethodCall $methodCall): bool
    {
        $current = $methodCall;
        while ($current->var instanceof MethodCall) {
            $current = $current->var;
        }

        if (! $current->var instanceof FuncCall) {
            return false;
        }

        return $this->isName($current->var, 'expect');
    }

    /**
     * Get the argument from an expect() call
     */
    private function getExpectArgument(MethodCall $methodCall): ?Node\Expr
    {
        $current = $methodCall;
        while ($current->var instanceof MethodCall) {
            $current = $current->var;
        }

        if (! $current->var instanceof FuncCall) {
            return null;
        }

        $expectCall = $current->var;
        if (! $this->isName($expectCall, 'expect')) {
            return null;
        }

        if (! isset($expectCall->args[0])) {
            return null;
        }

        return $expectCall->args[0]->value;
    }

    /**
     * Build the chained method call with and()
     */
    private function buildChainedCall(MethodCall $first, MethodCall $second, Node\Expr $expectArg): MethodCall
    {
        $current = $second;
        $methods = [];

        while ($current instanceof MethodCall) {
            if ($current->var instanceof FuncCall && $this->isName($current->var, 'expect')) {
                $methods[] = [
                    'name' => $current->name,
                    'args' => $current->args,
                ];
                break;
            }

            $methods[] = [
                'name' => $current->name,
                'args' => $current->args,
            ];

            $current = $current->var;
        }

        $methods = array_reverse($methods);

        $result = $first;

        $result = new MethodCall($result, 'and', [$this->nodeFactory->createArg($expectArg)]);

        foreach ($methods as $method) {
            $result = new MethodCall($result, $method['name'], $method['args']);
        }

        return $result;
    }
}
