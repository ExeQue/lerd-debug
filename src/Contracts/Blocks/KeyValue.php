<?php

namespace Lerd\Debug\Contracts\Blocks;

use Lerd\Debug\Contracts\Block;

/**
 * Names and their values.
 */
interface KeyValue extends Block
{
    /**
     * @return array<string, mixed>
     */
    public function values(): array;
}
