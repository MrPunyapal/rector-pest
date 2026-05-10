<?php

declare(strict_types=1);

namespace RectorPest\ValueObject;

/**
 * Metadata describing a semantic issue that can be auto-fixed by a Rector rule.
 */
final readonly class PestSemanticIssue
{
    /**
     * @param non-empty-list<string> $diagnosticIdentifiers
     */
    public function __construct(
        public array $diagnosticIdentifiers,
        public string $category,
        public string $summary,
    ) {
    }
}
