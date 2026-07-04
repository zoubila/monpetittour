<?php

declare(strict_types=1);

namespace App\Application\Handler;

use App\Application\DTO\RiderListItem;
use App\Application\Repository\RiderReadRepositoryInterface;

final readonly class ListRidersHandler
{
    public function __construct(
        private RiderReadRepositoryInterface $riders,
    ) {
    }

    /**
     * @return list<RiderListItem>
     */
    public function __invoke(): array
    {
        return $this->riders->listRiders();
    }
}
