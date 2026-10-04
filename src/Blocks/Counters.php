<?php

namespace Lerd\Debug\Blocks;

use Lerd\Debug\Contracts\Blocks\Counters as CountersContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * Headline numbers in a row, a name above each value, like the figures at the
 * top of the Performance tab.
 */
class Counters implements CountersContract
{
    /**
     * @param array<string, int|float|string> $counters
     */
    public function __construct(protected array $counters)
    {
    }

    public function type(): string
    {
        return 'counters';
    }

    /**
     * @return array<string, int|float|string>
     */
    public function counters(): array
    {
        return $this->counters;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
