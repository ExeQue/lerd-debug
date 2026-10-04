<?php

namespace Lerd\Debug\Blocks;

use Lerd\Debug\Contracts\Blocks\Text as TextContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * A paragraph of plain text.
 */
class Text implements TextContract
{
    public function __construct(protected string $text)
    {
    }

    public function type(): string
    {
        return 'text';
    }

    public function text(): string
    {
        return $this->text;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
