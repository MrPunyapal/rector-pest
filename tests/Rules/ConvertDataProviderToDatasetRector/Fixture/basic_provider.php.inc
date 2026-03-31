<?php

/**
 * @dataProvider emailProvider
 */
test('validates email', function (string $email, bool $valid) {
    expect(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)->toBe($valid);
});

function emailProvider(): array
{
    return [
        ['test@test.com', true],
        ['invalid', false],
    ];
}

?>
-----
<?php

test('validates email', function (string $email, bool $valid) {
    expect(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)->toBe($valid);
})->with([
    ['test@test.com', true],
    ['invalid', false],
]);
