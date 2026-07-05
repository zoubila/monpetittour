<?php

declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Entity\StageRiderResultRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class StageFixtureLoader
{
    public function __construct(
        private RiderRecordRepository $riders,
        private StageRecordRepository $stages,
        private StageRiderResultRecordRepository $results,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function loadIfEmpty(): void
    {
        $stages = [];

        foreach ($this->stageDefinitions() as $stageDefinition) {
            $stage = $this->stages->findOneByNumber($stageDefinition->number());

            if (!$stage instanceof StageRecord) {
                $stage = $stageDefinition;
                $this->stages->save($stage);
            }

            $stages[] = $stage;
        }

        $this->entityManager->flush();
        $this->loadMissingResults($stages);
        $this->entityManager->flush();
    }

    /**
     * @return list<StageRecord>
     */
    private function stageDefinitions(): array
    {
        return [
            new StageRecord(1, 'Florence', 'Rimini', 206, 2_100, 'https://www.letour.fr/img/stage-1.png'),
            new StageRecord(2, 'Cesenatico', 'Bologne', 199, 1_850, 'https://www.letour.fr/img/stage-2.png'),
            new StageRecord(3, 'Plaisance', 'Turin', 231, 900, 'https://www.letour.fr/img/stage-3.png'),
            new StageRecord(4, 'Turin', 'Valloire', 178, 3_600, 'https://www.letour.fr/img/stage-4.png'),
        ];
    }

    /**
     * @param list<StageRecord> $stages
     */
    private function loadMissingResults(array $stages): void
    {
        $riders = $this->riders->findAllOrderedByName();

        foreach ($stages as $stageIndex => $stage) {
            if ($this->results->count(['stage' => $stage]) > 0) {
                continue;
            }

            foreach ($riders as $riderIndex => $rider) {
                $this->results->save(new StageRiderResultRecord(
                    $stage,
                    $rider,
                    $this->timeFor($stage, $rider, $stageIndex, $riderIndex),
                ));
            }
        }
    }

    private function timeFor(StageRecord $stage, RiderRecord $rider, int $stageIndex, int $riderIndex): int
    {
        $baseTime = ((int) round($stage->distanceInKilometers() * 82)) + (int) ($stage->positiveElevationInMeters() / 8);
        $nameFactor = strlen($rider->slug()) % 17;

        return $baseTime + ($riderIndex * 19) + ($stageIndex * 31) + ($nameFactor * 7);
    }
}
