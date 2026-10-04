<?php

namespace Lerd\Debug\Blocks;

use Lerd\Debug\Contracts\Blocks\KeyValue as KeyValueContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * Names and their values; a nested value renders as a tree.
 */
class KeyValue implements KeyValueContract
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(protected array $values)
    {
    }

    public function type(): string
    {
        return 'kv';
    }

    /**
     * @return array<string, mixed>
     */
    public function values(): array
    {
        return $this->values;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
