<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Handler\ImportRidersHandler;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Tests\Support\DatabaseSchemaTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ImportRidersHandlerTest extends KernelTestCase
{
    use DatabaseSchemaTrait;

    public function testItImportsRidersThroughTheConfiguredImportSource(): void
    {
        self::bootKernel();
        $this->recreateDatabaseSchema();

        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        $this->clearRiders($riders);

        /** @var ImportRidersHandler $importRiders */
        $importRiders = self::getContainer()->get(ImportRidersHandler::class);
        $importRiders();

        self::assertSame(24, $riders->countRiders());
        self::assertNotNull($riders->findOneBySlug('tadej-pogacar'));
    }

    public function testItKeepsExistingRiderIdsWhenImportingAgain(): void
    {
        self::bootKernel();
        $this->recreateDatabaseSchema();

        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        /** @var ImportRidersHandler $importRiders */
        $importRiders = self::getContainer()->get(ImportRidersHandler::class);

        $tadejPogacar = $riders->findOneBySlug('tadej-pogacar');
        self::assertInstanceOf(RiderRecord::class, $tadejPogacar);
        $initialId = $tadejPogacar->id();

        $importRiders();

        $importedTadejPogacar = $riders->findOneBySlug('tadej-pogacar');
        self::assertInstanceOf(RiderRecord::class, $importedTadejPogacar);
        self::assertSame($initialId, $importedTadejPogacar->id());
    }

    private function clearRiders(RiderRecordRepository $riders): void
    {
        $riders->createQueryBuilder('rider')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
