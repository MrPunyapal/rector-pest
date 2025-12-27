<?php

// Skip: Different variables (should use ChainDifferentVariableExpectCallsRector)
expect($a)->toBe(10);
expect($b)->toBe(20);

// Skip: No expect() calls
$result = $a + $b;

// Skip: Single expect call
expect($single)->toBeTrue();
