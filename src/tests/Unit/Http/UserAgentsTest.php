<?php

declare(strict_types=1);

namespace UnitTests\Http;

use App\Http\UserAgents;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UserAgentsTest extends TestCase
{
    /**
     * @covers \App\Http\UserAgents::getRandom
     */
    public function testMethodReadableTime(): void
    {
        $result = UserAgents::getRandom();

        static::assertStringStartsWith('Mozilla/5.0 (', $result);
    }
}
