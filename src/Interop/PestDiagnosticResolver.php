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
    public function resolve(string $diagnosticIdentifier): ?PestSemanticIssue
    {
        foreach (PestSemanticIssues::all() as $issue) {
            if ($issue->matchesDiagnosticIdentifier($diagnosticIdentifier)) {
                return $issue;
            }
        }

        return null;
    }
}
