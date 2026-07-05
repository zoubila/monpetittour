<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class LeagueTeamCompositionItem
{
    /**
     * @param list<LeagueTeamCompositionRiderItem> $riders
     */
    public function __construct(
        public string $teamName,
        public string $ownerUsername,
        public int $spentBudgetInEuros,
        public bool $isCurrentUser,
        public array $riders,
    ) {
    }
}
