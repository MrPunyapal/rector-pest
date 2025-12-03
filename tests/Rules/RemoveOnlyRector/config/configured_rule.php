<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\RemoveOnlyRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RemoveOnlyRector::class);
};
