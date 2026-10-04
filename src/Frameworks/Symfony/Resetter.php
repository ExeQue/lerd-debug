<?php

namespace Lerd\Debug\Frameworks\Symfony;

use Lerd\Debug\Lerd;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Forgets Lerd's kept entries when Symfony resets its services between
 * requests in a worker runtime.
 */
class Resetter implements ResetInterface
{
    public function reset(): void
    {
        Lerd::flush();
    }
}
