<?php

declare(strict_types=1);

namespace App\Helpers;

use Closure;

final class Range
{
    private array $range;

    public function __construct(int $min, int $max)
    {
        if ($max < $min) {
            $aux = $min;
            $min = $max;
            $max = $aux;
        }

        $this->range = range($min, $max);
    }

    public static function fromArray(array $pair): self
    {
        [$min, $max] = $pair;

        return new Range($min, $max);
    }

    public function contains(int $entry): bool
    {
        return reset($this->range) <= $entry && $entry <= end($this->range);
    }

    public function each(Closure $closure): mixed
    {
        return array_map(function (int $entry) use ($closure) {
            return $closure($entry);
        }, $this->range);
    }
}
