<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Chains multiple expect() calls on the same variable into a single chained expectation.
 *
 * This rule ONLY handles same-variable chaining. For different-variable bridging
 * with ->and(), see ChainDifferentVariableExpectCallsRector.
 */
final class ChainSameVariableExpectCallsRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Chains multiple expect() calls on the same variable into a single chained expectation',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10);
expect($a)->toBeInt();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($a)->toBe(10)->toBeInt();
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10);
expect($value)->toBeInt();
expect($value)->toBeGreaterThan(5);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect($value)->toBe(10)->toBeInt()->toBeGreaterThan(5);
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

                // don't merge across comments — preserve explicit separation
                $currentComments = (array) $stmt->getAttribute('comments', []);
                $nextComments = (array) $nextStmt->getAttribute('comments', []);
                if ($currentComments !== []) {
                    continue;
                }

                if ($nextComments !== []) {
                    continue;
                }

                // Only handle same variable - different variables should use ChainDifferentVariableExpectCallsRector
                if (! $this->nodeComparator->areNodesEqual($firstExpectArg, $nextExpectArg)) {
                    continue;
                }

                $this->mergeSameVariable($stmts, $key);

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

    /**
     * @param array<Node\Stmt> $stmts
     */
    private function mergeSameVariable(array &$stmts, int $key): void
    {
        /** @var Expression $exprStmt */
        $exprStmt = $stmts[$key];
        /** @var Expression $nextExprStmt */
        $nextExprStmt = $stmts[$key + 1];

        $first = $exprStmt->expr;
        $second = $nextExprStmt->expr;

        /** @var MethodCall $first */
        /** @var MethodCall $second */
        $exprStmt->expr = $this->buildChainedCall($first, $second);

        // preserve comments from the removed statement(s)
        $collectedComments = (array) $exprStmt->getAttribute('comments', []);
        $collectedComments = array_merge($collectedComments, (array) $nextExprStmt->getAttribute('comments', []));

        unset($stmts[$key + 1]);
        $stmts = array_values($stmts);

        if ($collectedComments !== []) {
            $filtered = array_values(array_filter($collectedComments, function ($c): bool {
                if (! is_object($c)) {
                    return false;
                }

                if (method_exists($c, 'getText')) {
                    $text = $c->getText();
                    return is_string($text) && trim($text) !== '';
                }

                return true;
            }));

            if ($filtered !== []) {
                $exprStmt->setAttribute('comments', $filtered);
            }
        }
    }
}
