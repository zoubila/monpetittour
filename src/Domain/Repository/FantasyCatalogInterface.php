<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\CumulativeStanding;
use App\Domain\Entity\League;
use App\Domain\Entity\Rider;
use App\Domain\Entity\Stage;

interface FantasyCatalogInterface
{
    /**
     * @return list<Rider>
     */
    public function riders(): array;

    public function riderBySlug(string $slug): ?Rider;

    public function mainLeague(): League;

    /**
     * @return list<Stage>
     */
    public function stages(): array;

    /**
     * @return list<CumulativeStanding>
     */
    public function standings(): array;
}
