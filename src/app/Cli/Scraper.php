<?php

declare(strict_types=1);

namespace App\Cli;

use App\Helpers\Range;
use App\Http\UserAgents;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;

final class Scraper
{
    public const MODE_SEQUENTIAL = 'sequential';
    public const MODE_CONCURRENT = 'concurrent';

    private readonly string $mode;
    private readonly string $endpoint;
    private readonly Client $clientSync;
    private readonly Client $clientAsync;
    private readonly int $concurrency;
    private array $result;

    public function __construct(
        private readonly int $province
    ) {
    }

    public function setup(array $config): self
    {
        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());

        $extra = [
            RequestOptions::SYNCHRONOUS => true,
            RequestOptions::HEADERS     => [
                'User-Agent' => UserAgents::getRandom(),
                'Referer'    => 'https://www.correos.es/',
            ],
            'handler' => $stack,
        ];

        $extraSync = [
            RequestOptions::SYNCHRONOUS => true,
        ];

        $extraAsync = [
            RequestOptions::SYNCHRONOUS => false,
        ];

        $this->clientSync  = new Client([...$config['guzzle_client'], ...$extra + $extraSync]);
        $this->clientAsync = new Client([...$config['guzzle_client'], ...$extra + $extraAsync]);

        $this->endpoint    = $config['endpoint_pattern'];
        $this->concurrency = $config['concurrency'];
        $this->mode        = $config['mode'];

        $this->result      = [];

        return $this;
    }

    public function process(int $min, int $max): array
    {
        match($this->mode) {
            Scraper::MODE_CONCURRENT => $this->processConcurrent($min, $max),
            Scraper::MODE_SEQUENTIAL => $this->processSequential($min, $max),
        };

        array_multisort(array_column($this->result, 'text'), SORT_ASC, $this->result);

        return $this->result;
    }

    private function processConcurrent(int $min, int $max): void
    {
        $postalCodes = Range::fromArray([$min, $max])->each(function (int $entry) {
            return sprintf('%02d%03d', $this->province, $entry);
        });

        $requestsGenerator = function (array $postalCodes) {
            foreach ($postalCodes as $code) {
                yield new Request('GET', sprintf($this->endpoint, $code), [
                    RequestOptions::HEADERS => [
                        'User-Agent' => UserAgents::getRandom(),
                        'Referer'    => 'https://www.correos.es/',
                    ],
                ]);
            }
        };

        $pool = new Pool($this->clientAsync, $requestsGenerator($postalCodes), [
            'concurrency' => $this->concurrency,

            'fulfilled' => function (Response $response, $index) use ($postalCodes) {
                $this->parseResponse($response);
            },

            'rejected' => function (ConnectException|RequestException $e) {
                // TODO - Log possible exceptions
            },
        ]);

        $pool->promise()->wait();
    }

    private function processSequential(int $min, int $max): void
    {
        $postalCodes = Range::fromArray([$min, $max])->each(function (int $entry) {
            return sprintf('%02d%03d', $this->province, $entry);
        });

        foreach ($postalCodes as $code) {
            $response = $this->clientSync->request('GET', sprintf($this->endpoint, $code), [
                'User-Agent' => UserAgents::getRandom(),
                'Referer'    => 'https://www.correos.es/',
            ]);

            $this->parseResponse($response);
        }
    }

    private function parseResponse(Response $response): void
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (array_key_exists('suggestions', $data)) {
            $this->result = [...$this->result, ...$data['suggestions']];
        }
    }
}
