<?php

declare(strict_types=1);

namespace App\Application\Repository;

interface RiderResultReadRepositoryInterface
{
    /**
     * @param list<int> $riderIds
     * @return array<int, int>
     */
    public function cumulativeTimesByRiderIds(array $riderIds): array;

    public function bestCumulativeTimeInSeconds(): ?int;
}
