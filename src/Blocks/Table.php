<?php

namespace Lerd\Debug\Blocks;

use Lerd\Debug\Contracts\Blocks\Table as TableContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * Rows under named columns, a row a list or keyed by column.
 */
class Table implements TableContract
{
    /**
     * @param list<string> $columns
     * @param list<list<mixed>|array<string, mixed>> $rows
     */
    public function __construct(
        protected array $columns,
        protected array $rows,
    ) {
    }

    public function type(): string
    {
        return 'table';
    }

    /**
     * @return list<string>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * @return list<list<mixed>|array<string, mixed>>
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
