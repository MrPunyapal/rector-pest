<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToHaveKeyRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToHaveKeyRector::class]);
