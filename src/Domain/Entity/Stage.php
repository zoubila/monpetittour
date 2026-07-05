<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final readonly class Stage
{
    public function __construct(
        public int $number,
        public string $name,
        public float $distanceInKilometers,
    ) {
    }
}
