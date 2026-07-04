<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\External\TourDeFrance\TourDeFrance2026PublishedStartListSource;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Tests\Support\DatabaseSchemaTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class RiderStillRacingTest extends KernelTestCase
{
    use DatabaseSchemaTrait;

    public function testImportedRidersAreStillRacingByDefault(): void
    {
        self::bootKernel();
        $this->recreateDatabaseSchema();

        $application = new Application(self::$kernel);
        $command = $application->find('app:tour-de-france-2026:import-published-startlist');
        $tester = new CommandTester($command);
        $tester->execute([]);

        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        $tadejPogacar = $riders->findOneBySlug('tadej-pogacar');

        self::assertNotNull($tadejPogacar);
        self::assertTrue($tadejPogacar->isStillRacing());
    }

    public function testRiderCanBeMarkedAsAbandoned(): void
    {
        self::bootKernel();
        $this->recreateDatabaseSchema();

        /** @var RiderRecordRepository $riders */
        $riders = self::getContainer()->get(RiderRecordRepository::class);
        $firstImportedRider = (new TourDeFrance2026PublishedStartListSource())->riders()[0];
        $rider = $riders->findOneBySlug($firstImportedRider->slug);

        self::assertNotNull($rider);
        $rider->markAbandoned();

        self::assertFalse($rider->isStillRacing());
    }
}
