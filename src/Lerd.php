<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\Trackable;

use function array_shift;
use function array_slice;
use function constant;
use function count;
use function defined;
use function max;

/**
 * One entry point for everything an app writes to lerd's Debug window, and
 * the one place every entry passes through: Lerd::timeline() for rows on the
 * request's timeline and events timed by hand, Lerd::tab() for tabs of your
 * own, and the log methods for log lines.
 *
 * lerd observes track() and reads each entry from it, so without lerd the
 * calls only keep the entries here, where entries() shows them.
 */
class Lerd
{
    /** How many entries entries() keeps unless told otherwise. */
    public const DEFAULT_KEEP = 500;

    private static int $keep = self::DEFAULT_KEEP;

    private static ?Timeline $timeline = null;

    /** @var list<Trackable> */
    private static array $entries = [];

    private static ?bool $enabled = null;

    /**
     * Whether lerd is capturing this process. Off it, in production say, every
     * call returns at once and nothing is kept, not even in memory. Read from
     * the constants lerd's extension defines, once per process.
     */
    public static function enabled(): bool
    {
        return self::$enabled ??= (defined('LERD_DEVTOOLS_ON') && constant('LERD_DEVTOOLS_ON') === true)
            || (defined('LERD_DEVTOOLS_JOBS') && constant('LERD_DEVTOOLS_JOBS') === true);
    }

    /**
     * Force capture on or off, for tests or to switch it off by hand; null goes
     * back to reading it from lerd.
     */
    public static function enable(?bool $enabled = true): void
    {
        self::$enabled = $enabled;
    }

    /**
     * How many tracked entries to keep for entries(), the oldest dropped first.
     * Zero keeps none, which lerd does not need: it reads each entry as it is
     * tracked.
     */
    public static function keep(int $entries): void
    {
        self::$keep = max(0, $entries);
        self::$entries = self::$keep === 0 ? [] : array_slice(self::$entries, -self::$keep);
    }

    public static function timeline(): Timeline
    {
        return self::$timeline ??= new Timeline();
    }

    /**
     * Say who the request runs as, shown in its header in lerd. Framework
     * adapters set this themselves; call it for an app that authenticates
     * its own way.
     */
    public static function auth(int|string $id, ?string $email = null, ?string $name = null, ?string $guard = null): void
    {
        self::track(new AuthUser((string) $id, $email, $name, $guard));
    }

    /**
     * Write a line to the request's log at a PSR-3 level. Context "trace" =>
     * true keeps the stack trace, "performance" => true shows the line on the
     * Performance tab.
     *
     * @param array<string, mixed> $context
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        self::track(new LogLine($level, $message, $context));
    }

    /** @param array<string, mixed> $context */
    public static function emergency(string $message, array $context = []): void
    {
        self::log('emergency', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function alert(string $message, array $context = []): void
    {
        self::log('alert', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function notice(string $message, array $context = []): void
    {
        self::log('notice', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }

    /**
     * The tab with this name; every block added to it reaches lerd at once.
     */
    public static function tab(string $title): Tab
    {
        return Tab::named($title);
    }

    /**
     * Hand an entry to lerd: a timeline row, a block of a tab, or an object of
     * your own whose JSON has the same shape.
     */
    public static function track(Trackable $entry): void
    {
        if (!self::enabled()) {
            return;
        }
        if (self::$keep === 0) {
            return;
        }
        self::$entries[] = $entry;
        if (count(self::$entries) > self::$keep) {
            array_shift(self::$entries);
        }
    }

    /**
     * The entries tracked so far, oldest first.
     *
     * @return list<Trackable>
     */
    public static function entries(): array
    {
        return self::$entries;
    }

    /**
     * Forget the tracked entries and any events not yet stopped, between jobs
     * in a worker or between tests.
     */
    public static function flush(): void
    {
        self::$entries = [];
        self::$timeline = null;
    }
}
