<?php

namespace Lerd\Debug;

enum ChartType: string
{
    case Line = 'line';
    case Bar = 'bar';
    case Pie = 'pie';
    case ExplodedPie = 'exploded-pie';
}
