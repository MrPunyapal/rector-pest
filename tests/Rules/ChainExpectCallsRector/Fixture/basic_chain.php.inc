<?php

test('basic example', function () {
    $value = 10;

    expect($value)->toBe(10);
    expect($value)->toBeInt();
});

?>
-----
<?php

test('basic example', function () {
    $value = 10;

    expect($value)->toBe(10)->and($value)->toBeInt();
});

?>
