<?php

namespace Lerd\Debug\Tests;

use Lerd\Debug\Lerd;

use function uses;

// Lerd keeps entries in static state, so every test starts without any, and
// with capture on, since no lerd extension defines the constants here.
uses()->beforeEach(function () {
    Lerd::enable(true);
    Lerd::flush();
})->in(__DIR__);
