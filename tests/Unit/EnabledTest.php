<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Chart;
use Lerd\Debug\Color;
use Lerd\Debug\Lerd;
use Lerd\Debug\Series;

use function afterEach;
use function constant;
use function defined;
use function expect;
use function it;
use function json_decode;
use function json_encode;

afterEach(fn () => Lerd::enable(true));

it('keeps nothing at all when lerd is not capturing', function () {
    Lerd::enable(false);

    Lerd::info('ignored');
    Lerd::timeline()->event('ignored')->start()->stop();
    $tab = Lerd::tab('Ignored')->text('ignored');

    expect(Lerd::entries())->toBeEmpty()
        ->and($tab->blocks())->toBeEmpty();
});

it('reads whether lerd is capturing from the extension when not forced', function () {
    Lerd::enable(null);
    $capturing = (defined('LERD_DEVTOOLS_ON') && constant('LERD_DEVTOOLS_ON') === true)
        || (defined('LERD_DEVTOOLS_JOBS') && constant('LERD_DEVTOOLS_JOBS') === true);

    expect(Lerd::enabled())->toBe($capturing);
});

it('builds a series a point at a time and a chart a series at a time', function () {
    $chart = Chart::bar(Series::make('orders', Color::Teal)->point('Mon', 3)->point('Tue', 5))
        ->add(Series::make('returns')->point('Tue', 1));

    expect(json_decode(json_encode($chart), true)['series'])->toBe([
        ['name' => 'orders', 'points' => ['Mon' => 3, 'Tue' => 5], 'color' => 'teal'],
        ['name' => 'returns', 'points' => ['Tue' => 1], 'color' => null],
    ]);
});
