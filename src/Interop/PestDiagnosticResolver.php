<?php

declare(strict_types=1);

namespace RectorPest\Interop;

use RectorPest\Registry\PestSemanticIssues;
use RectorPest\ValueObject\PestSemanticIssue;

/**
 * Resolves machine-readable diagnostic identifiers to canonical semantic issues.
 */
final class PestDiagnosticResolver
{
    /**
     * @return array<string, PestSemanticIssue>
     */
    private function identifierMap(): array
    {
        $identifierMap = [];

        foreach (PestSemanticIssues::all() as $issue) {
            foreach ($issue->allDiagnosticIdentifiers() as $identifier) {
                $identifierMap[$identifier] = $issue;
            }
        }

        return $identifierMap;
    }

    public function resolve(string $diagnosticIdentifier): ?PestSemanticIssue
    {
        return $this->identifierMap()[$diagnosticIdentifier] ?? null;
    }

    public function canonicalize(string $diagnosticIdentifier): ?string
    {
        return $this->resolve($diagnosticIdentifier)?->identifier;
    }

    public function supports(string $diagnosticIdentifier): bool
    {
        return $this->resolve($diagnosticIdentifier) instanceof PestSemanticIssue;
    }

    /**
     * @param list<string> $diagnosticIdentifiers
     * @return list<PestSemanticIssue>
     */
    public function resolveAll(array $diagnosticIdentifiers): array
    {
        $resolvedIssues = [];

        foreach ($diagnosticIdentifiers as $diagnosticIdentifier) {
            $issue = $this->resolve($diagnosticIdentifier);

            if (! $issue instanceof PestSemanticIssue) {
                continue;
            }

            $resolvedIssues[$issue->identifier] = $issue;
        }

        return array_values($resolvedIssues);
    }

    /**
     * @return list<string>
     */
    public function supportedDiagnosticIdentifiers(): array
    {
        return array_keys($this->identifierMap());
    }
}
