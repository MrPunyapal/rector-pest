<?php

expect($value1)->toBeInt();
expect($value1)->toBe(10);
expect($value2)->toBeString();
expect($value2)->toBe('hello');

-----
<?php

expect($value1)->toBeInt()
    ->toBe(10)
    ->and($value2)
    ->toBeString()
    ->toBe('hello');
