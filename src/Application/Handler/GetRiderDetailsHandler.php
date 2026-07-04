<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\RiderDetails;
use App\Application\Query\GetRiderDetailsQuery;
use App\Application\Repository\RiderReadRepositoryInterface;

final readonly class GetRiderDetailsHandler
{
    public function __construct(
        private RiderReadRepositoryInterface $riders,
    ) {
    }

    public function __invoke(GetRiderDetailsQuery $query): ?RiderDetails
    {
        return $this->riders->riderDetailsBySlug($query->slug);
    }
}
