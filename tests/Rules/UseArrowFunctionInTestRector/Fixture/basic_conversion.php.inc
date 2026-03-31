<?php

test('single expression', function () {
    expect(true)->toBeTrue();
});

it('returns value', function () {
    expect($this->service->get())->toBe('value');
});

?>
-----
<?php

test('single expression', fn() => expect(true)->toBeTrue());

it('returns value', fn() => expect($this->service->get())->toBe('value'));

?>
