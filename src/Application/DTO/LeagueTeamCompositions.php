<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class LeagueTeamCompositions
{
    /**
     * @param list<LeagueTeamCompositionItem> $teams
     */
    public function __construct(
        public string $leagueName,
        public string $leagueCode,
        public array $teams,
    ) {
    }
}
