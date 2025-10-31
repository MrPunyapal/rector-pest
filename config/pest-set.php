<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use MrPunyapal\RectorPest\Rules\ConvertAssertToExpectRule;
use MrPunyapal\RectorPest\Rules\ConvertTestMethodToPestFunctionRule;
use MrPunyapal\RectorPest\Rules\ConvertSetUpToBeforeEachRule;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ConvertAssertToExpectRule::class);
    $rectorConfig->rule(ConvertTestMethodToPestFunctionRule::class);
    $rectorConfig->rule(ConvertSetUpToBeforeEachRule::class);
};
