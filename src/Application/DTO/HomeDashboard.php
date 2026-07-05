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
        public int $completedStageCount,
        public int $totalStageCount,
        public string $formattedTeamTotalTime,
        public ?int $globalFantasyRank,
        public array $leagues,
    ) {
    }
}
