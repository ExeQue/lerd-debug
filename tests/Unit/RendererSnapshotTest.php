<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\AuthUser;
use Lerd\Debug\Blocks\Code;
use Lerd\Debug\Blocks\Counters;
use Lerd\Debug\Blocks\KeyValue;
use Lerd\Debug\Blocks\Table;
use Lerd\Debug\Blocks\Text;
use Lerd\Debug\Chart;
use Lerd\Debug\Color;
use Lerd\Debug\Contracts\Trackable;
use Lerd\Debug\Entry;
use Lerd\Debug\LogLine;
use Lerd\Debug\Rendering\Renderer;
use Lerd\Debug\Rendering\Renderers;
use Lerd\Debug\Series;
use Lerd\Debug\TabBlock;

use function array_map;
use function dirname;
use function expect;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function it;
use function json_encode;
use function sprintf;
use function test;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * One of everything a renderer turns into lerd's schema, with fixed values so
 * the output is the same on every run.
 *
 * @return list<Trackable>
 */
function everyEntry(): array
{
    $chart = Chart::bar(new Series('orders', ['Mon' => 3, 'Tue' => 5], Color::Teal))->add(Series::make('returns')->point('Tue', 1));

    return [
        new Entry('Render invoice', 'billing', Color::Amber, 1700000000.25, 12.5, ['order' => 42]),
        new Entry('Webhook received', 'hooks', Color::Cyan, 1700000001.5, null, []),
        new LogLine('warning', 'Slow upstream', ['ms' => 2300, 'trace' => true, 'performance' => true]),
        new AuthUser('7', 'ada@example.test', 'Ada', 'web'),
        new TabBlock('cart', 'Cart', new Table(['sku', 'qty'], [['A-1', 2], ['sku' => 'B-7', 'qty' => 1]]), 'Lines', 2, 1, ['before', 'database']),
        new TabBlock('cart', 'Cart', new KeyValue(['total' => 19.9, 'items' => [['sku' => 'A-1']]]), 'Contents', 2, 1),
        new TabBlock('cart', 'Cart', new Counters(['items' => 3, 'total' => '59.70']), 'Summary', 2, 2),
        new TabBlock('cart', 'Cart', new Code('{"a":1}', 'json'), 'Payload', 2, 1),
        new TabBlock('cart', 'Cart', new Text('Coupon applied'), null, 2, 1),
        new TabBlock('cart', 'Cart', $chart, 'This week', 2, 2),
        new TabBlock('cart', 'Cart', Chart::pie(new Series('status', ['paid' => 8, 'pending' => 2])), 'Status', 2, 1),
        new TabBlock('cart', 'Cart', Chart::explodedPie(new Series('refunds', ['card' => 3, 'bank' => 1])), 'Refunds', 2, 1),
    ];
}

function rendered(Renderer $renderer): string
{
    return json_encode(
        array_map(fn (Trackable $entry) => $renderer->render($entry), everyEntry()),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) . "\n";
}

// A renderer for a released schema version must never change its output: lerd
// versions in the wild read exactly this. A change belongs in a new renderer.
test('every renderer keeps rendering exactly what its fixture holds', function (Renderer $renderer) {
    $fixture = dirname(__DIR__) . sprintf('/Fixtures/renderers/v%d.json', $renderer->version());
    if (!file_exists($fixture)) {
        file_put_contents($fixture, rendered($renderer));
        $this->fail(sprintf('Wrote %s for a renderer without one; review it and commit it.', $fixture));
    }

    expect(rendered($renderer))->toBe(file_get_contents($fixture));
})->with(fn () => array_map(fn (Renderer $renderer) => [$renderer], Renderers::all()));

it('has a fixture for the renderer the package renders with today', function () {
    expect(file_exists(dirname(__DIR__) . sprintf('/Fixtures/renderers/v%d.json', Renderers::latest()->version())))->toBeTrue();
});
