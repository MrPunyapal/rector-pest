<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/**
 * Base configuration for rector-pest
 * Import this in your set configurations
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpstanConfig('vendor/mrpunyapal/peststan/extension.neon');
};
