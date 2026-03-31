<?php

test('with dataset', function (string $email) {
    expect($email)->toContain('@');
})->with(['test@test.com', 'user@domain.com']);

?>
-----
<?php

test('with dataset', fn(string $email) => expect($email)->toContain('@'))->with(['test@test.com', 'user@domain.com']);

?>
