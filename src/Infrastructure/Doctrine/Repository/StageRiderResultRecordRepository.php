<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Entity\StageRiderResultRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StageRiderResultRecord>
 */
final class StageRiderResultRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StageRiderResultRecord::class);
    }

    /**
     * @return list<StageRiderResultRecord>
     */
    public function findByStage(StageRecord $stage): array
    {
        return $this->createQueryBuilder('result')
            ->innerJoin('result.rider', 'rider')
            ->andWhere('result.stage = :stage')
            ->setParameter('stage', $stage)
            ->orderBy('result.timeInSeconds', 'ASC')
            ->addOrderBy('rider.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<RiderRecord> $riders
     * @return array<int, int>
     */
    public function totalTimesByRiderIds(array $riders): array
    {
        if ($riders === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('result')
            ->select('IDENTITY(result.rider) AS riderId, SUM(result.timeInSeconds) AS totalTime')
            ->andWhere('result.rider IN (:riders)')
            ->setParameter('riders', $riders)
            ->groupBy('result.rider')
            ->getQuery()
            ->getArrayResult();

        $times = [];
        foreach ($rows as $row) {
            $times[(int) $row['riderId']] = (int) $row['totalTime'];
        }

        return $times;
    }

    /**
     * @param list<RiderRecord> $riders
     * @return array<int, int>
     */
    public function stageTimesByRiderIds(StageRecord $stage, array $riders): array
    {
        if ($riders === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('result')
            ->select('IDENTITY(result.rider) AS riderId, result.timeInSeconds AS timeInSeconds')
            ->andWhere('result.stage = :stage')
            ->andWhere('result.rider IN (:riders)')
            ->setParameter('stage', $stage)
            ->setParameter('riders', $riders)
            ->getQuery()
            ->getArrayResult();

        $times = [];
        foreach ($rows as $row) {
            $times[(int) $row['riderId']] = (int) $row['timeInSeconds'];
        }

        return $times;
    }

    public function save(StageRiderResultRecord $result): void
    {
        $this->getEntityManager()->persist($result);
    }
}
