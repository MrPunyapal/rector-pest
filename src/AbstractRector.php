<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest;

use Rector\Rector\AbstractRector as BaseAbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;

/**
 * Base abstract class for all Pest rectors
 * Extends Rector's AbstractRector and implements DocumentedRuleInterface
 */
abstract class AbstractRector extends BaseAbstractRector implements DocumentedRuleInterface
{
}
