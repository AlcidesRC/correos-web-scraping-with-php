<?php

declare(strict_types=1);

namespace UnitTests\Cli;

use App\Cli\Scraper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ScraperTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        $this->config                 = include(__DIR__ . '/../../../config/app.php');
        $this->config['concurrency']  = 1;
        $this->config['postal-codes'] = [1, 10];
    }

    protected function tearDown(): void
    {
        unset($this->config);
    }

    /**
     * @covers \App\Cli\Scraper::setup
     * @covers \App\Cli\Scraper::process
     * @covers \App\Cli\Scraper::__construct
     * @covers \App\Helpers\Range::__construct
     * @covers \App\Helpers\Range::each
     * @covers \App\Helpers\Range::fromArray
     * @covers \App\Http\UserAgents::getRandom
     *
     * @dataProvider dataProviderForMethodProcess
     */
    public function testMethodProcess(int $province, int $min, int $max, array $fixture): void
    {
        $result = (new Scraper($province))->setup($this->config)->process($min, $max);

        $this->assertEquals($fixture, $result);
    }

    public function dataProviderForMethodProcess(): array
    {
        $loadFixture = static function (int $province): array {
            return unserialize(
                file_get_contents(__DIR__ . sprintf('/../../Fixture/Cli/ScraperTest/province-%d.serialized', $province))
            );
        };

        $randomProvince = random_int(3, 5);

        return [
            // Explicit checkpoints
            [1, 1, 10, $loadFixture(1)],
            [2, 1, 10, $loadFixture(2)],

            // Random checkpoints
            [$randomProvince, 1, 10, $loadFixture($randomProvince)],
        ];
    }
}
