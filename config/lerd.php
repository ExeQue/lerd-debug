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

];
