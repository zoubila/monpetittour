<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class RiderDetails
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $realTeam,
        public string $nationality,
        public int $marketValueInEuros,
        public ?string $specialty,
    ) {
    }
}
