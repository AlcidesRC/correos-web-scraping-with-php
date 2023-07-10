<?php

declare(strict_types=1);

namespace UnitTests\FileSystem;

use App\FileSystem\CsvFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @phpstan-type DataProviderEntry array{string, list<string>, non-empty-array<int, non-empty-list<string>>}
 */
final class CsvFileTest extends TestCase
{
    /**
     * @covers \App\FileSystem\CsvFile::saveTo
     *
     * @dataProvider dataProviderForMethodSave
     *
     * @param array<string> $headers
     * @param array<int, array<string>> $lines
     */
    public function testMethodSave(string $filepath, array $headers, array $lines): void
    {
        CsvFile::saveTo($filepath, $headers, $lines);

        static::assertTrue(file_exists($filepath));
        static::assertGreaterThan(0, filesize($filepath));
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public function dataProviderForMethodSave(): array
    {
        return [
            ['/tmp/test1.csv', [], [['a', 'aa', 'aaa'], ['b', 'bb', 'bbb']]],
            ['/tmp/test2.csv', ['X', 'XX', 'XXX'], [['a', 'aa', 'aaa'], ['b', 'bb', 'bbb']]],
        ];
    }
}
