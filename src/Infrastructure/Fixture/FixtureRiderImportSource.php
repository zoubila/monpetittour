<?php

declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use App\Application\DTO\ImportedRider;
use App\Application\Port\RiderImportSourceInterface;
use App\Domain\Entity\Rider;

final readonly class FixtureRiderImportSource implements RiderImportSourceInterface
{
    public function __construct(
        private FantasyFixtureCatalog $fixtures,
    ) {
    }

    /**
     * @return list<ImportedRider>
     */
    public function riders(): array
    {
        return array_map(
            static fn (Rider $rider): ImportedRider => new ImportedRider(
                $rider->slug,
                $rider->name,
                $rider->realTeam,
                $rider->nationality,
                $rider->marketValueInEuros,
                $rider->specialty,
            ),
            $this->fixtures->riders(),
        );
    }
}
