<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Color;
use Lerd\Debug\Lerd;

use function expect;
use function it;

it('tracks nothing until the event is stopped', function () {
    $event = Lerd::timeline()->event('Import')->start();

    expect(Lerd::entries())->toBeEmpty()
        ->and($event->isRunning())->toBeTrue();

    $event->stop();

    expect(Lerd::entries())->toHaveCount(1)
        ->and(Lerd::entries()[0]->durationMs())->toBeFloat()
        ->and($event->isRunning())->toBeFalse();
});

it('tracks an event stopped without being started as a moment', function () {
    Lerd::timeline()->event('Webhook received', 'hooks', Color::Cyan)->with(['source' => 'stripe'])->stop();

    expect(Lerd::entries()[0]->durationMs())->toBeNull()
        ->and(Lerd::entries()[0]->details())->toBe(['source' => 'stripe']);
});

it('tracks an event once however often it is stopped', function () {
    $event = Lerd::timeline()->event('Sync')->start();
    $event->stop();
    $event->stop();

    expect(Lerd::entries())->toHaveCount(1);
});

it('hands out the same running event by name until it is stopped', function () {
    Lerd::timeline()->event('Checkout')->start();
    Lerd::timeline()->event('Checkout')->color(Color::Rose)->stop();

    expect(Lerd::entries())->toHaveCount(1)
        ->and(Lerd::entries()[0]->color())->toBe(Color::Rose)
        ->and(Lerd::timeline()->event('Checkout')->isStopped())->toBeFalse();
});

it('runs a callback through the event and returns its result', function () {
    expect(Lerd::timeline()->event('Render')->run(fn () => 'html'))->toBe('html')
        ->and(Lerd::entries()[0]->durationMs())->toBeFloat();
});

it('records work that already happened from a start and a duration', function () {
    Lerd::timeline()->event('Queue wait')->startAt(1000.5)->duration(250.0)->stop();

    expect(Lerd::entries()[0]->start())->toBe(1000.5)
        ->and(Lerd::entries()[0]->durationMs())->toBe(250.0);
});
