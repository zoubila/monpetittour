<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\DTO\ImportedStage;
use App\Application\DTO\ImportedStageResult;
use App\Application\DTO\OfficialRiderStandingItem;
use App\Application\Handler\GetOfficialRiderGeneralClassificationHandler;
use App\Application\Handler\ImportTourDeFrance2026StageResultsHandler;
use App\Application\Handler\ImportTourDeFrance2026StagesHandler;
use App\Application\Port\TourDeFrance2026StageDataSourceInterface;
use App\Infrastructure\Doctrine\Entity\StageRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRecordRepository;
use App\Infrastructure\Doctrine\Repository\StageRiderResultRecordRepository;
use App\Tests\Support\DatabaseSchemaTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImportTourDeFrance2026StageResultsHandlerTest extends KernelTestCase
{
    use DatabaseSchemaTrait;

    public function testItImportsStageMetadataAndStageRiderResults(): void
    {
        self::bootKernel();
        $this->recreateDatabaseSchema();

        /** @var StageRecordRepository $stages */
        $stages = self::getContainer()->get(StageRecordRepository::class);
        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        /** @var StageRiderResultRecordRepository $results */
        $results = self::getContainer()->get(StageRiderResultRecordRepository::class);
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $source = new class implements TourDeFrance2026StageDataSourceInterface {
            public function stages(): array
            {
                return [
                    new ImportedStage(1, 'Barcelona', 'Barcelona', 19.6, 0, 'https://www.letour.fr/img/stage-1.png'),
                ];
            }

            public function stageResults(int $stageNumber): array
            {
                return [
                    new ImportedStageResult(1, 'T. POGACAR', 'UAE TEAM EMIRATES XRG', 16_335, 0),
                    new ImportedStageResult(2, 'J. VINGEGAARD', 'TEAM VISMA | LEASE A BIKE', 16_000, 0),
                ];
            }

            public function abandonedRiderNames(int $stageNumber): array
            {
                return ['J. VINGEGAARD'];
            }
        };

        $importStages = new ImportTourDeFrance2026StagesHandler($source, $stages, $entityManager);
        $handler = new ImportTourDeFrance2026StageResultsHandler(
            $source,
            $importStages,
            $stages,
            $riders,
            $results,
            $entityManager,
        );

        $report = $handler(1);
        $stage = $stages->findOneByNumber(1);
        $tadejPogacar = $riders->findOneBySlug('tadej-pogacar');
        $jonasVingegaard = $riders->findOneBySlug('jonas-vingegaard');
        $seppKuss = $riders->findOneBySlug('sepp-kuss');

        self::assertInstanceOf(StageRecord::class, $stage);
        self::assertNotNull($tadejPogacar);
        self::assertNotNull($jonasVingegaard);
        self::assertNotNull($seppKuss);
        self::assertSame('Barcelona', $stage->startLocation());
        self::assertSame('Barcelona', $stage->finishLocation());
        self::assertSame(19.6, $stage->distanceInKilometers());
        self::assertSame('https://www.letour.fr/img/stage-1.png', $stage->mapPath());
        self::assertSame(2, $report->importedResultCount);
        self::assertSame([], $report->unmatchedRiderNames);
        self::assertSame(
            1,
            (int) $entityManager->getConnection()->fetchOne(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'stage' AND column_name = 'stage_number'",
            ),
        );
        self::assertSame(
            0,
            (int) $entityManager->getConnection()->fetchOne(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'stage' AND column_name = 'id'",
            ),
        );
        self::assertSame(
            1,
            (int) $entityManager->getConnection()->fetchOne(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'stage_rider_result' AND column_name = 'stage_number'",
            ),
        );
        self::assertSame(
            1,
            (int) $entityManager->getConnection()->fetchOne(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'stage_rider_result' AND column_name = 'gap_in_seconds'",
            ),
        );
        self::assertSame(
            0,
            (int) $entityManager->getConnection()->fetchOne(
                "SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'stage_rider_result' AND column_name = 'stage_id'",
            ),
        );
        self::assertSame(
            2,
            (int) $entityManager->getConnection()->fetchOne(
                'SELECT COUNT(*) FROM stage_rider_result WHERE stage_number = 1',
            ),
        );

        $stageResults = $results->findByStage($stage);
        self::assertCount(2, $stageResults);
        self::assertSame('Jonas Vingegaard', $stageResults[0]->rider()->name());
        self::assertSame(16_000, $stageResults[0]->timeInSeconds());
        self::assertSame(0, $stageResults[0]->gapInSeconds());
        self::assertSame('Tadej Pogacar', $stageResults[1]->rider()->name());
        self::assertSame(16_335, $stageResults[1]->timeInSeconds());
        self::assertSame(0, $stageResults[1]->gapInSeconds());
        self::assertTrue($tadejPogacar->isStillRacing());
        self::assertFalse($jonasVingegaard->isStillRacing());
        self::assertFalse($seppKuss->isStillRacing());

        /** @var GetOfficialRiderGeneralClassificationHandler $classification */
        $classification = self::getContainer()->get(GetOfficialRiderGeneralClassificationHandler::class);
        $officialStandings = $classification();

        self::assertSame('Tadej Pogacar', $officialStandings[0]->riderName);
        self::assertSame('-', $officialStandings[0]->formattedTotalGap);

        $jonasStanding = array_values(array_filter(
            $officialStandings,
            static fn (OfficialRiderStandingItem $standing): bool => $standing->riderName === 'Jonas Vingegaard',
        ))[0] ?? null;

        self::assertNotNull($jonasStanding);
        self::assertGreaterThan($officialStandings[0]->rank, $jonasStanding->rank);
    }
}
