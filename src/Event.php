<?php

namespace Lerd\Debug;

use function max;
use function microtime;

/**
 * Something to time by hand, handed out by Lerd::timeline()->event(). Start the timer when
 * the work begins and stop it when it ends, run a callback through it, or set
 * when it started and how long it took for work that already happened. Only
 * stopping tracks it, as a span when it has a start and as a moment otherwise.
 */
class Event
{
    protected ?float $startedAt = null;

    protected ?float $fixedDuration = null;

    protected bool $stopped = false;

    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        protected string $name,
        protected string $category = 'app',
        protected Color $color = Color::Blue,
        protected array $details = [],
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function start(): static
    {
        $this->startedAt = microtime(true);

        return $this;
    }

    /**
     * Start the event at a moment already past, a Unix timestamp with
     * microseconds.
     */
    public function startAt(float $timestamp): static
    {
        $this->startedAt = $timestamp;

        return $this;
    }

    /**
     * Fix how long the event took instead of timing it; stop() then ends it
     * this many milliseconds after its start.
     */
    public function duration(float $milliseconds): static
    {
        $this->fixedDuration = max(0.0, $milliseconds);
        $this->startedAt ??= microtime(true) - $this->fixedDuration / 1000;

        return $this;
    }

    public function color(Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function category(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Add details shown in the row's popover.
     *
     * @param array<string, mixed> $details
     */
    public function with(array $details): static
    {
        $this->details = $details + $this->details;

        return $this;
    }

    /**
     * Time $callback with this event and return what it returns; the event is
     * stopped even when the callback throws.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function run(callable $callback): mixed
    {
        $this->start();
        try {
            return $callback();
        } finally {
            $this->stop();
        }
    }

    /**
     * Stop the timer and hand the event to lerd. Stopping it again does nothing.
     */
    public function stop(): void
    {
        if ($this->stopped) {
            return;
        }
        $this->stopped = true;
        Lerd::timeline()->forget($this);
        $now = microtime(true);
        $duration = match (true) {
            $this->fixedDuration !== null => $this->fixedDuration,
            $this->startedAt !== null => ($now - $this->startedAt) * 1000,
            default => null,
        };
        Lerd::track(new Entry($this->name, $this->category, $this->color, $this->startedAt ?? $now, $duration, $this->details));
    }

    public function isRunning(): bool
    {
        return $this->startedAt !== null && !$this->stopped;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }
}
