<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;

final readonly class GetLeagueDashboardQuery
{
    public function __construct(
        public int $leagueId,
        public ApplicationUser $user,
    ) {
    }
}
