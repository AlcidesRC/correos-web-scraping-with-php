<?php

declare(strict_types=1);

namespace App\FileSystem;

final class CsvFile
{
    public static function saveTo(string $filepath, array $headers, array $lines): void
    {
        $fp = fopen($filepath, 'w');

        if (count($headers)) {
            fputcsv($fp, $headers);
        }

        array_map(static function (array $line) use ($fp): void {
            fputcsv($fp, $line);
        }, $lines);

        fclose($fp);
    }
}
