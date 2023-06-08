<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Cli\Scraper;
use App\FileSystem\CsvFile;
use App\Helpers\ReadableSize;
use App\Helpers\ReadableTime;

$metrics = [
    'memory' => ['start' => memory_get_usage()  , 'end' => 0],
    'time'   => ['start' => microtime(true)     , 'end' => 0],
];

$printLine = static function (string $line): void {
    echo $line . PHP_EOL;
};

//---

$config = include __DIR__ . '/../config/app.php';

$province = $argc >= 2 ? (int) $argv[1] : $config['provinces'][0];
$min      = $argc >= 3 ? (int) $argv[2] : $config['postal-codes'][0];
$max      = $argc >= 4 ? (int) $argv[3] : $config['postal-codes'][1];

$printLine(sprintf(
    '- Province [ %d ] - Postal Codes [ %d..%d ] - Concurrent Requests [ %d ]...',
    $province,
    $min,
    $max,
    $config['concurrency']
));

CsvFile::saveTo(
    filepath: sprintf($config['output'], $province),
    headers: ['Text', 'Longitude', 'Latitude'],
    lines: (new Scraper($province))->setup($config)->process($min, $max)
);

//---

$metrics['time']['end']   = microtime(true);
$metrics['memory']['end'] = memory_get_usage();

$printLine(sprintf(
    '- Elapsed time: %s',
    ReadableTime::convert($metrics['time']['end'] - $metrics['time']['start'])
));
$printLine(sprintf(
    '- Consumed memory: %s',
    ReadableSize::convert($metrics['memory']['end'] - $metrics['memory']['start'])
));
$printLine(sprintf(
    '- CSV generated at: %s',
    sprintf($config['output'], $province)
));
