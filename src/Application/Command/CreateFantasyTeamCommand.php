<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;

final readonly class CreateFantasyTeamCommand
{
    /**
     * @param list<int> $riderIds
     */
    public function __construct(
        public ApplicationUser $owner,
        public string $name,
        public array $riderIds,
    ) {
    }
}
