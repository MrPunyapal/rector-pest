<?php

declare(strict_types=1);

namespace MrPunyapal\RectorPest;

use MrPunyapal\RectorPest\Rules\ConvertAssertToExpectRule;
use MrPunyapal\RectorPest\Rules\ConvertSetUpToBeforeEachRule;
use MrPunyapal\RectorPest\Rules\ConvertTestMethodToPestFunctionRule;
use Rector\Config\RectorConfig;

/**
 * Pest Rule Set for Rector
 * 
 * This class provides a convenient way to register all Pest-related Rector rules.
 */
final class PestSetList
{
    /**
     * Register all Pest rules with the Rector configuration
     */
    public static function configure(RectorConfig $rectorConfig): void
    {
        $rectorConfig->rule(ConvertAssertToExpectRule::class);
        $rectorConfig->rule(ConvertTestMethodToPestFunctionRule::class);
        $rectorConfig->rule(ConvertSetUpToBeforeEachRule::class);
    }
}
