<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\Pest2ToPest3\TapToDeferRector;
use MrPunyapal\RectorPest\Rules\Pest2ToPest3\ToHaveMethodOnClassRector;
use Rector\Config\RectorConfig;

/**
 * @see https://pestphp.com/docs/upgrade-guide#content-from-pest-v2-to-pest-v3
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->rule(TapToDeferRector::class);

    $rectorConfig->rule(ToHaveMethodOnClassRector::class);
};
