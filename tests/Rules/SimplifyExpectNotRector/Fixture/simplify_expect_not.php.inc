<?php

expect(!$condition)->toBeTrue();
expect(!$value)->toBe(false);
expect(!empty($array))->toBeTrue();
expect(!is_null($variable))->toBeTrue();

?>
-----
<?php

expect($condition)->not->toBeTrue();
expect($value)->not->toBe(false);
expect(empty($array))->not->toBeTrue();
expect(is_null($variable))->not->toBeTrue();

?>