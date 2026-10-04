<?php

namespace Lerd\Debug\Contracts\Blocks;

use Lerd\Debug\Contracts\Block;

/**
 * Rows under named columns.
 */
interface Table extends Block
{
    /**
     * @return list<string>
     */
    public function columns(): array;

    /**
     * @return list<list<mixed>|array<string, mixed>>
     */
    public function rows(): array;
}
