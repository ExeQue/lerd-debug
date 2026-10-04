<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Chart;
use Lerd\Debug\Color;
use Lerd\Debug\Lerd;
use Lerd\Debug\Series;
use Lerd\Debug\Tab;
use Lerd\Debug\TabBlock;

use function array_column;
use function expect;
use function it;
use function json_decode;
use function json_encode;

it('collects a tab\'s blocks in the order they were added', function () {
    $tab = Tab::make('Feature flags')
        ->keyValue(['beta' => true], 'Flags')
        ->table(['flag', 'on'], [['beta', true]])
        ->chart(Chart::pie(new Series('flags', ['on' => 3, 'off' => 1])));

    expect(array_column($tab->blocks(), 'type'))->toBe(['kv', 'table', 'chart'])
        ->and($tab->blocks()[2]['chart'])->toBe('pie');
});

it('tracks every block through Lerd with the tab it belongs to', function () {
    Tab::make('Cart')->text('hello');

    expect(Lerd::entries())->toHaveCount(1)
        ->and(Lerd::entries()[0])->toBeInstanceOf(TabBlock::class)
        ->and(Lerd::entries()[0]->tabId())->toBe('cart');
});

it('lines up every series on the labels any of them has', function () {
    $chart = Chart::bar(
        new Series('orders', ['Mon' => 3, 'Tue' => 5], Color::Teal),
        new Series('returns', ['Tue' => 1, 'Wed' => 2]),
    );

    expect(json_decode(json_encode($chart), true))->toBe([
        'type' => 'chart',
        'chart' => 'bar',
        'labels' => ['Mon', 'Tue', 'Wed'],
        'series' => [
            ['name' => 'orders', 'points' => ['Mon' => 3, 'Tue' => 5], 'color' => 'teal'],
            ['name' => 'returns', 'points' => ['Tue' => 1, 'Wed' => 2], 'color' => null],
        ],
    ]);
});

it('takes a block of your own as long as it describes itself', function () {
    $block = new class () implements \Lerd\Debug\Contracts\Block {
        public function type(): string
        {
            return 'text';
        }

        public function jsonSerialize(): array
        {
            return ['type' => 'text', 'text' => 'from a custom block'];
        }
    };

    Tab::make('Custom')->add($block, 'Note');

    expect(json_decode(json_encode(Lerd::entries()[0]), true)['block'])
        ->toBe(['title' => 'Note', 'span' => 1, 'type' => 'text', 'text' => 'from a custom block']);
});

it('carries the tab\'s columns and each block\'s span to lerd', function () {
    Tab::make('Report')->columns(3)
        ->text('wide', span: 2)
        ->text('narrow');

    $first = json_decode(json_encode(Lerd::entries()[0]), true);
    expect($first['columns'])->toBe(3)
        ->and($first['block']['span'])->toBe(2)
        ->and(json_decode(json_encode(Lerd::entries()[1]), true)['block']['span'])->toBe(1);
});

it('adds headline counters as a block', function () {
    Tab::make('Stats')->counters(['orders' => 12, 'revenue' => '1.2k'], 'Today');

    expect(json_decode(json_encode(Lerd::entries()[0]), true)['block'])
        ->toBe(['title' => 'Today', 'span' => 1, 'type' => 'counters', 'counters' => ['orders' => 12, 'revenue' => '1.2k']]);
});

it('has a chart type of its own for an exploded pie', function () {
    expect(json_decode(json_encode(Chart::explodedPie(new Series('refunds', ['card' => 3]))), true)['chart'])->toBe('exploded-pie');
});

it('places a tab before or after another', function () {
    Tab::make('Billing')->before('database')->text('first');
    Tab::make('Flags')->after('performance')->text('second');

    $first = json_decode(json_encode(Lerd::entries()[0]), true);
    $second = json_decode(json_encode(Lerd::entries()[1]), true);
    expect($first['placement'])->toBe(['position' => 'before', 'tab' => 'database'])
        ->and($second['placement'])->toBe(['position' => 'after', 'tab' => 'performance']);
});
