<?php

namespace Lerd\Debug\Contracts\Blocks;

use Lerd\Debug\Contracts\Block;

/**
 * A paragraph of plain text.
 */
interface Text extends Block
{
    public function text(): string;
}
