<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;

final readonly class CreateFantasyLeagueCommand
{
    public function __construct(
        public ApplicationUser $creator,
        public string $name,
    ) {
    }
}
