<?php

namespace Lerd\Debug\Contracts\Blocks;

use Lerd\Debug\Contracts\Block;

/**
 * Headline numbers in a row.
 */
interface Counters extends Block
{
    /**
     * @return array<string, int|float|string>
     */
    public function counters(): array;
}
