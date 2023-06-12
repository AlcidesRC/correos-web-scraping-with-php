<?php

declare(strict_types=1);

use App\Cli\Scraper;
use GuzzleHttp\RequestOptions;

return [
    // [Scraper::MODE_SEQUENTIAL, Scraper::MODE_CONCURRENT]
    'mode' => Scraper::MODE_SEQUENTIAL,

    // Endpoint pattern
    // https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=08001
    // https://api1.correos.es/digital-services/searchengines/api/v1/suggestions?text=52001
    'endpoint_pattern' => 'digital-services/searchengines/api/v1/suggestions?text=%s',

    // Pattern to generate the CSV by province
    'output' => __DIR__ . '/../output/province-%02d.csv',

    // Range of valid province IDs
    'provinces' => [1, 52],

    // Range of valid postal codes suffixes
    'postal-codes' => [1, 999],

    // Number of concurrent request to perform during the process
    'concurrency' => 10,

    // Guzzle Client options
    'guzzle_client' => [
        // Generic settings
        RequestOptions::ALLOW_REDIRECTS => true,
        RequestOptions::CONNECT_TIMEOUT => 0,
        RequestOptions::DELAY           => 1,
        RequestOptions::TIMEOUT         => 10,
        RequestOptions::VERIFY          => false,
        'base_uri'                      => 'https://api1.correos.es/',
        'protocols'                     => ['http', 'https'],
        'referer'                       => true,
        'track_redirects'               => false,

        // Retry settings
        'max_retry_attempts' => 2,
        'retry_enabled'      => true,
        'retry_on_status'    => [429, 500, 503],
        'retry_on_timeout'   => true,
    ]
];
