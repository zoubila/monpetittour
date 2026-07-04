<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;

interface FantasyLeagueRepositoryInterface
{
    public function findOneByCode(string $code): ?FantasyLeagueRecord;

    /**
     * @return list<FantasyLeagueRecord>
     */
    public function findByParticipant(ApplicationUser $participant): array;

    public function save(FantasyLeagueRecord $league): void;
}
