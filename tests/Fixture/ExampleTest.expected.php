<?php

// This file shows the expected output after applying Rector Pest rules
// to the ExampleTest.php fixture

namespace MrPunyapal\RectorPest\Tests\Fixture;

beforeEach(function () {
    $this->value = 42;
});

it('works', function () {
    expect(true)->toBeTrue();
    expect(false)->toBeFalse();
});

test('user can login', function () {
    $expected = 'user@example.com';
    $actual = 'user@example.com';
    
    expect($actual)->toEqual($expected);
    expect($actual)->toBe($expected);
});

test('array operations', function () {
    $array = [1, 2, 3];
    
    expect($array)->toBeArray();
    expect($array)->toHaveCount(3);
    expect($array)->toContain(2);
});

test('null values', function () {
    $value = null;
    
    expect($value)->toBeNull();
    expect([])->toBeEmpty();
});

afterEach(function () {
    $this->value = null;
});
