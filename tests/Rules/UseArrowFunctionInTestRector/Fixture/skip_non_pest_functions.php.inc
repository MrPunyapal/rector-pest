<?php

someOtherFunction('test', function () {
    return true;
});

$callback = function () {
    expect(true)->toBeTrue();
};

?>
