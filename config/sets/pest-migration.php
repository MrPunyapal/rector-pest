<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\ConvertAssertToExpectRector;

/**
 * PHPUnit to Pest migration rules
 *
 * This set contains rules for converting PHPUnit test patterns to Pest equivalents.
 * These are structural transformations and should be reviewed after application.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    // PHPUnit assertion to Pest expect() conversion
    $rectorConfig->rule(ConvertAssertToExpectRector::class);
};
