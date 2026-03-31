<?php

test('multiple statements', function () {
    $user = createUser();
    expect($user->name)->toBe('John');
});

test('with setup and assertion', function () {
    $result = calculate(1, 2);
    expect($result)->toBe(3);
});

beforeEach(function () {
    $this->service = new Service();
});

?>
