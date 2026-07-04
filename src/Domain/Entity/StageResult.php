<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final readonly class StageResult
{
    public function __construct(
        public Stage $stage,
        public Rider $rider,
        public int $timeInSeconds,
    ) {
    }
}
