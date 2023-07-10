<?php

declare(strict_types=1);

namespace App\Cli;

use App\Helpers\Range;
use App\Http\UserAgents;
use Closure;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleRetry\GuzzleRetryMiddleware;
use Psr\Http\Message\ResponseInterface;

final class Scraper
{
    public const MODE_SEQUENTIAL = 'sequential';
    public const MODE_CONCURRENT = 'concurrent';

    private string $mode;
    private string $endpoint;
    private Client $clientSync;
    private Client $clientAsync;
    private int $concurrency;

    /**
     * @var array<int|string, mixed>
     */
    private array $result;

    public function __construct(
        private readonly int $province
    ) {
    }

    /**
     * @param array<string, array<int, int>|int|string> $config
     */
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

        /** @phpstan-ignore-next-line */
        $this->clientSync  = new Client([...$config['guzzle_client'], ...$extra + $extraSync]);

        /** @phpstan-ignore-next-line */
        $this->clientAsync = new Client([...$config['guzzle_client'], ...$extra + $extraAsync]);

        $this->concurrency = $config['concurrency'];        /** @phpstan-ignore-line */
        $this->endpoint    = $config['endpoint_pattern'];   /** @phpstan-ignore-line */
        $this->mode        = $config['mode'];               /** @phpstan-ignore-line */
        $this->result      = [];

        return $this;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function process(int $min, int $max): array
    {
        match ($this->mode) {
            Scraper::MODE_CONCURRENT => $this->processConcurrent($min, $max),
            Scraper::MODE_SEQUENTIAL => $this->processSequential($min, $max),
            default                  => throw new Exception('Mode not implemented yet'),
        };

        /** @phpstan-ignore-next-line */
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

        $pool = new Pool($this->clientAsync, $requestsGenerator((array) $postalCodes), [
            'concurrency' => $this->concurrency,

            'fulfilled' => function (Response $response, $index) {
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

        foreach ((array) $postalCodes as $code) {
            /** @phpstan-ignore-next-line */
            $response = $this->clientSync->request('GET', sprintf($this->endpoint, (string) $code), [
                'User-Agent' => UserAgents::getRandom(),
                'Referer'    => 'https://www.correos.es/',
            ]);

            $this->parseResponse($response);
        }
    }

    private function parseResponse(ResponseInterface $response): void
    {
        $data = json_decode($response->getBody()->getContents(), true);

        /* @phpstan-ignore-next-line */
        if (array_key_exists('suggestions', $data)) {
            $this->result = [...$this->result, ...$data['suggestions']];
        }
    }
}
