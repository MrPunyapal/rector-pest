<?php

try {
    doSomething();
} catch (RuntimeException $e) {
    expect($e->getMessage())->toBe('error');
}

?>
-----
<?php

expect(fn() => doSomething())->toThrow(RuntimeException::class, 'error');

?>
