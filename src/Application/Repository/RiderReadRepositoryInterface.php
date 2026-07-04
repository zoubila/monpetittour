<?php

declare(strict_types=1);

namespace App\Application\Repository;

use App\Application\DTO\RiderDetails;
use App\Application\DTO\RiderListItem;
use App\Domain\Entity\Rider;

interface RiderReadRepositoryInterface
{
    /**
     * @return list<RiderListItem>
     */
    public function listRiders(): array;

    public function riderDetailsBySlug(string $slug): ?RiderDetails;

    /**
     * @param list<int> $ids
     * @return list<Rider>
     */
    public function domainRidersByIds(array $ids): array;

    public function countRiders(): int;
}
