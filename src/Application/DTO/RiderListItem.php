<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class RiderListItem
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public string $realTeam,
        public string $nationality,
        public string $nationalityFlag,
        public int $marketValueInEuros,
        public ?string $specialty,
    ) {
    }
}
