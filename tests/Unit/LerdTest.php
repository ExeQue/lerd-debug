<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Color;
use Lerd\Debug\Entry;
use Lerd\Debug\Lerd;
use RuntimeException;

use function expect;
use function it;
use function json_decode;
use function json_encode;

it('returns what a measured callback returns', function () {
    expect(Lerd::timeline()->measure('answer', fn () => 42, 'maths', Color::Teal))->toBe(42);
});

it('lets an exception from a measured callback through', function () {
    Lerd::timeline()->measure('boom', fn () => throw new RuntimeException('boom'));
})->throws(RuntimeException::class, 'boom');

it('hands out the same tab for the same name', function () {
    Lerd::tab('Flags')->keyValue(['beta' => true]);
    Lerd::tab('Flags')->text('again');

    expect(Lerd::tab('Flags')->blocks())->toHaveCount(2)
        ->and(Lerd::tab('Flags')->id())->toBe('flags');
});

it('tracks a timeline entry even when the measured callback throws', function () {
    try {
        Lerd::timeline()->measure('boom', fn () => throw new RuntimeException('boom'), 'jobs', Color::Rose);
    } catch (RuntimeException) {
    }

    expect(Lerd::entries())->toHaveCount(1)
        ->and(Lerd::entries()[0])->toBeInstanceOf(Entry::class)
        ->and(Lerd::entries()[0]->color())->toBe(Color::Rose);
});

it('serialises a timeline entry to the shape lerd reads', function () {
    Lerd::timeline()->event('Coupon applied', 'billing', Color::Emerald, ['code' => 'WELCOME'])->stop();

    expect(json_decode(json_encode(Lerd::entries()[0]), true))->toMatchArray([
        'type' => 'timeline',
        'label' => 'Coupon applied',
        'category' => 'billing',
        'color' => 'emerald',
        'duration_ms' => null,
        'details' => ['code' => 'WELCOME'],
    ]);
});

it('says who the request runs as', function () {
    Lerd::auth(7, 'ada@example.test', 'Ada', 'web');

    expect(json_decode(json_encode(Lerd::entries()[0]), true))->toBe([
        'type' => 'auth',
        'id' => '7',
        'email' => 'ada@example.test',
        'name' => 'Ada',
        'guard' => 'web',
    ]);
});

it('keeps as many entries as it is told, dropping the oldest first', function () {
    Lerd::keep(2);
    Lerd::info('one');
    Lerd::info('two');
    Lerd::info('three');

    expect(Lerd::entries())->toHaveCount(2)
        ->and(Lerd::entries()[0])->toBeInstanceOf(\Lerd\Debug\LogLine::class)
        ->and(Lerd::entries()[0]->message())->toBe('two');

    Lerd::keep(Lerd::DEFAULT_KEEP);
});

it('keeps nothing in memory when told to keep none, while still tracking', function () {
    Lerd::keep(0);
    Lerd::info('sent but not kept');

    expect(Lerd::entries())->toBeEmpty();

    Lerd::keep(Lerd::DEFAULT_KEEP);
});
