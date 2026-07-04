<?php

declare(strict_types=1);

namespace App\Domain\Entity;

final readonly class FantasyUser
{
    public function __construct(
        public string $username,
    ) {
    }
}
