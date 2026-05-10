<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

use RectorPest\Registry\PestSemanticIssues;

final readonly class ExpectationSemanticAnalysis
{
    public function __construct(
        public string $issueIdentifier,
        public string $matcher,
        public bool $negated,
    ) {
    }

    public static function redundant(string $matcher, bool $negated): self
    {
        return new self(PestSemanticIssues::REDUNDANT_EXPECTATION, $matcher, $negated);
    }

    public static function impossible(string $matcher, bool $negated): self
    {
        return new self(PestSemanticIssues::IMPOSSIBLE_EXPECTATION, $matcher, $negated);
    }

    public function isRedundant(): bool
    {
        return $this->issueIdentifier === PestSemanticIssues::REDUNDANT_EXPECTATION;
    }

    public function isImpossible(): bool
    {
        return $this->issueIdentifier === PestSemanticIssues::IMPOSSIBLE_EXPECTATION;
    }
}
