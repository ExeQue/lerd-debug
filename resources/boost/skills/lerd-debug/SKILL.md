---
name: lerd-debug
description: Write timeline rows, log lines and custom Debug window tabs (tables, key/value, counters, code, text, charts) for lerd with the lerd/debug package. Use when instrumenting code to show up in lerd's Requests lens, timing work, or adding debug tabs.
---

# lerd/debug

## When to use this skill

Use it when code should show up in lerd's Debug window: timing a piece of work on the request's timeline, writing a debug log line, or building a tab of your own with data about the request. Everything goes through `Lerd\Debug\Lerd`. Outside lerd every call is a no-op, so the calls can stay in the code.

## Timeline

```php
use Lerd\Debug\{Lerd, Color};

// Time a callback; returns the callback's result and records even on exceptions.
$result = Lerd::timeline()->measure('Render invoice PDF', fn () => $pdf->render(), 'billing', Color::Amber, ['order' => $id]);

// A moment: an event stopped without being started.
Lerd::timeline()->event('Payment authorised', 'billing', Color::Emerald)->stop();

// Timed by hand: only stop() records it. Same name gives the same running event.
$sync = Lerd::timeline()->event('Stock sync', 'inventory', Color::Violet)->start();
$sync->with(['skus' => 120])->stop();
Lerd::timeline()->event('Import')->run(fn () => $importer->run());
Lerd::timeline()->event('Queue wait')->startAt($queuedAt)->duration($ms)->stop();
```

- The category (third argument) is free text and becomes a timeline filter; use one per area, `billing`, `imports`.
- Colours come from the `Color` enum only: Blue, Indigo, Violet, Pink, Rose, Orange, Amber, Lime, Emerald, Teal, Cyan, Slate.

## Logging

```php
Lerd::info('Cache warmed', ['keys' => 120]);
Lerd::warning('Slow upstream', ['ms' => 2300, 'trace' => true]);   // keep the stack trace
Lerd::notice('Fast path taken', ['performance' => true]);           // also on the Performance tab
```

Levels are the PSR-3 ones; `trace` and `performance` are flags, not context.

## Tabs

```php
use Lerd\Debug\{Chart, Series};

Lerd::tab('Cart')->columns(2)
    ->counters(['items' => 3, 'total' => '€59.70'], 'Summary', span: 2)
    ->keyValue($cart->toArray(), 'Contents')
    ->table(['sku', 'qty'], $lines, 'Lines')
    ->code($payload, 'json', 'Webhook')
    ->text('Coupon applied')
    ->chart(Chart::bar(Series::make('orders', Color::Teal)->point('Mon', 3)->point('Tue', 5)), 'This week', span: 2);
```

- `Lerd::tab('Name')` returns the same tab for the same name; write to it from anywhere in the request.
- `columns(1..4)` lays blocks out in a grid; `span:` on a block makes it wider.
- Charts: `Chart::line(...$series)`, `Chart::bar(...$series)`, `Chart::pie($series)`, `Chart::explodedPie($series)`; a `Series` is a name and a value per label.

## Conventions

- Prefer `Lerd::tab()` over many timeline rows for data that is not about time.
- Do not wrap calls in environment checks; the package already does nothing outside lerd.
- In tests, call `Lerd::enable(true)` and assert with `Lerd::entries()`; `Lerd::flush()` resets.
- Custom objects implement the contracts in `Lerd\Debug\Contracts` (`Block`, `Chart`, `Series`, `TimelineEntry`, `LogEntry`).

Full documentation: https://lerd.sh/features/debug-package/
