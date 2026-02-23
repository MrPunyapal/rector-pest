<?php

expect($value)->toBe(10);
expect($value)->toBeInt();
expect($value)->toBeGreaterThan(5);

-----
<?php

expect($value)
    ->toBeInt()
    ->toBe(10)
    ->toBeGreaterThan(5);
