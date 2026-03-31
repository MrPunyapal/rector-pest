<?php

try {
    doSomething();
} catch (RuntimeException $e) {
    log($e);
    expect($e->getMessage())->toBe('error');
}

try {
    doSomething();
} catch (RuntimeException $e) {
    expect($e->getMessage())->toBe('error');
} finally {
    cleanup();
}

try {
    doSomething();
} catch (RuntimeException $e) {
    expect($e->getMessage())->toBe('error');
} catch (InvalidArgumentException $e) {
    expect($e->getMessage())->toBe('other');
}

?>
