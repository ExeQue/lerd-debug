<?php

namespace Lerd\Debug\Contracts;

use Lerd\Debug\ChartType;

/**
 * A chart block: how it is drawn and the series it draws.
 */
interface Chart extends Block
{
    public function chartType(): ChartType;

    /**
     * @return list<Series>
     */
    public function series(): array;

    /**
     * Add a series after the ones already in the chart.
     */
    public function add(Series $series): static;
}
