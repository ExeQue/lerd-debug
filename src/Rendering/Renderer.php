<?php

namespace Lerd\Debug\Rendering;

use Lerd\Debug\Contracts\Block;
use Lerd\Debug\Contracts\Series;
use Lerd\Debug\Contracts\TabEntry;
use Lerd\Debug\Contracts\Trackable;

/**
 * Turns what an app hands to Lerd into the data one version of lerd's schema
 * expects. lerd names the version it speaks, so a schema change on either side
 * is a new renderer rather than a break.
 */
interface Renderer
{
    /**
     * The schema version this renderer produces.
     */
    public function version(): int;

    /**
     * @return array<string, mixed>
     */
    public function render(Trackable $entry): array;

    /**
     * @return array<string, mixed>
     */
    public function block(Block $block): array;

    /**
     * A tab's block as it is drawn, with its heading and span.
     *
     * @return array<string, mixed>
     */
    public function tabBlock(TabEntry $entry): array;

    /**
     * @return array<string, mixed>
     */
    public function series(Series $series): array;
}
