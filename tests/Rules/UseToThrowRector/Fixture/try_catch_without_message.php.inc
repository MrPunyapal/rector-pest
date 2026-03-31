<?php

try {
    riskyOperation();
} catch (\InvalidArgumentException $e) {
}

?>
-----
<?php

expect(fn() => riskyOperation())->toThrow(\InvalidArgumentException::class);

?>
