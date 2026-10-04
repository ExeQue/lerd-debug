# lerd/debug

Write your own data into [lerd](https://lerd.sh)'s Debug window: rows on a request's timeline, log lines, and tabs of your own with tables, figures and charts. Outside lerd, in production say, every call returns at once and nothing is kept.

```bash
composer require lerd/debug --dev
```

```php
use Lerd\Debug\{Lerd, Color, Chart, Series};

// Timeline
$pdf = Lerd::timeline()->measure('Render invoice PDF', fn () => $invoice->render(), 'billing', Color::Amber);

$sync = Lerd::timeline()->event('Stock sync', 'inventory', Color::Violet)->start();
// ...
$sync->with(['skus' => 120])->stop();

// Log
Lerd::warning('Slow upstream', ['ms' => 2300, 'trace' => true]);

// A tab of your own
Lerd::tab('Cart')->columns(2)
    ->counters(['items' => 3, 'total' => '€59.70'], 'Summary', span: 2)
    ->keyValue($cart->toArray(), 'Contents')
    ->chart(Chart::bar(Series::make('orders', Color::Teal)->point('Mon', 3)->point('Tue', 5)), 'This week');
```

Everything the package can do, tabs and blocks, charts, logging flags, events timed by hand, framework integrations, objects of your own and how lerd reads it, is described in the documentation: **[lerd.sh/features/debug-package](https://lerd.sh/features/debug-package/)**.

## Laravel

The service provider is discovered on its own. Publish the config to switch the package off by hand, on top of it only acting where lerd is capturing:

```bash
php artisan vendor:publish --tag=lerd-config
```

```php
// config/lerd.php
'enabled' => env('LERD_ENABLED', true),
```

## Symfony

Register the bundle, and optionally switch the package off in config, on top of it only acting where lerd is capturing:

```php
// config/bundles.php
Lerd\Debug\Frameworks\Symfony\LerdDebugBundle::class => ['dev' => true],
```

```yaml
# config/packages/lerd.yaml
lerd:
    enabled: true
```

The bundle forgets the entries `Lerd` keeps between requests in a worker runtime (FrankenPHP, RoadRunner) and after each Messenger message.

## Yii

List the bootstrap class in the app's config; a `lerd.enabled` param set to `false` switches the package off:

```php
'bootstrap' => ['log', Lerd\Debug\Frameworks\Yii\Bootstrap::class],
```

It forgets the entries `Lerd` keeps after each yii2-queue job.

Any other framework needs nothing registered; call `Lerd::flush()` after each job in a long-running worker.

## Laravel Boost

The package ships a Boost skill, `lerd-debug`, which `php artisan boost:install` offers when the package is installed.

## Development

```bash
composer install
composer test        # test:unit, test:arch and test:types
composer test:unit   # Pest, unit tests
composer test:arch   # Pest, architecture tests
composer test:types  # PHPStan, level 10 with strict rules
composer lint        # Pint, PSR-12
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for the rules on renderers and contracts before changing what the package sends to lerd.

## License

MIT
