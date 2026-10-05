<?php

namespace Lerd\Debug\Frameworks\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickTerminated;
use Lerd\Debug\Lerd;

use function dirname;
use function is_int;

/**
 * Applies the app's lerd config, where `enabled` false switches the package
 * off even where lerd is capturing, and forgets the entries Lerd keeps once a
 * job or an Octane request is done, so a long-running worker does not carry
 * one job's entries into the next.
 * Discovered by Laravel on its own. ::class resolves without loading the
 * class, so an app without queue or Octane installed is fine.
 */
class DebugServiceProvider extends ServiceProvider
{
    /** @var list<class-string> */
    public const PRUNE_AFTER = [
        JobProcessed::class,
        JobExceptionOccurred::class,
        RequestTerminated::class,
        TaskTerminated::class,
        TickTerminated::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 3) . '/config/lerd.php', 'lerd');
    }

    public function boot(Repository $config, Dispatcher $events): void
    {
        $this->publishes([dirname(__DIR__, 3) . '/config/lerd.php' => $this->app->configPath('lerd.php')], 'lerd-config');

        if ($config->get('lerd.enabled', true) === false) {
            Lerd::enable(false);
        }
        $keep = $config->get('lerd.keep');
        if (is_int($keep)) {
            Lerd::keep($keep);
        }

        $events->listen(self::PRUNE_AFTER, fn () => Lerd::flush());
    }
}
