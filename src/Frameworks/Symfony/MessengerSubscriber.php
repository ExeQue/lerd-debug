<?php

namespace Lerd\Debug\Frameworks\Symfony;

use Lerd\Debug\Lerd;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

/**
 * Forgets Lerd's kept entries once a Messenger worker has handled a message,
 * or failed to.
 */
class MessengerSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class => 'flush',
            WorkerMessageFailedEvent::class => 'flush',
        ];
    }

    public function flush(): void
    {
        Lerd::flush();
    }
}
