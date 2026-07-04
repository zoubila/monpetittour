<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class UserLeagueSummary
{
    /**
     * @param list<StandingItem> $standings
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $code,
        public array $standings,
    ) {
    }
}
