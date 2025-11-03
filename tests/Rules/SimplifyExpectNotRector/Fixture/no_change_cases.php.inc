<?php

// Cases where SimplifyExpectNotRector should NOT make changes

expect($condition)->toBeTrue();
expect($value)->toBe(10);
expect($array)->toHaveCount(5);

expect($condition)->not->toBeTrue();
expect($value)->not->toBe(false);

expect()->toBeTrue();

$object->method(!$condition)->toBeTrue();

expect($value, 'message')->toBe(10);

expect($a && !$b)->toBeTrue();
expect(!$a || $b)->toBeTrue();

expect(someFunction(!$condition))->toBeTrue();

?>
