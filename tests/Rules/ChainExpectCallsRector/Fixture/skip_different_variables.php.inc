<?php

test('different variables should not chain', function () {
    $value1 = 10;
    $value2 = 20;
    
    expect($value1)->toBe(10);
    expect($value2)->toBe(20);
});

?>
