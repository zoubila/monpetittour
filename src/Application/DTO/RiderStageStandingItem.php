<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class RiderStageStandingItem
{
    public function __construct(
        public int $rank,
        public string $riderName,
        public string $realTeam,
        public string $time,
    ) {
    }
}
