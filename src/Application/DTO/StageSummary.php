<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class StageSummary
{
    public function __construct(
        public int $number,
        public string $startLocation,
        public string $finishLocation,
        public int $distanceInKilometers,
        public int $positiveElevationInMeters,
    ) {
    }
}
