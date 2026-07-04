<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Enum\RiderSpecialty;

final readonly class ImportedRider
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
