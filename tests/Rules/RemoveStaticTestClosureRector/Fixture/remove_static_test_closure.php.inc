<?php

it('uses the test case instance', static function (): void {
    expect($this->app)->not->toBeNull();
});

describe('nested hooks', static function (): void {
    beforeEach(static function (): void {
        expect($this->app)->not->toBeNull();

        expect(['first', 'second'])
            ->sequence(
                static fn ($value) => $value->toBe('first'),
                static fn ($value) => $value->toBe('second'),
            );
    });

    it('keeps chained expectations intact', static function (): void {
        expect('pest')
            ->toBeString()
            ->and('rector')
            ->toBeString();
    });
});

test('already valid callback stays the same', function (): void {
    expect(true)->toBeTrue();
});

array_map(static function (string $value): string {
    return strtoupper($value);
}, ['a', 'b']);

?>
-----
<?php

it('uses the test case instance', function (): void {
    expect($this->app)->not->toBeNull();
});

describe('nested hooks', static function (): void {
    beforeEach(function (): void {
        expect($this->app)->not->toBeNull();

        expect(['first', 'second'])
            ->sequence(
                static fn ($value) => $value->toBe('first'),
                static fn ($value) => $value->toBe('second'),
            );
    });

    it('keeps chained expectations intact', static function (): void {
        expect('pest')
            ->toBeString()
            ->and('rector')
            ->toBeString();
    });
});

test('already valid callback stays the same', function (): void {
    expect(true)->toBeTrue();
});

array_map(static function (string $value): string {
    return strtoupper($value);
}, ['a', 'b']);

?>