<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\Pest2ToPest3\TapToDeferRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TapToDeferRector::class);
};
