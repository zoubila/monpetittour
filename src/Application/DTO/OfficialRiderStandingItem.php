<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class OfficialRiderStandingItem
{
    public function __construct(
        public int $rank,
        public string $riderName,
        public string $nationalityFlag,
        public string $realTeam,
        public string $formattedTotalTime,
        public string $formattedTotalGap,
        public bool $isInCurrentUserTeam,
        public bool $isStillRacing,
    ) {
    }
}
