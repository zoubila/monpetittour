<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use App\Infrastructure\Fixture\RiderFixtureLoader;
use App\Infrastructure\Fixture\StageFixtureLoader;

trait DatabaseSchemaTrait
{
    private function recreateDatabaseSchema(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        /** @var RiderFixtureLoader $fixtures */
        $fixtures = self::getContainer()->get(RiderFixtureLoader::class);
        $fixtures->loadIfEmpty();

        /** @var StageFixtureLoader $stages */
        $stages = self::getContainer()->get(StageFixtureLoader::class);
        $stages->loadIfEmpty();
    }
}
