<?php

declare(strict_types=1);

namespace App\Application\Command;

final readonly class RegisterUserCommand
{
    public function __construct(
        public string $username,
        public string $plainPassword,
    ) {
    }
}
