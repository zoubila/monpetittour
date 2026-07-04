<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;

final readonly class JoinFantasyLeagueCommand
{
    public function __construct(
        public ApplicationUser $participant,
        public string $code,
    ) {
    }
}
