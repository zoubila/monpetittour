<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;

interface FantasyTeamRepositoryInterface
{
    public function findOneByOwner(ApplicationUser $owner): ?FantasyTeamRecord;

    /**
     * @return list<FantasyTeamRecord>
     */
    public function findAllTeams(): array;

    /**
     * @param list<int> $riderIds
     */
    public function create(ApplicationUser $owner, string $name, array $riderIds): FantasyTeamRecord;
}
