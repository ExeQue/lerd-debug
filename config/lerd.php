<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Whether lerd/debug writes anything at all. When false, nothing is kept
    | or sent even where lerd is capturing. When true, the package still only
    | acts where lerd is capturing, so leaving it on in production is safe.
    | Set LERD_ENABLED to true or false, or 1 or 0.
    |
    */

    'enabled' => (bool) env('LERD_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Kept entries
    |--------------------------------------------------------------------------
    |
    | How many of the entries the app wrote are kept in memory for
    | Lerd::entries(), the oldest dropped first. lerd itself reads each entry
    | as it is written, so this only matters to code that reads them back,
    | a test say. Set LERD_KEEP, or 0 to keep none.
    |
    */

    'keep' => (int) env('LERD_KEEP', 500), // @phpstan-ignore cast.int

];
