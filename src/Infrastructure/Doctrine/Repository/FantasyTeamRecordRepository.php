<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Application\Repository\FantasyTeamRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\ApplicationUser;
use App\Infrastructure\Doctrine\Entity\FantasyTeamRecord;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FantasyTeamRecord>
 */
final class FantasyTeamRecordRepository extends ServiceEntityRepository implements FantasyTeamRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly RiderRecordRepository $riders,
    )
    {
        parent::__construct($registry, FantasyTeamRecord::class);
    }

    public function findOneByOwner(ApplicationUser $owner): ?FantasyTeamRecord
    {
        return $this->findOneBy(['owner' => $owner]);
    }

    /**
     * @return list<FantasyTeamRecord>
     */
    public function findAllTeams(): array
    {
        return $this->createQueryBuilder('team')
            ->innerJoin('team.owner', 'owner')
            ->orderBy('team.name', 'ASC')
            ->addOrderBy('owner.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(FantasyTeamRecord $team): void
    {
        $this->getEntityManager()->persist($team);
        $this->getEntityManager()->flush();
    }

    /**
     * @param list<int> $riderIds
     */
    public function create(ApplicationUser $owner, string $name, array $riderIds): FantasyTeamRecord
    {
        $team = new FantasyTeamRecord($owner, $name, $this->riders->findByIds($riderIds));
        $this->save($team);

        return $team;
    }
}
