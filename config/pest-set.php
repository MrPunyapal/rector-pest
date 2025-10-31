<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use MrPunyapal\RectorPest\PestSetList;

return static function (RectorConfig $rectorConfig): void {
    PestSetList::configure($rectorConfig);
};
