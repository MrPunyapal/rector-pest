<?php

declare(strict_types=1);

namespace RectorPest;

use RectorPest\ValueObject\PestSemanticIssue;

/**
 * Base class for Rectors that align with Pest semantic diagnostics.
 */
abstract class AbstractSemanticPestRector extends AbstractRector
{
    abstract public function getSemanticIssue(): PestSemanticIssue;

    final public function getSemanticIdentifier(): string
    {
        return $this->getSemanticIssue()->identifier;
    }

    final public function getSemanticCategory(): string
    {
        return $this->getSemanticIssue()->category;
    }

    final public function getSemanticFixCategory(): string
    {
        return $this->getSemanticIssue()->fixCategory;
    }

    final public function getSemanticSafetyLevel(): string
    {
        return $this->getSemanticIssue()->safetyLevel;
    }

    final public function getSemanticFixability(): string
    {
        return $this->getSemanticIssue()->fixability;
    }

    final public function getSemanticSeverity(): string
    {
        return $this->getSemanticIssue()->severity;
    }

    final public function getSemanticFixabilityConfidence(): string
    {
        return $this->getSemanticIssue()->confidence;
    }
}
