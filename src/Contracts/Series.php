<?php

namespace Lerd\Debug\Contracts;

use JsonSerializable;
use Lerd\Debug\Color;

/**
 * One series of a chart: a name, a value per label, and an optional colour.
 */
interface Series extends JsonSerializable
{
    public function name(): string;

    /**
     * @return array<int|string, int|float>
     */
    public function points(): array;

    public function color(): ?Color;

    /**
     * Add the value at one label, drawn after the ones added before it.
     */
    public function point(int|string $label, int|float $value): static;
}
