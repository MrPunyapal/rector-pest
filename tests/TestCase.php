<?php

namespace Tests;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

abstract class TestCase extends AbstractRectorTestCase
{
    public string $configFilePath = '';

    public function provideConfigFilePath(): string
    {
        return $this->configFilePath;
    }
}
