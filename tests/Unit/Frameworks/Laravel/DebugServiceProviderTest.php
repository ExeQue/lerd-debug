<?php

namespace Lerd\Debug\Tests\Unit\Frameworks\Laravel;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Queue\Events\JobProcessed;
use Lerd\Debug\Frameworks\Laravel\DebugServiceProvider;
use Lerd\Debug\Lerd;

use function dirname;
use function expect;
use function it;
use function putenv;

/**
 * An application with the package's provider registered and booted, and the
 * given lerd config on top of the package's own.
 *
 * @param array<string, mixed> $lerd
 */
function bootedApp(array $lerd = []): Application
{
    $app = new Application(dirname(__DIR__, 4));
    $app->instance('config', new Repository(['lerd' => $lerd]));
    $app->alias('config', RepositoryContract::class);
    $app->instance(DispatcherContract::class, new Dispatcher($app));
    $provider = new DebugServiceProvider($app);
    $provider->register();
    $app->call([$provider, 'boot']);

    return $app;
}

it('forgets the kept entries once a job is done', function () {
    $app = bootedApp();

    Lerd::timeline()->event('during the job')->stop();
    expect(Lerd::entries())->toHaveCount(1);

    $app->make(DispatcherContract::class)->dispatch(JobProcessed::class);
    expect(Lerd::entries())->toBeEmpty();
});

it('switches the package off when the config says so', function () {
    bootedApp(['enabled' => false]);

    Lerd::info('ignored');

    expect(Lerd::enabled())->toBeFalse()
        ->and(Lerd::entries())->toBeEmpty();
});

it('reads LERD_ENABLED from the environment as a boolean', function (string $value, bool $enabled) {
    putenv("LERD_ENABLED={$value}");
    $config = require dirname(__DIR__, 4) . '/config/lerd.php';
    putenv('LERD_ENABLED');

    expect($config['enabled'])->toBe($enabled);
})->with([['false', false], ['0', false], ['true', true], ['1', true]]);

it('leaves the package on when the config allows it', function () {
    bootedApp(['enabled' => true]);

    expect(Lerd::enabled())->toBeTrue();
});
