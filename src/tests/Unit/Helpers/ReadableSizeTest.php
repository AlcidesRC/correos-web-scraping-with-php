<?php

declare(strict_types=1);

namespace UnitTests\Helpers;

use App\Helpers\ReadableSize;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @phpstan-type DataProviderEntry array{int, string}
 */
final class ReadableSizeTest extends TestCase
{
    /**
     * @covers \App\Helpers\ReadableSize::convert
     *
     * @dataProvider dataProviderForMethodConvert
     */
    public function testMethodConvert(float $number, string $expected): void
    {
        $result = ReadableSize::convert($number);

        static::assertEquals($result, $expected);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public function dataProviderForMethodConvert(): array
    {
        return [
            [0, '0.00 B'],
            [123, '123.00 B'],
            [1024, '1.00 KB'],
            [2540000, '2.42 MB'],
            [8590000000000, '7.81 TB'],
        ];
    }
}
