<?php

namespace Lerd\Debug\Frameworks\Yii;

use Lerd\Debug\Lerd;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\queue\Queue;

use function class_exists;
use function is_int;

/**
 * lerd/debug for Yii 2: the `lerd.enabled` param set to false switches the
 * package off, and the entries Lerd keeps are forgotten after each yii2-queue
 * job, so a listener running jobs in its own process does not carry one job's
 * entries into the next. Listed in the app's `bootstrap` config.
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        if (($app->params['lerd.enabled'] ?? true) === false) {
            Lerd::enable(false);
        }
        if (is_int($app->params['lerd.keep'] ?? null)) {
            Lerd::keep($app->params['lerd.keep']);
        }

        if (class_exists(Queue::class)) {
            $flush = static fn () => Lerd::flush();
            Event::on(Queue::class, Queue::EVENT_AFTER_EXEC, $flush);
            Event::on(Queue::class, Queue::EVENT_AFTER_ERROR, $flush);
        }
    }
}
