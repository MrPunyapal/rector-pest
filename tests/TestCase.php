<?php

namespace Tests;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

abstract class TestCase extends AbstractRectorTestCase
{
    public static string $configFilePath = '';

    public function provideConfigFilePath(): string
    {
        return self::$configFilePath;
    }
}
