<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class LeagueTeamCompositionRiderItem
{
    public function __construct(
        public string $name,
        public string $nationality,
        public string $nationalityFlag,
        public string $realTeam,
        public int $marketValueInEuros,
        public ?string $specialty,
        public bool $isStillRacing,
    ) {
    }
}
