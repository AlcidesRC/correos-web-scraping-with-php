<?php

declare(strict_types=1);

namespace App\Helpers;

final class ReadableTime
{
    public static function convert(float $num): string
    {
        return sprintf(
            '%02d:%02d:%02d.%d',
            floor($num / 3600),
            (int) ($num / 60) % 60,
            (int) $num % 60,
            explode('.', number_format($num, 4))[1]
        );
    }
}
