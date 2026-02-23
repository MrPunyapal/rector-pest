<?php

expect($value1)->toBe(10);
expect($value2)->toBe(20);
expect($value3)->toBe(30);

-----
<?php

expect($value1)->toBe(10)
    ->and($value2)
    ->toBe(20)
    ->and($value3)
    ->toBe(30);
