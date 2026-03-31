<?php

/**
 * @dataProvider complexProvider
 */
test('complex', function ($a, $b) {
    expect($a)->toBe($b);
});

function complexProvider(): array
{
    $items = compute();
    return $items;
}

?>
