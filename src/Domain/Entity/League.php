<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final readonly class League
{
    /**
     * @param list<FantasyTeam> $teams
     */
    public function __construct(
        public string $name,
        public array $teams,
    ) {
    }
}
