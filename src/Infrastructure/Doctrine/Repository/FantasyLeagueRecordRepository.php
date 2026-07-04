<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Application\Repository\FantasyLeagueRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyLeagueRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FantasyLeagueRecord>
 */
final class FantasyLeagueRecordRepository extends ServiceEntityRepository implements FantasyLeagueRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FantasyLeagueRecord::class);
    }

    public function findOneByCode(string $code): ?FantasyLeagueRecord
    {
        return $this->findOneBy(['code' => strtoupper($code)]);
    }

    /**
     * @return list<FantasyLeagueRecord>
     */
    public function findByParticipant(ApplicationUser $participant): array
    {
        return $this->createQueryBuilder('league')
            ->innerJoin('league.participants', 'participant')
            ->andWhere('participant = :participant')
            ->setParameter('participant', $participant)
            ->orderBy('league.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(FantasyLeagueRecord $league): void
    {
        $this->getEntityManager()->persist($league);
        $this->getEntityManager()->flush();
    }
}
