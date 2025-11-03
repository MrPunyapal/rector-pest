<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\ChainExpectCallsRector;
use MrPunyapal\RectorPest\Rules\SimplifyExpectNotRector;
use Rector\Config\RectorConfig;

/**
 * Code quality improvements for Pest tests
 *
 * This set will contain rules for:
 * - Better test readability and expressiveness
 * - Removing redundant code in tests
 * - Using more expressive Pest APIs (expect, toBe, etc.)
 * - Type safety improvements
 * - Dataset optimizations
 * - Better assertion methods
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    // Chain multiple expect() calls using and()
    $rectorConfig->rule(ChainExpectCallsRector::class);

    // Simplify negated expectations using not() modifier
    $rectorConfig->rule(SimplifyExpectNotRector::class);
};
