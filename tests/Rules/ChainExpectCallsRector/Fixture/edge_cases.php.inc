<?php

expect($router->getMiddlewareGroups())->toHaveKey('web');
expect($router->getMiddlewareGroups()['web'])->toContain(AuthenticateJobs::class);

expect($router->getMiddlewareGroups())->toHaveKey('abc');
expect($router->getMiddlewareGroups()['abc'])->toContain(AuthenticateJobs::class);

-----
<?php

expect($router->getMiddlewareGroups())->toHaveKey('web')->and($router->getMiddlewareGroups()['web'])->toContain(AuthenticateJobs::class)->and($router->getMiddlewareGroups())->toHaveKey('abc')->and($router->getMiddlewareGroups()['abc'])->toContain(AuthenticateJobs::class);
