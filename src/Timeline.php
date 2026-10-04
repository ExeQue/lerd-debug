<?php

namespace Lerd\Debug;

/**
 * Puts your own work on the request's timeline in lerd's Debug window, reached
 * through Lerd::timeline(): a callback timed with measure(), or an event timed
 * by hand with event().
 */
class Timeline
{
    /** @var array<string, Event> events handed out by name and not yet stopped */
    protected array $events = [];

    /**
     * Run $callback and record how long it took.
     *
     * @template T
     * @param callable(): T $callback
     * @param array<string, mixed> $details
     * @return T
     */
    public function measure(string $label, callable $callback, string $category = 'app', Color $color = Color::Blue, array $details = []): mixed
    {
        return (new Event($label, $category, $color, $details))->run($callback);
    }

    /**
     * An event to time by hand: start() it, stop() it, and only then does it
     * reach lerd. The same name gives back the same event until it is stopped,
     * so one part of an app can start it and another stop it; stopped without
     * being started, it is a moment.
     *
     * @param array<string, mixed> $details
     */
    public function event(string $name, string $category = 'app', Color $color = Color::Blue, array $details = []): Event
    {
        if (!Lerd::enabled()) {
            return new Event($name, $category, $color, $details);
        }

        return $this->events[$name] ??= new Event($name, $category, $color, $details);
    }

    /**
     * Let go of a stopped event, so the next event() with its name is a new
     * one. Event::stop() calls this.
     */
    public function forget(Event $event): void
    {
        if (($this->events[$event->name()] ?? null) === $event) {
            unset($this->events[$event->name()]);
        }
    }
}
