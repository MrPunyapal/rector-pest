<?php

declare(strict_types=1);

namespace RectorPest\Interop;

use RectorPest\AbstractSemanticPestRector;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Rules\ConvertBeforeAllInDescribeRector;
use RectorPest\Rules\FixInvalidRepeatValueRector;
use RectorPest\Rules\RemoveStaticTestClosureRector;
use RectorPest\ValueObject\PestSemanticIssue;
use RectorPest\ValueObject\SemanticFixCandidate;

/**
 * Maps canonical semantic issues to the Rector rules that can safely address them.
 */
final readonly class SemanticIssueMapper
{
    /**
     * @var array<string, list<class-string<AbstractSemanticPestRector>>>
     */
    private const RULE_MAP = [
        PestSemanticIssues::STATIC_TEST_CLOSURE => [RemoveStaticTestClosureRector::class],
        PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE => [ConvertBeforeAllInDescribeRector::class],
        PestSemanticIssues::AFTER_ALL_IN_DESCRIBE => [ConvertBeforeAllInDescribeRector::class],
        PestSemanticIssues::INVALID_REPEAT_VALUE => [FixInvalidRepeatValueRector::class],
    ];

    public function __construct(
        private PestDiagnosticResolver $diagnosticResolver = new PestDiagnosticResolver(),
    ) {
    }

    /**
     * @return list<SemanticFixCandidate>
     */
    public function resolveCandidatesForDiagnostic(string $diagnosticIdentifier): array
    {
        $issue = $this->diagnosticResolver->resolve($diagnosticIdentifier);
        if (!$issue instanceof PestSemanticIssue) {
            return [];
        }

        return $this->resolveCandidatesForIssue($issue->identifier, $diagnosticIdentifier);
    }

    /**
     * @return list<SemanticFixCandidate>
     */
    public function resolveCandidatesForIssue(string $issueIdentifier, ?string $matchedDiagnosticIdentifier = null): array
    {
        $issue = PestSemanticIssues::get($issueIdentifier);
        if (!$issue instanceof PestSemanticIssue) {
            return [];
        }

        $rectorClasses = self::RULE_MAP[$issueIdentifier] ?? [];
        $diagnosticIdentifier = $matchedDiagnosticIdentifier ?? $issue->identifier;

        return array_map(
            static fn (string $rectorClass): SemanticFixCandidate => new SemanticFixCandidate(
                $issue,
                $rectorClass,
                $diagnosticIdentifier,
            ),
            $rectorClasses,
        );
    }
}
