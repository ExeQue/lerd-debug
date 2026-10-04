<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Entry;
use Lerd\Debug\Rendering\Renderer;
use Lerd\Debug\Rendering\Renderers;
use Lerd\Debug\Rendering\V1Renderer;

use function expect;
use function it;

it('picks the newest renderer no newer than the schema asked for', function () {
    expect(Renderers::for(1))->toBeInstanceOf(V1Renderer::class)
        ->and(Renderers::for(7)->version())->toBe(1)
        ->and(Renderers::latest())->toBeInstanceOf(Renderer::class);
});

it('renders a timeline entry in the first schema', function () {
    expect(Renderers::for(1)->render(new Entry('Charge', 'billing', start: 1.5, durationMs: 2.0)))->toBe([
        'type' => 'timeline',
        'label' => 'Charge',
        'category' => 'billing',
        'color' => 'blue',
        'start' => 1.5,
        'duration_ms' => 2.0,
        'details' => [],
    ]);
});
