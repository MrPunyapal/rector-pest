<?php

declare(strict_types=1);

namespace RectorPest\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use RectorPest\AbstractSemanticPestRector;
use RectorPest\Analyzer\SemanticExpectationAnalyzer;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\ValueObject\ExpectationSemanticAnalysis;
use RectorPest\ValueObject\PestSemanticIssue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoveRedundantLiteralTypeExpectationRector extends AbstractSemanticPestRector
{
    private ?string $currentFileContents = null;

    // @codeCoverageIgnoreStart
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes redundant literal type expectations when a later matcher keeps the chain meaningful',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
expect('pest')
    ->toBeString()
    ->toStartWith('p');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
expect('pest')
    ->toStartWith('p');
CODE_SAMPLE
                ),
            ]
        );
    }

    // @codeCoverageIgnoreEnd

    public function getSemanticIssue(): PestSemanticIssue
    {
        return PestSemanticIssues::redundantExpectation();
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
        if (! $this->isExpectChain($node) || ! $node->var instanceof MethodCall) {
            return null;
        }

        return $this->removeRedundantMatchersFromChain($node);
    }

    private function removeRedundantMatchersFromChain(MethodCall $node): ?MethodCall
    {
        $hasChanged = false;
        $current = $node;

        while ($current->var instanceof MethodCall) {
            $inner = $current->var;
            $analysis = SemanticExpectationAnalyzer::analyzeLiteralTypeMatcher($inner);

            if (! $analysis instanceof ExpectationSemanticAnalysis || ! $analysis->isRedundant() || ! $this->canRemoveFromChain($current, $inner, $analysis)) {
                $current = $inner;

                continue;
            }

            $current->var = $this->unwrapRedundantExpectation($inner, $analysis);
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    private function canRemoveFromChain(MethodCall $current, MethodCall $inner, ExpectationSemanticAnalysis $analysis): bool
    {
        if (! $current->name instanceof Identifier || $current->name->toString() === 'and') {
            return false;
        }

        if ($this->nodeOrParentsHaveComments($current) || $this->nodeOrParentsHaveComments($inner) || $this->nodeOrDescendantsHaveComments($inner) || $this->hasInlineCommentBetween($inner, $current)) {
            return false;
        }

        return ! $analysis->negated
            || ! $inner->var instanceof PropertyFetch
            || $inner->var->getComments() === [];
    }

    private function nodeOrParentsHaveComments(Node $node): bool
    {
        $current = $node;

        while ($current instanceof Node) {
            if ($current->getComments() !== []) {
                return true;
            }

            $current = $current->getAttribute('parent');
        }

        return false;
    }

    private function nodeOrDescendantsHaveComments(mixed $node): bool
    {
        if ($node instanceof Node) {
            if ($node->getComments() !== []) {
                return true;
            }

            foreach ($node->getSubNodeNames() as $subNodeName) {
                if ($this->nodeOrDescendantsHaveComments($node->{$subNodeName})) {
                    return true;
                }
            }

            return false;
        }

        if (! is_array($node)) {
            return false;
        }

        foreach ($node as $item) {
            if ($this->nodeOrDescendantsHaveComments($item)) {
                return true;
            }
        }

        return false;
    }

    private function hasInlineCommentBetween(MethodCall $inner, MethodCall $current): bool
    {
        $startFilePos = $inner->getStartFilePos();
        $endFilePos = $current->getEndFilePos();

        if ($startFilePos < 0 || $endFilePos < $startFilePos) {
            return false;
        }

        $segment = substr($this->getCurrentFileContents(), $startFilePos, $endFilePos - $startFilePos + 1);

        return str_contains($segment, '//') || str_contains($segment, '/*');
    }

    private function getCurrentFileContents(): string
    {
        return $this->currentFileContents ??= (string) file_get_contents($this->file->getFilePath());
    }

    private function unwrapRedundantExpectation(MethodCall $methodCall, ExpectationSemanticAnalysis $analysis): Expr
    {
        if (! $analysis->negated || ! $methodCall->var instanceof PropertyFetch) {
            return $methodCall->var;
        }

        return $methodCall->var->var;
    }
}
