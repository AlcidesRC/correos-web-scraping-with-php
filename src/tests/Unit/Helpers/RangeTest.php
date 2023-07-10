<?php

declare(strict_types=1);

namespace UnitTests\Helpers;

use Closure;
use App\Helpers\Range;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @phpstan-type DataProviderEntry1 array{int, int, Range}
 * @phpstan-type DataProviderEntry2 array{array<int, int>, Range}
 * @phpstan-type DataProviderEntry3 array{array<int, int>, int, boolean}
 * @phpstan-type DataProviderEntry4 array{array<int, int>, callable, non-empty-list<int>}
 */
final class RangeTest extends TestCase
{
    /**
     * @covers \App\Helpers\Range::__construct
     *
     * @dataProvider dataProviderForMethodConstruct
     */
    public function testMethodConvert(int $min, int $max, Range $expected): void
    {
        $result = new Range($min, $max);

        static::assertEquals($result, $expected);
    }

    /**
     * @return array<int, DataProviderEntry1>
     */
    public function dataProviderForMethodConstruct(): array
    {
        return [
            [1, 2, new Range(1, 2)],
            [2, 1, new Range(1, 2)],
        ];
    }

    /**
     * @covers \App\Helpers\Range::__construct
     * @covers \App\Helpers\Range::fromArray
     *
     * @dataProvider dataProviderForMethodFromArray
     *
     * @param array<int, int> $pair
     */
    public function testMethodFromArray(array $pair, Range $expected): void
    {
        $result = Range::fromArray($pair);

        static::assertEquals($result, $expected);
    }

    /**
     * @return array<int, DataProviderEntry2>
     */
    public function dataProviderForMethodFromArray(): array
    {
        return [
            [[1, 2], new Range(1, 2)],
            [[2, 1], new Range(1, 2)],
        ];
    }

    /**
     * @covers \App\Helpers\Range::__construct
     * @covers \App\Helpers\Range::fromArray
     * @covers \App\Helpers\Range::contains
     *
     * @dataProvider dataProviderForMethodContains
     *
     * @param array<int, int> $pair
     */
    public function testMethodContains(array $pair, int $entry, bool $expected): void
    {
        $result = Range::fromArray($pair)->contains($entry);

        static::assertEquals($result, $expected);
    }

    /**
     * @return array<int, DataProviderEntry3>
     */
    public function dataProviderForMethodContains(): array
    {
        return [
            [[1, 5], 1, true],
            [[1, 5], 0, false],
        ];
    }

    /**
     * @covers \App\Helpers\Range::__construct
     * @covers \App\Helpers\Range::fromArray
     * @covers \App\Helpers\Range::each
     *
     * @dataProvider dataProviderForMethodEach
     *
     * @param array<int, int> $pair
     * @param non-empty-list<int> $expected
     */
    public function testMethodEach(array $pair, Closure $closure, array $expected): void
    {
        $result = Range::fromArray($pair)->each($closure);

        static::assertEquals($result, $expected);
    }

    /**
     * @return array<int, DataProviderEntry4>
     */
    public function dataProviderForMethodEach(): array
    {
        $closure = function (int $entry): int {
            return $entry * 2;
        };

        return [
            [[1, 5], $closure, [2, 4, 6, 8, 10]],
        ];
    }
}
