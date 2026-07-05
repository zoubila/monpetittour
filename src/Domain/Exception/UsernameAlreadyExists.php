<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use DomainException;

final class UsernameAlreadyExists extends DomainException
{
    public function __construct(public readonly string $username)
    {
        parent::__construct('error.username_already_exists');
    }
}
