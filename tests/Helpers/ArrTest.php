<?php

namespace Diana\Tests\Helpers;

use Diana\Support\Helpers\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public static function values(): iterable
    {
        yield 'empty' => [[], 'fallback', 'fallback'];
        yield 'single zero key' => [[10], 10, 10];
        yield 'numeric keys' => [[10, 20], 10, 20];
        yield 'insertion order' => [[9 => 'first', 0 => 'last'], 'first', 'last'];
        yield 'null is a value' => [['first' => null, 'last' => null], null, null];
        yield 'false is a value' => [[false], false, false];
    }

    #[DataProvider('values')]
    public function testFirstAndLastPreserveValuesForArraysAndGenerators(array $values, mixed $first, mixed $last): void
    {
        $generator = static function () use ($values): iterable { yield from $values; };
        self::assertSame($first, Arr::first($values, default: 'fallback'));
        self::assertSame($last, Arr::last($values, default: 'fallback'));
        self::assertSame($first, Arr::first($generator(), default: 'fallback'));
        self::assertSame($last, Arr::last($generator(), default: 'fallback'));
    }

    public function testPredicatesReceiveKeysAndFindTheFirstAndLastMatch(): void
    {
        $values = ['a' => 1, 'b' => 2, 'c' => 3];
        $predicate = static fn (int $value, string $key): bool => $key !== 'a' && $value >= 2;
        $generator = static function () use ($values): iterable { yield from $values; };
        foreach ([$values, $generator()] as $input) {
            self::assertSame(2, Arr::first($input, $predicate));
        }
        foreach ([$values, $generator()] as $input) {
            self::assertSame(3, Arr::last($input, $predicate));
        }
    }

    public function testDefaultsAreLazyAndMatchingNullDoesNotUseTheDefault(): void
    {
        $calls = 0;
        $default = static function () use (&$calls): string { ++$calls; return 'fallback'; };
        foreach (['first', 'last'] as $method) {
            self::assertNull(Arr::$method([null], static fn ($value) => $value === null, $default));
            self::assertSame(0, $calls);
        }
        foreach (['first', 'last'] as $method) {
            self::assertSame('fallback', Arr::$method([10], static fn () => false, $default));
        }
        self::assertSame(2, $calls);
    }

    public function testFirstStopsAOneShotIterableAtTheFirstMatch(): void
    {
        $input = static function (): iterable {
            yield 42;
            self::fail('The first element must not consume the rest of the generator.');
        };
        self::assertSame(42, Arr::first($input()));
    }
}
