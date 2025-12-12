<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest\Rules;

use MrPunyapal\RectorPest\AbstractRector;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ChainExpectCallsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains multiple expect() calls on the same value into a single chained expectation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10);
expect($value)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)
    ->toBe(10)
    ->toBeInt();
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
        return NodeGroup::STMTS_AWARE;
    }

    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! property_exists($node, 'stmts') || $node->stmts === null) {
            return null;
        }

        /** @var array<Node\Stmt> $stmts */
        $stmts = $node->stmts;
        $hasChanged = false;

        do {
            $changedInPass = false;

            foreach ($stmts as $key => $stmt) {
                if (! is_int($key)) {
                    continue;
                }

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
                if (! $firstExpectArg instanceof Expr) {
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
                if (! $nextExpectArg instanceof Expr) {
                    continue;
                }

                if (! $this->nodeComparator->areNodesEqual($firstExpectArg, $nextExpectArg)) {
                    continue;
                }

                $chainedCall = $this->buildChainedCall($methodCall, $nextMethodCall);

                $stmt->expr = $chainedCall;

                unset($stmts[$key + 1]);

                /** @var array<Node\Stmt> $stmts */
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

    private function buildChainedCall(MethodCall $first, MethodCall $second): MethodCall
    {
        $secondMethods = $this->collectChainMethods($second);

        $result = $this->rebuildMethodChain($first, $secondMethods);

        /** @var MethodCall $result */
        return $result;
    }
}
