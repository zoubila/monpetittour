<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final readonly class CumulativeStanding
{
    public function __construct(
        public FantasyTeam $team,
        public int $totalTimeInSeconds,
        public int $rank,
    ) {
    }
}
