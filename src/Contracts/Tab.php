<?php

namespace Lerd\Debug\Contracts;

use Lerd\Debug\Contracts\Chart as ChartContract;

/**
 * A tab of your own: an id the blocks are put together under, a title, and a
 * way to add a block, which reaches lerd as it is added.
 */
interface Tab
{
    public function id(): string;

    public function title(): string;

    /**
     * How many columns the tab lays its blocks out in on a wide screen.
     */
    public function columnCount(): int;

    /**
     * Lay the tab's blocks out in this many columns on a wide screen.
     */
    public function columns(int $columns): static;

    /**
     * Show the tab before another: a built-in tab's id (performance, request,
     * database, ...) or another custom tab's id.
     */
    public function before(string $tab): static;

    /**
     * Show the tab after another, named the same way as before().
     */
    public function after(string $tab): static;

    /**
     * The blocks added so far, as lerd receives them.
     *
     * @return list<array<string, mixed>>
     */
    public function blocks(): array;

    /**
     * Add a block, spanning this many of the tab's columns.
     */
    public function add(Block $block, ?string $title = null, int $span = 1): static;

    /**
     * @param list<string> $columns
     * @param list<list<mixed>|array<string, mixed>> $rows
     */
    public function table(array $columns, array $rows, ?string $title = null, int $span = 1): static;

    /**
     * @param array<string, mixed> $values
     */
    public function keyValue(array $values, ?string $title = null, int $span = 1): static;

    /**
     * @param array<string, int|float|string> $counters
     */
    public function counters(array $counters, ?string $title = null, int $span = 1): static;

    public function code(string $code, string $language = '', ?string $title = null, int $span = 1): static;

    public function text(string $text, ?string $title = null, int $span = 1): static;

    public function chart(ChartContract $chart, ?string $title = null, int $span = 1): static;
}
