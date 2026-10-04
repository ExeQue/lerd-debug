<?php

namespace Lerd\Debug\Contracts\Blocks;

use Lerd\Debug\Contracts\Block;

/**
 * Code or other preformatted text.
 */
interface Code extends Block
{
    public function code(): string;

    public function language(): string;
}
