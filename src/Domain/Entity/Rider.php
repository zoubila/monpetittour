<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Enum\RiderSpecialty;

final readonly class Rider
{
    public function __construct(
        public string $slug,
        public string $name,
        public string $realTeam,
        public string $nationality,
        public int $marketValueInEuros,
        public ?RiderSpecialty $specialty,
    ) {
    }
}
