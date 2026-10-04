<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\Chart as ChartContract;
use Lerd\Debug\Contracts\Series as SeriesContract;
use Lerd\Debug\Rendering\Renderers;

use function array_values;

/**
 * A chart block: a line or bar chart with a series each, or a pie of its
 * first series, drawn over every series' labels in the order first seen.
 *
 * @phpstan-consistent-constructor
 */
class Chart implements ChartContract
{
    /**
     * @param list<SeriesContract> $series
     */
    public function __construct(
        protected ChartType $type,
        protected array $series,
    ) {
    }

    public static function line(SeriesContract ...$series): static
    {
        return new static(ChartType::Line, array_values($series));
    }

    public static function bar(SeriesContract ...$series): static
    {
        return new static(ChartType::Bar, array_values($series));
    }

    /**
     * A pie shows one series, its labels as the slices.
     */
    public static function pie(SeriesContract $series): static
    {
        return new static(ChartType::Pie, [$series]);
    }

    /**
     * A pie with its slices pulled apart from the centre.
     */
    public static function explodedPie(SeriesContract $series): static
    {
        return new static(ChartType::ExplodedPie, [$series]);
    }

    /**
     * Add a series after the ones already in the chart.
     */
    public function add(SeriesContract $series): static
    {
        $this->series[] = $series;

        return $this;
    }

    public function type(): string
    {
        return 'chart';
    }

    public function chartType(): ChartType
    {
        return $this->type;
    }

    public function series(): array
    {
        return $this->series;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->block($this);
    }
}
