<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class StandingItem
{
    public function __construct(
        public int $rank,
        public string $teamName,
        public string $ownerUsername,
        public int $spentBudgetInEuros,
        public int $totalTimeInSeconds,
        public string $formattedTotalTime,
    ) {
    }
}
