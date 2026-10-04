<?php

namespace Lerd\Debug\Contracts;

use Lerd\Debug\Color;

/**
 * A row on the request's timeline: a span when it has a duration, a moment
 * when it does not. Start is a Unix timestamp with microseconds.
 */
interface TimelineEntry extends Trackable
{
    public function label(): string;

    public function category(): string;

    public function color(): Color;

    public function start(): float;

    public function durationMs(): ?float;

    /**
     * @return array<string, mixed>
     */
    public function details(): array;
}
