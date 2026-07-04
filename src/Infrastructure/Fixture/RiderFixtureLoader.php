<?php

declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use App\Domain\Entity\Rider;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RiderFixtureLoader
{
    public function __construct(
        private FantasyFixtureCatalog $fixtures,
        private RiderRecordRepository $riders,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function loadIfEmpty(): void
    {
        if ($this->riders->count([]) > 0) {
            return;
        }

        foreach ($this->fixtures->riders() as $rider) {
            $this->riders->save($this->toRecord($rider));
        }

        $this->entityManager->flush();
    }

    private function toRecord(Rider $rider): RiderRecord
    {
        return new RiderRecord(
            $rider->slug,
            $rider->name,
            $rider->realTeam,
            $rider->nationality,
            $rider->marketValueInEuros,
            $rider->specialty,
        );
    }
}
