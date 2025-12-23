<?php

// Skip non-expect chains  
$value = false;

// Skip non-toBeTrue methods for toBeFalse match
expect(false)->toBeString();
