<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class StageLeagueDashboard
{
    /**
     * @param list<StandingItem> $teamStandings
     * @param list<RiderStageStandingItem> $riderStandings
     */
    public function __construct(
        public StageSummary $stage,
        public array $teamStandings,
        public array $riderStandings,
    ) {
    }
}
