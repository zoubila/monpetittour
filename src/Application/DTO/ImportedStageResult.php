<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class ImportedStageResult
{
    public function __construct(
        public int $position,
        public string $riderName,
        public string $realTeam,
        public int $timeInSeconds,
        public int $gapInSeconds,
    ) {
    }
}
