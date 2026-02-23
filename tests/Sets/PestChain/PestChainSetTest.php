<?php

declare(strict_types=1);

use Rector\Testing\Fixture\FixtureFileFinder;

beforeAll(function (): void {
    self::$configFilePath = __DIR__ . '/config/configured_set.php';
});

test('', function (string $filePath): void {
    $this->doTestFile($filePath);
})->with(
    fn (): Iterator => FixtureFileFinder::yieldDirectory(__DIR__ . '/Fixture')
);
