<?php

namespace Lerd\Debug\Contracts;

/**
 * One block added to a tab, with the tab it belongs to and the heading it
 * was given.
 */
interface TabEntry extends Trackable
{
    public function tabId(): string;

    public function tabTitle(): string;

    public function block(): Block;

    public function blockTitle(): ?string;

    /**
     * How many columns the tab lays its blocks out in.
     */
    public function tabColumns(): int;

    /**
     * How many of those columns this block spans.
     */
    public function span(): int;

    /**
     * Where the tab goes among the others, as [before|after, tab id], or null
     * for after every built-in tab.
     *
     * @return array{0: 'before'|'after', 1: string}|null
     */
    public function placement(): ?array;
}
