<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Infrastructure\Doctrine\Entity\StageRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StageRecord>
 */
final class StageRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StageRecord::class);
    }

    /**
     * @return list<StageRecord>
     */
    public function findAllOrderedByNumber(): array
    {
        return $this->createQueryBuilder('stage')
            ->orderBy('stage.stageNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByNumber(int $number): ?StageRecord
    {
        return $this->findOneBy(['stageNumber' => $number]);
    }

    public function save(StageRecord $stage): void
    {
        $this->getEntityManager()->persist($stage);
    }
}
