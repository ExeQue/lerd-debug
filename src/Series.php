<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\Series as SeriesContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * One series of a chart: its name, a value per label, and the colour it is
 * drawn in, picked from the palette in order when left out.
 *
 * @phpstan-consistent-constructor
 */
class Series implements SeriesContract
{
    /**
     * @param array<int|string, int|float> $points value by label, in the order they are drawn; a numeric label such as a year becomes an int key
     */
    public function __construct(
        protected string $name,
        protected array $points,
        protected ?Color $color = null,
    ) {
    }

    /**
     * Start a series to add points to one at a time.
     */
    public static function make(string $name, ?Color $color = null): static
    {
        return new static($name, [], $color);
    }

    /**
     * Add the value at one label, drawn after the ones added before it.
     */
    public function point(int|string $label, int|float $value): static
    {
        $this->points[$label] = $value;

        return $this;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function points(): array
    {
        return $this->points;
    }

    public function color(): ?Color
    {
        return $this->color;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->series($this);
    }
}
