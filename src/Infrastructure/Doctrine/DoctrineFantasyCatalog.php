<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use App\Domain\Entity\CumulativeStanding;
use App\Domain\Entity\League;
use App\Domain\Entity\Rider;
use App\Domain\Entity\Stage;
use App\Domain\Repository\FantasyCatalogInterface;
use App\Infrastructure\Doctrine\Entity\RiderRecord;
use App\Infrastructure\Doctrine\Repository\RiderRecordRepository;
use App\Infrastructure\Fixture\FantasyFixtureCatalog;

final readonly class DoctrineFantasyCatalog implements FantasyCatalogInterface
{
    public function __construct(
        private RiderRecordRepository $riders,
        private FantasyFixtureCatalog $fallbackFixtures,
    ) {
    }

    /**
     * @return list<Rider>
     */
    public function riders(): array
    {
        return array_map(
            static fn (RiderRecord $rider): Rider => self::toDomainRider($rider),
            $this->riders->findAllOrderedByName(),
        );
    }

    public function riderBySlug(string $slug): ?Rider
    {
        $rider = $this->riders->findOneBySlug($slug);

        return $rider instanceof RiderRecord ? self::toDomainRider($rider) : null;
    }

    public function mainLeague(): League
    {
        return $this->fallbackFixtures->mainLeague();
    }

    /**
     * @return list<Stage>
     */
    public function stages(): array
    {
        return $this->fallbackFixtures->stages();
    }

    /**
     * @return list<CumulativeStanding>
     */
    public function standings(): array
    {
        return $this->fallbackFixtures->standings();
    }

    public static function toDomainRider(RiderRecord $rider): Rider
    {
        return new Rider(
            $rider->slug(),
            $rider->name(),
            $rider->realTeam(),
            $rider->nationality(),
            $rider->marketValueInEuros(),
            $rider->specialty(),
        );
    }
}
