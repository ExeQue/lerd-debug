<?php

namespace Lerd\Debug\Contracts;

use JsonSerializable;

/**
 * One block of a tab. Its JSON carries a "type" lerd knows how to draw:
 * table, kv, code, text or chart.
 */
interface Block extends JsonSerializable
{
    public function type(): string;
}
