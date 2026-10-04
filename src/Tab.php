<?php

namespace Lerd\Debug;

use Lerd\Debug\Blocks\Code;
use Lerd\Debug\Blocks\Counters;
use Lerd\Debug\Blocks\KeyValue;
use Lerd\Debug\Blocks\Table;
use Lerd\Debug\Blocks\Text;
use Lerd\Debug\Contracts\Block;
use Lerd\Debug\Contracts\Chart as ChartContract;
use Lerd\Debug\Contracts\Tab as TabContract;
use Lerd\Debug\Rendering\Renderers;

use function max;
use function min;
use function preg_replace;
use function strtolower;
use function trim;

/**
 * A tab of your own in the request's Debug window, built from blocks.
 *
 * Every block is handed to lerd the moment it is added, so a tab can be
 * created once and written to from anywhere: Tab::named() gives back the same
 * tab for the same name, and lerd puts its blocks together in order.
 *
 * @phpstan-consistent-constructor
 */
class Tab implements TabContract
{
    /** @var array<string, Tab> */
    private static array $named = [];

    /** @var list<array<string, mixed>> */
    protected array $blocks = [];

    protected string $id;

    protected int $columns = 1;

    /** @var array{0: 'before'|'after', 1: string}|null */
    protected ?array $placement = null;

    public function __construct(protected string $title, ?string $id = null)
    {
        $this->id = $id ?? strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-'));
    }

    public static function make(string $title): static
    {
        return new static($title);
    }

    /**
     * The tab with this name, created the first time it is asked for.
     */
    public static function named(string $title): self
    {
        if (!Lerd::enabled()) {
            return new self($title);
        }

        return self::$named[$title] ??= new self($title);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Lay the tab's blocks out in this many columns on a wide screen; a block
     * spans one unless told otherwise. A narrow screen shows one column.
     */
    public function columns(int $columns): static
    {
        $this->columns = max(1, min(4, $columns));

        return $this;
    }

    public function before(string $tab): static
    {
        $this->placement = ['before', $tab];

        return $this;
    }

    public function after(string $tab): static
    {
        $this->placement = ['after', $tab];

        return $this;
    }

    public function columnCount(): int
    {
        return $this->columns;
    }

    /**
     * The blocks added so far, as lerd receives them.
     *
     * @return list<array<string, mixed>>
     */
    public function blocks(): array
    {
        return $this->blocks;
    }

    public function add(Block $block, ?string $title = null, int $span = 1): static
    {
        if (!Lerd::enabled()) {
            return $this;
        }
        $entry = new TabBlock($this->id(), $this->title(), $block, $title, $this->columnCount(), max(1, $span), $this->placement);
        $this->blocks[] = Renderers::latest()->tabBlock($entry);
        Lerd::track($entry);

        return $this;
    }

    /**
     * @param list<string> $columns
     * @param list<list<mixed>|array<string, mixed>> $rows
     */
    public function table(array $columns, array $rows, ?string $title = null, int $span = 1): static
    {
        return $this->add(new Table($columns, $rows), $title, $span);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function keyValue(array $values, ?string $title = null, int $span = 1): static
    {
        return $this->add(new KeyValue($values), $title, $span);
    }

    public function code(string $code, string $language = '', ?string $title = null, int $span = 1): static
    {
        return $this->add(new Code($code, $language), $title, $span);
    }

    public function text(string $text, ?string $title = null, int $span = 1): static
    {
        return $this->add(new Text($text), $title, $span);
    }

    /**
     * Headline numbers in a row, a name above each value.
     *
     * @param array<string, int|float|string> $counters
     */
    public function counters(array $counters, ?string $title = null, int $span = 1): static
    {
        return $this->add(new Counters($counters), $title, $span);
    }

    public function chart(ChartContract $chart, ?string $title = null, int $span = 1): static
    {
        return $this->add($chart, $title, $span);
    }
}
