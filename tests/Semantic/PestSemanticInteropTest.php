<?php

declare(strict_types=1);

use RectorPest\Interop\PestDiagnosticResolver;
use RectorPest\Interop\SemanticIssueMapper;
use RectorPest\Registry\PestSemanticIssues;
use RectorPest\Rules\ConvertBeforeAllInDescribeRector;
use RectorPest\Rules\FixInvalidRepeatValueRector;
use RectorPest\ValueObject\PestSemanticFixability;
use RectorPest\ValueObject\PestSemanticSafetyLevel;
use RectorPest\ValueObject\PestSemanticSeverity;

it('exposes canonical metadata for invalid repeat values', function (): void {
    $issue = PestSemanticIssues::invalidRepeatValue();

    expect($issue->identifier)->toBe(PestSemanticIssues::INVALID_REPEAT_VALUE);
    expect($issue->defaultMessage)->toContain('greater than 0');
    expect($issue->severity)->toBe(PestSemanticSeverity::ERROR);
    expect($issue->fixability)->toBe(PestSemanticFixability::AUTO_FIXABLE);
    expect($issue->safetyLevel)->toBe(PestSemanticSafetyLevel::SAFE);
    expect($issue->isAutoFixable())->toBeTrue();
});

it('resolves machine-readable diagnostics to canonical semantic issues', function (): void {
    $resolver = new PestDiagnosticResolver();

    $issue = $resolver->resolve(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);

    expect($issue)->not->toBeNull();
    expect($issue?->identifier)->toBe(PestSemanticIssues::AFTER_ALL_IN_DESCRIBE);
});

it('maps diagnostics to semantic fix candidates', function (): void {
    $mapper = new SemanticIssueMapper();

    $describeCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE);
    $repeatCandidates = $mapper->resolveCandidatesForDiagnostic(PestSemanticIssues::INVALID_REPEAT_VALUE);

    expect($describeCandidates)->toHaveCount(1);
    expect($describeCandidates[0]->rectorClass)->toBe(ConvertBeforeAllInDescribeRector::class);
    expect($describeCandidates[0]->matchedDiagnosticIdentifier)->toBe(PestSemanticIssues::BEFORE_ALL_IN_DESCRIBE);

    expect($repeatCandidates)->toHaveCount(1);
    expect($repeatCandidates[0]->rectorClass)->toBe(FixInvalidRepeatValueRector::class);
    expect($repeatCandidates[0]->issue->identifier)->toBe(PestSemanticIssues::INVALID_REPEAT_VALUE);
});
