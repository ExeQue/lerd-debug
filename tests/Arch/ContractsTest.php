<?php

namespace Lerd\Debug\Tests\Arch;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

use function array_filter;
use function array_merge;
use function array_unique;
use function array_values;
use function class_exists;
use function dirname;
use function expect;
use function in_array;
use function it;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Every class under src, by the file it is declared in.
 *
 * @return list<class-string>
 */
function packageClasses(): array
{
    $root = dirname(__DIR__, 2) . '/src/';
    $classes = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $class = 'Lerd\\Debug\\' . str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($root)));
        if (class_exists($class)) {
            $classes[] = $class;
        }
    }

    return $classes;
}

it('gives a class with a contract no public method its contracts do not declare', function () {
    $strays = [];
    foreach (packageClasses() as $class) {
        $reflection = new ReflectionClass($class);
        $contracts = array_values(array_filter(
            $reflection->getInterfaceNames(),
            fn (string $interface) => str_starts_with($interface, 'Lerd\\Debug\\Contracts\\'),
        ));
        if ($contracts === []) {
            continue;
        }
        $declared = [];
        foreach ($reflection->getInterfaces() as $interface) {
            foreach ($interface->getMethods() as $method) {
                $declared[] = $method->getName();
            }
        }
        $declared = array_unique(array_merge($declared));
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Named constructors and the constructor belong to the class, not
            // to what it promises.
            if ($method->isStatic() || $method->isConstructor()) {
                continue;
            }
            if (!in_array($method->getName(), $declared, true)) {
                $strays[] = $class . '::' . $method->getName();
            }
        }
    }

    expect($strays)->toBe([]);
});
