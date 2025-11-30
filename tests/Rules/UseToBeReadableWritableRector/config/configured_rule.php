<?php

declare(strict_types=1);

use MrPunyapal\RectorPest\Rules\UseToBeReadableWritableRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([UseToBeReadableWritableRector::class]);
