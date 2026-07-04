<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class UserTeamSummary
{
    /**
     * @param list<RiderListItem> $riders
     */
    public function __construct(
        public string $name,
        public int $spentBudgetInEuros,
        public array $riders,
    ) {
    }
}
