<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Application\Repository\RiderResultReadRepositoryInterface;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Entity\StageRiderResultRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StageRiderResultRecord>
 */
final class StageRiderResultRecordRepository extends ServiceEntityRepository implements RiderResultReadRepositoryInterface
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

    public function deleteByStage(StageRecord $stage): void
    {
        $this->createQueryBuilder('result')
            ->delete()
            ->andWhere('result.stage = :stage')
            ->setParameter('stage', $stage)
            ->getQuery()
            ->execute();
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
     * @param list<int> $riderIds
     * @return array<int, int>
     */
    public function cumulativeTimesByRiderIds(array $riderIds): array
    {
        if ($riderIds === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('result')
            ->select('IDENTITY(result.rider) AS riderId, SUM(result.timeInSeconds) AS totalTime')
            ->andWhere('result.rider IN (:riderIds)')
            ->setParameter('riderIds', $riderIds)
            ->groupBy('result.rider')
            ->getQuery()
            ->getArrayResult();

        $times = [];
        foreach ($rows as $row) {
            $times[(int) $row['riderId']] = (int) $row['totalTime'];
        }

        return $times;
    }

    public function bestCumulativeTimeInSeconds(): ?int
    {
        $rows = $this->createQueryBuilder('result')
            ->select('SUM(result.timeInSeconds) AS totalTime')
            ->groupBy('result.rider')
            ->orderBy('totalTime', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();

        if ($rows === []) {
            return null;
        }

        return (int) $rows[0]['totalTime'];
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
