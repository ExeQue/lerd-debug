<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\TimelineEntry;
use Lerd\Debug\Rendering\Renderers;

/**
 * One row on a request's timeline: a span when it has a duration, a moment
 * when it does not. Start is a Unix timestamp with microseconds.
 */
class Entry implements TimelineEntry
{
    /**
     * @param array<string, mixed> $details shown in the row's popover
     */
    public function __construct(
        protected string $label,
        protected string $category = 'app',
        protected Color $color = Color::Blue,
        protected float $start = 0.0,
        protected ?float $durationMs = null,
        protected array $details = [],
    ) {
    }

    public function type(): string
    {
        return 'timeline';
    }

    public function label(): string
    {
        return $this->label;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function color(): Color
    {
        return $this->color;
    }

    public function start(): float
    {
        return $this->start;
    }

    public function durationMs(): ?float
    {
        return $this->durationMs;
    }

    public function details(): array
    {
        return $this->details;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->render($this);
    }
}
