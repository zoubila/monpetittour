<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class HomeDashboard
{
    /**
     * @param list<UserLeagueSummary> $leagues
     */
    public function __construct(
        public ?UserTeamSummary $team,
        public int $riderCount,
        public int $stageCount,
        public int $teamBudgetInEuros,
        public array $leagues,
    ) {
    }
}
