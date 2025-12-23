<?php

// Test consecutive statements that should not be merged
// First is not an expression
$value = 10;
expect($value)->toBe(10);

// Non-expression statement between expects
throw new Exception('test');
expect($value)->toBeInt();
