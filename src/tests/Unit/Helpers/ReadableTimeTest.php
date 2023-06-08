<?php

declare(strict_types=1);

namespace UnitTests\Helpers;

use App\Helpers\ReadableTime;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ReadableTimeTest extends TestCase
{
    /**
     * @covers \App\Helpers\ReadableTime::convert
     *
     * @dataProvider dataProviderForMethodConvert
     */
    public function testMethodReadableTime(float $number, string $expected): void
    {
        $result = ReadableTime::convert($number);

        static::assertEquals($result, $expected);
    }

    public function dataProviderForMethodConvert(): array
    {
        return [
            [10, '00:00:10.0'],
            [3601.2345, '01:00:01.2345'],
        ];
    }
}
