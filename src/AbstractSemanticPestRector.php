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
}
