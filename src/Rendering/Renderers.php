<?php

namespace Lerd\Debug\Rendering;

use const PHP_INT_MAX;

/**
 * The renderers this package ships, one per schema version of lerd's.
 */
class Renderers
{
    /**
     * The renderer for the newest schema this package knows that is no newer
     * than the one asked for, so an older lerd keeps getting what it reads
     * and a newer one gets the best this package can give.
     */
    public static function for(int $version): Renderer
    {
        $chosen = null;
        foreach (self::all() as $renderer) {
            if ($renderer->version() <= $version && ($chosen === null || $renderer->version() > $chosen->version())) {
                $chosen = $renderer;
            }
        }

        return $chosen ?? self::all()[0];
    }

    public static function latest(): Renderer
    {
        return self::for(PHP_INT_MAX);
    }

    /**
     * @return list<Renderer>
     */
    public static function all(): array
    {
        return [new V1Renderer()];
    }
}
