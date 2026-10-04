<?php

namespace Lerd\Debug\Blocks;

use Lerd\Debug\Contracts\Blocks\Code as CodeContract;
use Lerd\Debug\Rendering\Renderers;

/**
 * A piece of code or other preformatted text.
 */
class Code implements CodeContract
{
    public function __construct(
        protected string $code,
        protected string $language = '',
    ) {
    }

    public function type(): string
    {
        return 'code';
    }

    public function code(): string
    {
        return $this->code;
    }

    public function language(): string
    {
        return $this->language;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
