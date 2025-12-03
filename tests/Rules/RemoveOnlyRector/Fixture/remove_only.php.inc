<?php

test('description', function () {
    expect(true)->toBeTrue();
})->only();

?>
-----
<?php

test('description', function () {
    expect(true)->toBeTrue();
});

?>
