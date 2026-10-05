<?php

namespace Lerd\Debug\Tests\Unit\Frameworks\Yii;

use Lerd\Debug\Frameworks\Yii\Bootstrap;
use Lerd\Debug\Lerd;
use yii\base\Event;
use yii\console\Application;
use yii\queue\ExecEvent;
use yii\queue\Queue;

use function afterEach;
use function define;
use function defined;
use function dirname;
use function expect;
use function it;

// Yii's static helper is not autoloaded; an app's entry script requires it,
// after keeping Yii from installing error handlers PHPUnit would flag.
defined('YII_ENABLE_ERROR_HANDLER') || define('YII_ENABLE_ERROR_HANDLER', false);
require_once dirname(__DIR__, 4) . '/vendor/yiisoft/yii2/Yii.php';

/**
 * A console application with the given params, enough for a bootstrap class.
 *
 * @param array<string, mixed> $params
 */
function application(array $params = []): Application
{
    return new Application(['id' => 'lerd-debug-test', 'basePath' => __DIR__, 'params' => $params]);
}

afterEach(function () {
    Event::offAll();
});

it('leaves the package on by default', function () {
    (new Bootstrap())->bootstrap(application());

    expect(Lerd::enabled())->toBeTrue();
});

it('switches the package off when lerd.enabled is false', function () {
    (new Bootstrap())->bootstrap(application(['lerd.enabled' => false]));

    Lerd::info('ignored');

    expect(Lerd::enabled())->toBeFalse()
        ->and(Lerd::entries())->toBeEmpty();
});

it('forgets kept entries after each queue job, run or failed', function (string $event) {
    (new Bootstrap())->bootstrap(application());
    Lerd::info('during the job');

    Event::trigger(Queue::class, $event, new ExecEvent());

    expect(Lerd::entries())->toBeEmpty();
})->with([Queue::EVENT_AFTER_EXEC, Queue::EVENT_AFTER_ERROR]);

it('keeps as many entries as the lerd.keep param says', function () {
    (new Bootstrap())->bootstrap(application(['lerd.keep' => 1]));

    Lerd::info('one');
    Lerd::info('two');

    expect(Lerd::entries())->toHaveCount(1);

    Lerd::keep(Lerd::DEFAULT_KEEP);
});
