<?php

it('becomes pending', function (): void {});

test('keeps chains', function (): void {
})->group('pending');

describe('nested tests', function (): void {
    it('also becomes pending', function (): void {
    });

    it('keeps assertions', function (): void {
        expect($this->app)->not->toBeNull();
    });

    test('keeps comments in placeholders', function (): void {
        // still drafting this test
    });
});

it('already pending');

?>
-----
<?php

it('becomes pending');

test('keeps chains')->group('pending');

describe('nested tests', function (): void {
    it('also becomes pending');

    it('keeps assertions', function (): void {
        expect($this->app)->not->toBeNull();
    });

    test('keeps comments in placeholders', function (): void {
        // still drafting this test
    });
});

it('already pending');

?>
