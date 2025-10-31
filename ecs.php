<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
    ]);

    $ecsConfig->skip([
        __DIR__ . '/tests/Fixture',
    ]);

    $ecsConfig->sets([
        __DIR__ . '/vendor/symplify/easy-coding-standard/config/set/psr12.php',
    ]);

    $ecsConfig->rules([
        NoUnusedImportsFixer::class,
        ArrayIndentationFixer::class,
    ]);

    $ecsConfig->ruleWithConfiguration(BinaryOperatorSpacesFixer::class, [
        'operators' => [
            '=>' => 'align_single_space_minimal',
        ],
    ]);
};
