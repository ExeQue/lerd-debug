<?php

namespace Lerd\Debug\Contracts;

use JsonSerializable;

/**
 * Anything handed to Lerd::track(). lerd reads it as its JSON, whose "type"
 * says what it is: "timeline" for a row on the timeline, "tab" for a block of
 * a tab.
 */
interface Trackable extends JsonSerializable
{
    public function type(): string;
}
