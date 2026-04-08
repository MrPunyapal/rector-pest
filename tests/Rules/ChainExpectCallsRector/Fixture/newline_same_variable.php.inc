<?php

// 2 matchers — second goes on new line
expect($value)->toBeString();
expect($value)->toBeGreaterThan('a');

// 3 matchers — all extras go on new lines
expect($counter)->toBe(10);
expect($counter)->toBeInt();
expect($counter)->toBeGreaterThan(5);

// ->not->toBeX() as first matcher (unit stays inline), extras on new lines
expect($name)->not->toBeNull();
expect($name)->toBeString();
expect($name)->toBeGreaterThan('a');

// already-inline chain extended with a new same-var matcher
// original tokens are preserved verbatim; only the new node gets newline
expect($title)->toBe('hello')->toBeString();
expect($title)->toBeGreaterThan('a');

// inside a test() closure — indent is 8 spaces (4 closure + 4 chain)
test('has correct values', function () {
    expect($score)->toBe(10);
    expect($score)->toBeInt();
    expect($score)->toBeGreaterThan(5);
});

-----
<?php

// 2 matchers — second goes on new line
expect($value)->toBeString()
    ->toBeGreaterThan('a');

// 3 matchers — all extras go on new lines
expect($counter)->toBe(10)
    ->toBeInt()
    ->toBeGreaterThan(5);

// ->not->toBeX() as first matcher (unit stays inline), extras on new lines
expect($name)->not->toBeNull()
    ->toBeString()
    ->toBeGreaterThan('a');

// already-inline chain extended with a new same-var matcher
// original tokens are preserved verbatim; only the new node gets newline
expect($title)->toBe('hello')->toBeString()
    ->toBeGreaterThan('a');

// inside a test() closure — indent is 8 spaces (4 closure + 4 chain)
test('has correct values', function () {
    expect($score)->toBe(10)
        ->toBeInt()
        ->toBeGreaterThan(5);
});
