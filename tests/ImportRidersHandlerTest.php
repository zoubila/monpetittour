<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Handler\ImportRidersHandler;
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

    private function clearRiders(RiderRecordRepository $riders): void
    {
        $riders->createQueryBuilder('rider')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
