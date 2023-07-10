<?php

declare(strict_types=1);

namespace App\FileSystem;

final class CsvFile
{
    /**
     * @param array<string> $headers
     * @param array<int, array<string>> $lines
     */
    public static function saveTo(string $filepath, array $headers, array $lines): void
    {
        $fp = fopen($filepath, 'w');

        if ($fp !== false) {
            if (count($headers)) {
                fputcsv($fp, $headers);
            }

            array_map(static function (array $line) use ($fp): void {
                fputcsv($fp, $line);
            }, $lines);

            fclose($fp);
        }
    }
}
