<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

use RectorPest\AbstractSemanticPestRector;

/**
 * Represents a possible Rector-based fix for a semantic issue.
 */
final readonly class SemanticFixCandidate
{
    /**
     * @param class-string<AbstractSemanticPestRector> $rectorClass
     */
    public function __construct(
        public PestSemanticIssue $issue,
        public string $rectorClass,
        public string $matchedDiagnosticIdentifier,
    ) {
    }
}
