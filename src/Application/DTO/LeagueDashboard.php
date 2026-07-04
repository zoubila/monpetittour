<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class LeagueDashboard
{
    /**
     * @param list<StandingItem> $generalStandings
     * @param list<StageLeagueDashboard> $stages
     */
    public function __construct(
        public string $name,
        public string $code,
        public array $generalStandings,
        public array $stages,
    ) {
    }
}
