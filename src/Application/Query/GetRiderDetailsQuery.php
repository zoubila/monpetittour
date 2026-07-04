<?php

declare(strict_types=1);

namespace App\Application\Query;

final readonly class GetRiderDetailsQuery
{
    public function __construct(
        public string $slug,
    ) {
    }
}
