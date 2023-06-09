<?php

declare(strict_types=1);

namespace UnitTests\Cli;

use App\Cli\Scraper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @phpstan-type DataProviderEntry array{
 *      string,
 *      int,
 *      int,
 *      int,
 *      array|array<int, array{"nombre": string, "latitude": float, "longitude": float}>
 * }
 */
final class ScraperTest extends TestCase
{
    /**
     * @var array<string, array<int, int>|int|string> $config
     */
    private array $config;

    protected function setUp(): void
    {
        $this->config                 = include(__DIR__ . '/../../../config/app.php');
        $this->config['mode']         = Scraper::MODE_SEQUENTIAL;
        $this->config['postal-codes'] = [1, 2];
        $this->config['concurrency']  = 2;
    }

    protected function tearDown(): void
    {
        unset($this->config);
    }

    /**
     * @covers \App\Cli\Scraper::__construct
     * @covers \App\Cli\Scraper::setup
     * @covers \App\Cli\Scraper::process
     * @covers \App\Cli\Scraper::processSequential
     * @covers \App\Cli\Scraper::processConcurrent
     * @covers \App\Cli\Scraper::parseResponse
     * @covers \App\Helpers\Range::__construct
     * @covers \App\Helpers\Range::each
     * @covers \App\Helpers\Range::fromArray
     * @covers \App\Http\UserAgents::getRandom
     *
     * @dataProvider dataProviderForMethodProcess
     *
     * @param array<int, array{"text": string, "longitude": float, "latitude": float}> $fixture
     */
    public function testMethodProcess(string $mode, int $province, int $min, int $max, array $fixture): void
    {
        $override = [
            'mode' => $mode
        ];

        $result = (new Scraper($province))->setup([...$this->config, ...$override])->process($min, $max);

        $this->assertEquals($fixture, $result);
    }

    /**
     * @return array<int, DataProviderEntry>
     */
    public function dataProviderForMethodProcess(): array
    {
        $loadFixture = static function (int $province): array {
            $contents = (string) file_get_contents(
                __DIR__ . sprintf('/../../Fixture/Cli/ScraperTest/province-%d.serialized', $province)
            );

            return (array) unserialize($contents);
        };

        return [
            [Scraper::MODE_SEQUENTIAL, 52, 1, 2, $loadFixture(52)],
            [Scraper::MODE_CONCURRENT, 52, 1, 2, $loadFixture(52)],
        ];
    }
}
