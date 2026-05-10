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
        public string $identifier,
        public string $defaultMessage,
        public array $diagnosticIdentifiers,
        public string $category,
        public string $fixCategory,
        public string $fixability,
        public string $severity,
        public string $safetyLevel,
        public string $confidence,
    ) {
    }

    public function matchesDiagnosticIdentifier(string $diagnosticIdentifier): bool
    {
        return in_array($diagnosticIdentifier, $this->diagnosticIdentifiers, true);
    }

    public function isAutoFixable(): bool
    {
        return $this->fixability === PestSemanticFixability::AUTO_FIXABLE;
    }
}
