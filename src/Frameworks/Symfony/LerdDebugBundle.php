<?php

namespace Lerd\Debug\Frameworks\Symfony;

use Lerd\Debug\Lerd;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

use function class_exists;
use function is_int;

/**
 * lerd/debug for Symfony: `lerd.enabled` switches the package off, and the
 * entries Lerd keeps are forgotten between requests in a worker runtime
 * (FrankenPHP, RoadRunner) and after each Messenger message, so a
 * long-running process does not carry one request's entries into the next.
 */
class LerdDebugBundle extends AbstractBundle
{
    protected string $extensionAlias = 'lerd';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->integerNode('keep')->defaultValue(Lerd::DEFAULT_KEEP)->min(0)->end()
            ->end();
    }

    /**
     * @param array<mixed> $config the processed config, its keys defined in configure()
     */
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configurator->parameters()->set('lerd.enabled', ($config['enabled'] ?? true) === true);
        $configurator->parameters()->set('lerd.keep', is_int($config['keep'] ?? null) ? $config['keep'] : Lerd::DEFAULT_KEEP);
        $services = $configurator->services();
        $services->set('lerd.resetter', Resetter::class)->tag('kernel.reset', ['method' => 'reset']);
        if (class_exists(WorkerMessageHandledEvent::class)) {
            $services->set('lerd.messenger_subscriber', MessengerSubscriber::class)->tag('kernel.event_subscriber');
        }
    }

    public function boot(): void
    {
        if ($this->container?->getParameter('lerd.enabled') === false) {
            Lerd::enable(false);
        }
        if ($this->container !== null && $this->container->hasParameter('lerd.keep')) {
            $keep = $this->container->getParameter('lerd.keep');
            if (is_int($keep)) {
                Lerd::keep($keep);
            }
        }
    }
}
