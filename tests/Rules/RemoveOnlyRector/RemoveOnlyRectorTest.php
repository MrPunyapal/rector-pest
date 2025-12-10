<?php

declare(strict_types=1);
use Rector\Testing\Fixture\FixtureFileFinder;

beforeAll(function () {
    self::$configFilePath = __DIR__ . '/config/configured_rule.php';
});

test('', function (string $filePath) {
    $this->doTestFile($filePath);
})->with(function (): Iterator {
    return FixtureFileFinder::yieldDirectory(__DIR__ . '/Fixture');
});
