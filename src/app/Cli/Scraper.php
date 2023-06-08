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
    private readonly Client $client;
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
            'handler' => $stack,
            RequestOptions::HEADERS => [
                'User-Agent' => UserAgents::getRandom(),
            ]
        ];

        $this->client      = new Client(array_merge($config['guzzle_client'], $extra));
        $this->concurrency = $config['concurrency'];
        $this->result      = [];

        return $this;
    }

    public function process(int $min, int $max): array
    {
        $postalCodes = Range::fromArray([$min, $max])->each(function (int $entry) {
            return sprintf('%02d%03d', $this->province, $entry);
        });

        $requestsGenerator = function (array $postalCodes) {
            foreach ($postalCodes as $code) {
                yield new Request('GET', "digital-services/searchengines/api/v1/suggestions?text=$code", [
                    RequestOptions::HEADERS => ['User-Agent' => UserAgents::getRandom()],
                ]);
            }
        };

        $pool = new Pool($this->client, $requestsGenerator($postalCodes), [
            'concurrency' => $this->concurrency,

            'fulfilled' => function (Response $response, $index) use ($postalCodes) {
                $data = json_decode($response->getBody()->getContents(), true);

                if (array_key_exists('suggestions', $data)) {
                    $this->result = [...$this->result, ...$data['suggestions']];
                }
            },

            'rejected' => function (ConnectException|RequestException $e) {
                // TODO - Log possible exceptions
            },
        ]);

        $pool->promise()->wait();

        array_multisort(array_column($this->result, 'text'), SORT_ASC, $this->result);

        return $this->result;
    }
}
