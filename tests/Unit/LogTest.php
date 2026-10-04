<?php

namespace Lerd\Debug\Tests\Unit;

use Lerd\Debug\Lerd;
use Lerd\Debug\LogLine;

use function expect;
use function it;
use function json_decode;
use function json_encode;

it('writes a log line at the level of the method called', function () {
    Lerd::warning('Disk almost full', ['free' => '2%']);

    $line = Lerd::entries()[0];
    expect($line)->toBeInstanceOf(LogLine::class)
        ->and($line->level())->toBe('warning')
        ->and($line->context())->toBe(['free' => '2%']);
});

it('takes trace and performance as flags rather than context', function () {
    Lerd::info('Cache warmed', ['trace' => true, 'performance' => true, 'keys' => 12]);

    $data = json_decode(json_encode(Lerd::entries()[0]), true);
    expect($data)->toBe([
        'type' => 'log',
        'level' => 'info',
        'message' => 'Cache warmed',
        'context' => ['keys' => 12],
        'trace' => true,
        'performance' => true,
    ]);
});
