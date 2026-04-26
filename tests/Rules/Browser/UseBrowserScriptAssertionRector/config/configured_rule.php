<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorPest\Rules\Browser\UseBrowserScriptAssertionRector;

return RectorConfig::configure()
    ->withRules([UseBrowserScriptAssertionRector::class]);
