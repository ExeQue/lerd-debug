# Contributing

Thanks for helping out. This file covers how to work on the package and the rules that keep it safe to ship to every lerd install, most of all the ones about rendering.

## Setup

```bash
composer install
composer test        # test:unit, test:arch and test:types
composer lint        # Pint, PSR-12 with imports instead of fully qualified names
```

Every change keeps `composer test` and `composer lint -- --test` green. The CI workflow runs the unit and architecture tests on PHP 8.1 to 8.5, PHPStan at level 10 with strict rules, and Pint.

## Rendering

lerd does not read the package's classes. It reads what a renderer in `src/Rendering` makes of them, for the schema version lerd names. Apps pin this package and lerd updates on its own schedule, so an install can pair any version of one with any version of the other. The renderers are what keep that working, and these rules are what keep the renderers trustworthy.

### A released renderer never changes its output

`V1Renderer` produces schema 1, and every lerd that asks for schema 1 reads exactly that. Once a renderer has shipped in a release, its output is frozen:

- No key is renamed, removed, or given a different type or meaning.
- No value changes shape: a list stays a list, a string stays a string, `null` stays allowed where it was.
- No new block or entry type appears in its output, since an older lerd would not know it.

`tests/Unit/RendererSnapshotTest.php` renders a fixed set of entries with every renderer and compares the result byte for byte with `tests/Fixtures/renderers/v{version}.json`. If that test fails, the change is wrong for the renderer it touched; it is never fixed by updating the fixture of a released renderer.

### Changing the schema means a new renderer

When lerd needs data in a different shape, or a new kind of block or entry:

1. Agree the new schema with lerd first. lerd's collector names the version it reads in `DEBUG_SCHEMA`, and its UI has to understand the new shape before anything produces it.
2. Add `V{n}Renderer` next to the existing ones and return it from `Renderers::all()`. Extending the previous renderer and overriding what differs is fine.
3. Run the tests once. The snapshot test writes `tests/Fixtures/renderers/v{n}.json` and fails on purpose. Read the file, check every entry is what lerd expects, and commit it with the renderer.
4. Leave every older renderer and fixture untouched, so a lerd still asking for an older version keeps getting it.

`Renderers::for()` hands out the newest renderer no newer than the version asked for, so a newer package works with an older lerd and an older package with a newer one.

### Adding to the package without a new schema

New methods, builders and classes are welcome as long as they produce data the current schema already describes. A new convenience on `Tab` that adds an existing block type is fine; a new block type is a schema change.

### Contracts

What the package accepts is described by the interfaces in `src/Contracts`, and a renderer reads objects only through them, never through a concrete class, so an app's own implementation renders the same as the package's. The architecture test in `tests/Arch` fails when a class with a contract has a public method its contracts do not declare; add the method to the contract rather than relaxing the test. Constructors and static named constructors are the exception.

Classes stay non-final so apps can extend them, and keep their constructor signatures stable (`@phpstan-consistent-constructor`).

## Doing nothing outside lerd

The package must cost nothing where lerd is not capturing. `Lerd::enabled()` reads that from the constants lerd's extension defines, and every path that keeps or tracks something checks it first. A new feature that stores anything, even in memory, does the same.

## Style

- Names are written out in full; no shorthand variables.
- Classes, functions and constants are imported rather than written fully qualified; Pint enforces it.
- Fluent methods return `static`.
- Comments explain why, briefly; the code says what.
