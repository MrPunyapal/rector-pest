<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Enum\NodeGroup;
use RectorPest\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Converts PHPUnit @dataProvider methods to Pest with() datasets
 */
final class ConvertDataProviderToDatasetRector extends AbstractRector
{
    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts PHPUnit @dataProvider annotations to Pest with() datasets',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
/**
 * @dataProvider emailProvider
 */
test('validates email', function (string $email, bool $valid) {
    expect(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)->toBe($valid);
});

function emailProvider(): array
{
    return [
        ['test@test.com', true],
        ['invalid', false],
    ];
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
test('validates email', function (string $email, bool $valid) {
    expect(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)->toBe($valid);
})->with([
    ['test@test.com', true],
    ['invalid', false],
]);
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

        $providerMap = $this->collectProviderFunctions($node->stmts);
        if ($providerMap === []) {
            return null;
        }

        $hasChanged = false;
        $removableProviders = [];
        $newStmts = [];

        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                $newStmts[] = $stmt;

                continue;
            }

            $result = $this->processTestStatement($stmt, $providerMap, $removableProviders);
            if ($result !== null) {
                $newStmts[] = $result;
                $hasChanged = true;

                continue;
            }

            $newStmts[] = $stmt;
        }

        if (! $hasChanged) {
            return null;
        }

        $node->stmts = $this->removeProviderFunctions($newStmts, $removableProviders);

        return $node;
    }

    /**
     * Collect all provider functions: name => array data
     *
     * @param array<Node\Stmt> $stmts
     * @return array<string, Array_>
     */
    private function collectProviderFunctions(array $stmts): array
    {
        $providers = [];

        foreach ($stmts as $stmt) {
            if (! $stmt instanceof Node\Stmt\Function_) {
                continue;
            }

            $name = $this->getName($stmt);
            if ($name === null) {
                continue;
            }

            $returnArray = $this->extractReturnArray($stmt);
            if ($returnArray === null) {
                continue;
            }

            $providers[$name] = $returnArray;
        }

        return $providers;
    }

    private function extractReturnArray(Node\Stmt\Function_ $function): ?Array_
    {
        if (count($function->stmts) !== 1) {
            return null;
        }

        $stmt = $function->stmts[0];
        if (! $stmt instanceof Return_) {
            return null;
        }

        if (! $stmt->expr instanceof Array_) {
            return null;
        }

        return $stmt->expr;
    }

    /**
     * Process a test statement to attach ->with() if it has @dataProvider
     *
     * @param array<string, Array_> $providerMap
     * @param array<string> $removableProviders
     */
    private function processTestStatement(
        Expression $stmt,
        array $providerMap,
        array &$removableProviders
    ): ?Expression {
        $expr = $stmt->expr;

        if ($expr instanceof MethodCall) {
            $rootFuncCall = $this->findRootFuncCall($expr);
        } elseif ($expr instanceof FuncCall) {
            $rootFuncCall = $expr;
        } else {
            return null;
        }

        if ($rootFuncCall === null) {
            return null;
        }

        if (! $this->isNames($rootFuncCall, ['test', 'it'])) {
            return null;
        }

        $providerName = $this->extractDataProviderFromDocBlock($stmt);
        if ($providerName === null) {
            return null;
        }

        if (! isset($providerMap[$providerName])) {
            return null;
        }

        $dataArray = $providerMap[$providerName];
        $removableProviders[] = $providerName;

        $withCall = new MethodCall($expr, 'with', [new Arg($dataArray)]);

        $this->removeDataProviderDocBlock($stmt);

        return new Expression($withCall);
    }

    private function findRootFuncCall(MethodCall $methodCall): ?FuncCall
    {
        $current = $methodCall;

        while ($current instanceof MethodCall) {
            $current = $current->var;
        }

        if ($current instanceof FuncCall) {
            return $current;
        }

        return null;
    }

    private function extractDataProviderFromDocBlock(Expression $stmt): ?string
    {
        $docComment = $stmt->getDocComment();
        if ($docComment === null) {
            return null;
        }

        $text = $docComment->getText();
        if (preg_match('/@dataProvider\s+(\w+)/', $text, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function removeDataProviderDocBlock(Expression $stmt): void
    {
        $stmt->setAttribute('comments', []);
    }

    /**
     * Remove provider functions that were inlined
     *
     * @param array<Node\Stmt> $stmts
     * @param array<string> $providerNames
     * @return array<Node\Stmt>
     */
    private function removeProviderFunctions(array $stmts, array $providerNames): array
    {
        return array_values(array_filter($stmts, function (Node\Stmt $stmt) use ($providerNames): bool {
            if (! $stmt instanceof Node\Stmt\Function_) {
                return true;
            }

            $name = $this->getName($stmt);

            return ! in_array($name, $providerNames, true);
        }));
    }
}
