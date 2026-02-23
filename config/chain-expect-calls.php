<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * Related config for ChainExpectCallsRector.
 *
 * Automatically imported when ChainExpectCallsRector is registered.
 * Enables newline formatting for chained fluent calls so that
 * ->and() chains are printed on separate lines for readability.
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->newLineOnFluentCall();
};
