<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\Block;
use Lerd\Debug\Contracts\TabEntry;
use Lerd\Debug\Rendering\Renderers;

/**
 * One block added to a tab, carrying the tab it belongs to.
 */
class TabBlock implements TabEntry
{
    public function __construct(
        protected string $tabId,
        protected string $tabTitle,
        protected Block $block,
        protected ?string $blockTitle = null,
        protected int $tabColumns = 1,
        protected int $span = 1,
        /** @var array{0: 'before'|'after', 1: string}|null */
        protected ?array $placement = null,
    ) {
    }

    public function type(): string
    {
        return 'tab';
    }

    public function tabId(): string
    {
        return $this->tabId;
    }

    public function tabTitle(): string
    {
        return $this->tabTitle;
    }

    public function block(): Block
    {
        return $this->block;
    }

    public function blockTitle(): ?string
    {
        return $this->blockTitle;
    }

    public function tabColumns(): int
    {
        return $this->tabColumns;
    }

    public function span(): int
    {
        return $this->span;
    }

    public function placement(): ?array
    {
        return $this->placement;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->render($this);
    }
}
