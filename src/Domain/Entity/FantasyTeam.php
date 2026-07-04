<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidFantasyTeam;

final readonly class FantasyTeam
{
    public const BUDGET_IN_EUROS = 500_000;
    private const EXPECTED_RIDER_COUNT = 8;

    /**
     * @param list<Rider> $riders
     */
    public function __construct(
        public string $name,
        public FantasyUser $owner,
        public array $riders,
    ) {
        if (count($riders) !== self::EXPECTED_RIDER_COUNT) {
            throw InvalidFantasyTeam::mustContainEightRiders();
        }

        if ($this->spentBudgetInEuros() > self::BUDGET_IN_EUROS) {
            throw InvalidFantasyTeam::budgetExceeded();
        }
    }

    public function spentBudgetInEuros(): int
    {
        return array_sum(array_map(
            static fn (Rider $rider): int => $rider->marketValueInEuros,
            $this->riders,
        ));
    }
}
