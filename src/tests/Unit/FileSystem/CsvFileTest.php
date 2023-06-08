<?php

declare(strict_types=1);

namespace UnitTests\FileSystem;

use App\FileSystem\CsvFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CsvFileTest extends TestCase
{
    /**
     * @covers \App\FileSystem\CsvFile::saveTo
     *
     * @dataProvider dataProviderForMethodSave
     */
    public function testMethodSave(string $filepath, array $headers, array $lines): void
    {
        $result = CsvFile::saveTo($filepath, $headers, $lines);

        static::assertTrue(file_exists($filepath));
        static::assertGreaterThan(0, filesize($filepath));
    }

    public function dataProviderForMethodSave(): array
    {
        return [
            ['/tmp/test1.csv', [], [['a', 'aa', 'aaa'], ['b', 'bb', 'bbb']]],
            ['/tmp/test2.csv', ['X', 'XX', 'XXX'], [['a', 'aa', 'aaa'], ['b', 'bb', 'bbb']]],
        ];
    }
}
