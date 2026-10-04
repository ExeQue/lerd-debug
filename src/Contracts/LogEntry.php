<?php

namespace Lerd\Debug\Contracts;

/**
 * A line written to the request's log through Lerd, with the PSR-3 level, the
 * context, and whether to keep its stack trace or show it on the Performance tab.
 */
interface LogEntry extends Trackable
{
    public function level(): string;

    public function message(): string;

    /**
     * @return array<string, mixed>
     */
    public function context(): array;

    public function withTrace(): bool;

    public function onPerformance(): bool;
}
