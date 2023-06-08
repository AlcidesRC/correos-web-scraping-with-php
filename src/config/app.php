<?php

declare(strict_types=1);

use GuzzleHttp\RequestOptions;

return [
    // Pattern to generate the CSV by province
    'output' => '/output/province-%02d.csv',

    // Range of valid province IDs
    'provinces' => [1, 52],

    // Range of valid postal codes suffixes
    'postal-codes' => [1, 999],

    // Number of concurrent request to perform during the process
    'concurrency' => 30,

    // Guzzle Client options
    'guzzle_client' => [
        // Generic settings
        RequestOptions::ALLOW_REDIRECTS => true,
        RequestOptions::CONNECT_TIMEOUT => 0,
        RequestOptions::DELAY           => 0,
        RequestOptions::TIMEOUT         => 2,
        RequestOptions::VERIFY          => false,
        RequestOptions::SYNCHRONOUS     => false,
        'base_uri'                      => 'https://api1.correos.es/',
        'protocols'                     => ['http', 'https'],
        'referer'                       => false,
        'track_redirects'               => false,

        // Retry settings
        'max_retry_attempts' => 2,
        'retry_enabled'      => true,
        'retry_on_status'    => [429, 500, 503],
        'retry_on_timeout'   => true,
    ]
];
