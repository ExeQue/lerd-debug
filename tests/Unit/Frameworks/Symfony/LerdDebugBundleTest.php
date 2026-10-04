<?php

namespace Lerd\Debug\Tests\Unit\Frameworks\Symfony;

use Lerd\Debug\Frameworks\Symfony\LerdDebugBundle;
use Lerd\Debug\Frameworks\Symfony\MessengerSubscriber;
use Lerd\Debug\Frameworks\Symfony\Resetter;
use Lerd\Debug\Lerd;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

use function expect;
use function it;
use function sys_get_temp_dir;

/**
 * A container with the bundle's extension loaded from the given config.
 *
 * @param array<string, mixed> $config
 */
function bundleContainer(array $config = []): ContainerBuilder
{
    $container = new ContainerBuilder();
    $container->setParameter('kernel.environment', 'test');
    $container->setParameter('kernel.build_dir', sys_get_temp_dir());
    $extension = (new LerdDebugBundle())->getContainerExtension();
    $container->registerExtension($extension);
    $container->loadFromExtension($extension->getAlias(), $config);

    return $container;
}

it('registers a resetter for worker runtimes and a Messenger subscriber', function () {
    $container = bundleContainer();
    $container->compile();

    expect($container->getParameter('lerd.enabled'))->toBeTrue();
});

it('switches the package off when lerd.enabled is false', function () {
    $container = bundleContainer(['enabled' => false]);
    $container->compile();
    $bundle = new LerdDebugBundle();
    $bundle->setContainer($container);
    $bundle->boot();

    Lerd::info('ignored');

    expect(Lerd::enabled())->toBeFalse()
        ->and(Lerd::entries())->toBeEmpty();
});

it('forgets kept entries when the services are reset', function () {
    Lerd::info('during the request');

    (new Resetter())->reset();

    expect(Lerd::entries())->toBeEmpty();
});

it('forgets kept entries after each Messenger message', function () {
    Lerd::info('during the message');

    expect(MessengerSubscriber::getSubscribedEvents())->toHaveKey(WorkerMessageHandledEvent::class);
    (new MessengerSubscriber())->flush();

    expect(Lerd::entries())->toBeEmpty();
});
