<?php

namespace Lerd\Debug;

use Lerd\Debug\Contracts\LogEntry;
use Lerd\Debug\Rendering\Renderers;

/**
 * A line written to the request's log through Lerd. The context keys "trace"
 * and "performance" are flags rather than context: the first keeps the call's
 * stack trace with the line, the second shows it on the Performance tab.
 */
class LogLine implements LogEntry
{
    /** @var array<string, mixed> */
    protected array $context;

    protected bool $withTrace;

    protected bool $onPerformance;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        protected string $level,
        protected string $message,
        array $context = [],
    ) {
        $this->withTrace = ($context['trace'] ?? false) === true;
        $this->onPerformance = ($context['performance'] ?? false) === true;
        unset($context['trace'], $context['performance']);
        $this->context = $context;
    }

    public function type(): string
    {
        return 'log';
    }

    public function level(): string
    {
        return $this->level;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function context(): array
    {
        return $this->context;
    }

    public function withTrace(): bool
    {
        return $this->withTrace;
    }

    public function onPerformance(): bool
    {
        return $this->onPerformance;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return Renderers::latest()->render($this);
    }
}
